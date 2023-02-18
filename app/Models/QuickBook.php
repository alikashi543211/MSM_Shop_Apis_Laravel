<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickBook extends Model
{
    use HasFactory;
    protected $fillable = [
        'access_token_key',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'access_token_validation_period',
        'refresh_token_validation_period',
        'client_id',
        'client_secret',
        'real_mid',
    ];
}
