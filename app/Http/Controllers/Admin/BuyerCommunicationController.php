<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Event;
use App\Models\Course;
use App\Models\Mentorship;
use App\Models\SellerProduct;
use App\Notifications\BuyerNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class BuyerCommunicationController extends Controller
{
    public function index()
    {
        return view('admin.buyer-communication.index');
    }

    public function getItems(Request $request)
    {
        $serviceType = $request->get('service_type');
        $items = [];

        switch ($serviceType) {
            case 'event':
                $items = Event::where('published', 1)
                    ->get(['id', 'title as name'])
                    ->map(fn($item) => ['id' => $item->id, 'name' => $item->name]);
                break;
            case 'course':
                $items = Course::where('published', 1)
                    ->get(['id', 'title as name'])
                    ->map(fn($item) => ['id' => $item->id, 'name' => $item->name]);
                break;
            case 'mentorship':
                $items = Mentorship::where('published', 1)
                    ->get(['id', 'title as name'])
                    ->map(fn($item) => ['id' => $item->id, 'name' => $item->name]);
                break;
            case 'marketplace':
                $items = SellerProduct::where('published', 1)
                    ->get(['id', 'title as name'])
                    ->map(fn($item) => ['id' => $item->id, 'name' => $item->name]);
                break;
        }

        return response()->json($items);
    }

    public function sendIndividual(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'send_email' => 'boolean',
        ]);

        $user = User::findOrFail($request->user_id);

        $details = [
            'subject' => $request->subject,
            'message' => $request->message,
            'action_url' => null,
            'action_label' => null,
        ];

        $user->notify(new BuyerNotification($details, $request->boolean('send_email')));

        return back()->with('success', 'Mensagem enviada com sucesso para ' . $user->name);
    }

    public function sendBulk(Request $request)
    {
        $request->validate([
            'service_type' => 'nullable|string|in:event,course,mentorship,marketplace',
            'item_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'send_email' => 'boolean',
        ]);

        $query = Order::with('user')
            ->where('status', 'paid')
            ->whereHas('user');

        if ($request->filled('service_type')) {
            $query->saleType($request->service_type);
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $orders = $query->get();
        $users = $orders->pluck('user')->unique('id');

        $details = [
            'subject' => $request->subject,
            'message' => $request->message,
            'action_url' => null,
            'action_label' => null,
        ];

        $count = 0;
        foreach ($users as $user) {
            $user->notify(new BuyerNotification($details, $request->boolean('send_email')));
            $count++;
        }

        return back()->with('success', "Mensagem enviada para {$count} compradores.");
    }

    public function searchUsers(Request $request)
    {
        $term = $request->get('term', '');
        $users = User::where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json($users);
    }

    public function previewRecipients(Request $request)
    {
        $request->validate([
            'service_type' => 'nullable|string|in:event,course,mentorship,marketplace',
            'item_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = Order::with('user')
            ->where('status', 'paid')
            ->whereHas('user');

        if ($request->filled('service_type')) {
            $query->saleType($request->service_type);
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $orders = $query->limit(50)->get();
        $users = $orders->pluck('user')->unique('id');

        return response()->json([
            'count' => $users->count(),
            'users' => $users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]),
        ]);
    }
}
