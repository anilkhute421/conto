<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmNotificationModel extends Model
{
    use HasFactory;
    protected $table = 'pm_notifications';
    protected $fillable = ['title','message','property_manager_id','message_language'];
}
