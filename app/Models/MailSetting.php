<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = [
        'enabled', 'host', 'port', 'encryption', 'username', 'password',
        'from_address', 'from_name',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'enabled' => 'boolean',
        'port' => 'integer',
        'password' => 'encrypted',
    ];
}
