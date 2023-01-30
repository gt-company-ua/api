<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderInsurant extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $hidden = ['id', 'order_id', 'created_at', 'updated_at'];

    public function getFullnameAttribute()
    {
        $fullname = [$this->surname, $this->name, $this->patronymic];

        return implode(' ', $fullname);
    }
}
