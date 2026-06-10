<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'game_slug',
        'item_name',
        'amount',
        'status'
    ];
}
