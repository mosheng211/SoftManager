<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_enabled',
        'sort_order'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];
    
    /**
     * 获取所有启用的支付方式
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getEnabledMethods()
    {
        return self::where('is_enabled', true)
            ->orderBy('sort_order')
            ->get();
    }
}
