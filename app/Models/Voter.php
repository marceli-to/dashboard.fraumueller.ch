<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voter extends Model
{
    protected $fillable = [
        'ip_address',
        'hash',
    ];
}
