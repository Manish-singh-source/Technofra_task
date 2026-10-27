<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderStatusHistory extends Model {
    protected $table = 'order_status_history';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['changed_at' => 'datetime', 'meta' => 'array'];
    public function order() { return $this->belongsTo(Order::class); }
    public function user() { return $this->belongsTo(User::class, 'changed_by'); }
}
