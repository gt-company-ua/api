<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportCategory extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['created_at', 'updated_at', 'show_osago', 'show_zk'];
    protected $casts = ['show_osago' => 'bool', 'show_zk' => 'bool'];

    public function powers()
    {
        return $this->hasMany(TransportPower::class);
    }
}
