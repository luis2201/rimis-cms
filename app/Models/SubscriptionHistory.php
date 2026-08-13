<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SubscriptionHistory extends Model
{
    protected $guarded=[];
    public function subscription(){return $this->belongsTo(Subscription::class);}
    public function changedBy(){return $this->belongsTo(User::class,'changed_by');}
}
