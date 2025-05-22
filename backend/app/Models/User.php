<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'expire_time',
        'current_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'expire_time' => 'datetime',
    ];
    
    /**
     * 判断用户是否为管理员
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    /**
     * 判断用户是否为分销商
     */
    public function isDistributor()
    {
        return $this->role === 'distributor';
    }
    
    /**
     * 获取用户的分销商资料
     */
    public function distributor()
    {
        return $this->hasOne(Distributor::class);
    }
    
    /**
     * 获取邀请该用户的分销商
     */
    public function inviter()
    {
        return $this->belongsToMany(Distributor::class, 'distributor_user')
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }
    
    /**
     * 获取用户的充值记录
     */
    public function rechargeRecords()
    {
        return $this->hasMany(RechargeRecord::class);
    }
}
 