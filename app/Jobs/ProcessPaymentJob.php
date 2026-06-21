<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class ProcessPaymentJob implements ShouldQueue
{
    use Queueable;

    public int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(): void
{
    $order = Order::find($this->orderId);

    if (!$order) {
        return;
    }

    /*
    ====================================
    STEP 1
    CEK USER
    ====================================
    */

    $userResponse = Http::get(
        'http://localhost:8080/users/' . $order->user_id
    );

    if (!$userResponse->successful()) {

        $order->update([
            'status' => 'FAILED'
        ]);

        return;
    }

    /*
    ====================================
    STEP 2
    POTONG SALDO
    ====================================
    */

    $deductResponse = Http::post(
        'http://localhost:8080/users/deduct-balance/' . $order->user_id,
        [
            'amount' => $order->amount
        ]
    );

    if (!$deductResponse->successful()) {

        $order->update([
            'status' => 'FAILED'
        ]);

        return;
    }

    /*
    ====================================
    STEP 3
    BUAT PAYMENT
    ====================================
    */

    $mutation = '
    mutation {
      insert_payments_one(
        object: {
          order_id: ' . $order->id . ',
          amount: ' . $order->amount . ',
          payment_method: "E-WALLET",
          status: "SUCCESS",
          paid_at: "2026-06-20T00:00:00+07:00"
        }
      ) {
        id
      }
    }';

    $response = Http::withHeaders([
        'x-hasura-admin-secret' => 'admin123'
    ])->post(
        'http://localhost:8081/v1/graphql',
        [
            'query' => $mutation
        ]
    );

    /*
    ====================================
    STEP 4
    UPDATE ORDER
    ====================================
    */

    $order->update([
        'status' => 'SUCCESS'
    ]);
}
}
