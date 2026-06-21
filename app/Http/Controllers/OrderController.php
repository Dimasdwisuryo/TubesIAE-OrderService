<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Jobs\ProcessPaymentJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function store(Request $request)
{
    /*
    ==========================
    AMBIL DATA GAME
    ==========================
    */

    $gameResponse = Http::get(
        'http://localhost:8001/api/games/' .
        $request->game_slug
    );

    if (!$gameResponse->successful()) {

        return response()->json([
            'message' => 'Game tidak ditemukan'
        ], 404);
    }

    $game = $gameResponse->json();

    /*
    ==========================
    CARI DENOMINASI
    ==========================
    */

    $selectedDenom = null;

    foreach ($game['denominations'] as $denom) {

        if ($denom['id'] == $request->denomination_id) {
            $selectedDenom = $denom;
            break;
        }
    }

    if (!$selectedDenom) {

        return response()->json([
            'message' => 'Denominasi tidak ditemukan'
        ], 404);
    }

    /*
    ==========================
    SIMPAN ORDER
    ==========================
    */

    $order = Order::create([
        'user_id' => $request->user_id,
        'game_slug' => $request->game_slug,
        'item_name' => $selectedDenom['name'],
        'amount' => $selectedDenom['price'],
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
