<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

//class AdminModel extends Model implements JWTSubject
class AdminModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'name', 'username', 'email', 'phone','password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // public function getJWTIdentifier()
    // {
    //    return $this->getKey();
    // }

    // public function getJWTCustomClaims()
    // {
    //     return [];
    // }

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

}
