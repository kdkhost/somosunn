<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::query()->orderByDesc('id')->paginate(20);
        return view('panel.admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        [$events, $courses, $mentorships] = $this->loadAppliesToOptions();

        return view('panel.admin.coupons.form', [
            'coupon' => new Coupon(),
            'events' => $events,
            'courses' => $courses,
            'mentorships' => $mentorships,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $coupon = new Coupon($data);
        $coupon->normalizeCode();
        $coupon->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['redirect' => route('panel.admin.coupons.index'), 'message' => 'Cupom criado com sucesso.']);
        }

        return redirect()->route('panel.admin.coupons.index')->with('success', 'Cupom criado com sucesso.');
    }

    public function edit(Coupon $coupon)
    {
        [$events, $courses, $mentorships] = $this->loadAppliesToOptions();

        return view('panel.admin.coupons.form', compact('coupon', 'events', 'courses', 'mentorships'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validateData($request, $coupon->id);
        $coupon->fill($data);
        $coupon->normalizeCode();
        $coupon->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['redirect' => route('panel.admin.coupons.index'), 'message' => 'Cupom atualizado com sucesso.']);
        }

        return redirect()->route('panel.admin.coupons.index')->with('success', 'Cupom atualizado com sucesso.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('panel.admin.coupons.index')->with('success', 'Cupom removido.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        if ($request->has('code')) {
            $code = strtoupper(trim((string) $request->input('code')));
            $code = preg_replace('/\s+/', '', $code);
            $request->merge(['code' => $code]);
        }

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('coupons', 'code')->ignore($ignoreId),
            ],
            'name' => 'nullable|string|max:120',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'is_active' => 'nullable|boolean',
            'applies_to' => 'required|in:all,event,course,mentorship',
            'applies_to_id' => 'nullable|integer|min:1',
            'min_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if (($data['discount_type'] ?? null) === 'percent' && (float) ($data['discount_value'] ?? 0) > 100) {
            throw ValidationException::withMessages(['discount_value' => 'Para desconto percentual, use um valor entre 0 e 100.']);
        }

        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    private function loadAppliesToOptions(): array
    {
        $events = collect();
        $courses = collect();
        $mentorships = collect();

        try {
            $events = Event::query()
                ->select(['id', 'title', 'start_at'])
                ->orderByDesc('start_at')
                ->limit(200)
                ->get();

            $courses = Course::query()
                ->select(['id', 'title'])
                ->orderByDesc('id')
                ->limit(200)
                ->get();

            $mentorships = Mentorship::query()
                ->select(['id', 'title'])
                ->orderByDesc('id')
                ->limit(200)
                ->get();
        } catch (\Throwable $e) {
            // Silence if tables don't exist yet or other issues
        }

        return [$events, $courses, $mentorships];
    }
}
