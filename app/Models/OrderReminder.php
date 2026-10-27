<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderReminder extends Model { public $timestamps=false; protected $guarded=[]; protected $casts=['reminder_for_date'=>'date','sent_on'=>'date']; }
