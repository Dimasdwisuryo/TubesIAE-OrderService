<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Jobs\ProcessPaymentJob;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $order = Order::create([
            'user_id' => $request->user_id,
            'game_slug' => $request->game_slug,
            'item_name' => $request->item_name,
            'amount' => $request->amount,
            'status' => 'PENDING'
        ]);

        ProcessPaymentJob::dispatch($order->id);

        return response()->json([
            'message' => 'Order berhasil dibuat',
            'order_id' => $order->id
        ], 201);
    }

    public function show(int $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan'
            ], 404);
        }

        return response()->json($order);
    }
}
