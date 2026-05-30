<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Notifications\BuyerNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class BuyerCommunicationController extends Controller
{
    public function index()
    {
        $saleTypeLabels = Order::SALE_TYPE_LABELS;
        return view('panel.admin.buyer-communication.index', compact('saleTypeLabels'));
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
            'sale_type' => 'nullable|string|in:' . implode(',', array_keys(Order::SALE_TYPE_LABELS)),
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'send_email' => 'boolean',
        ]);

        $query = Order::with('user')
            ->where('status', 'paid')
            ->whereHas('user');

        if ($request->filled('sale_type')) {
            $query->saleType($request->sale_type);
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
            'sale_type' => 'nullable|string|in:' . implode(',', array_keys(Order::SALE_TYPE_LABELS)),
        ]);

        $query = Order::with('user')
            ->where('status', 'paid')
            ->whereHas('user');

        if ($request->filled('sale_type')) {
            $query->saleType($request->sale_type);
        }

        $orders = $query->limit(50)->get();
        $users = $orders->pluck('user')->unique('id');

        return response()->json([
            'count' => $users->count(),
            'users' => $users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]),
        ]);
    }
}
