<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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

    sleep(3);

    logger("Order {$order->id} dikirim ke Payment Service");
}

    }

