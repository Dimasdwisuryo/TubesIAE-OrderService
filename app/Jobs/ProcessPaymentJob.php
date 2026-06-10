<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPaymentJob implements ShouldQueue
{
    use Queueable;

    public $orderId;

    public function __construct($orderId)
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

        $order->update([
            'status' => 'SUCCESS'
        ]);
    }
}
