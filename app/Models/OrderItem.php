<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model { protected $guarded = []; protected $casts = ['quantity'=>'decimal:3','qty_received'=>'decimal:3','qty_delivered'=>'decimal:3','purchase_rate'=>'decimal:2','selling_rate'=>'decimal:2']; public function order(){return $this->belongsTo(Order::class);} }
