<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
  protected $fillable = [
    'order_id',
    'email',
    'info',
    'status'
  ];

  public function order()
  {
    return $this->belongsTo(Order::class, 'order_id', 'order_id');
  }
}
