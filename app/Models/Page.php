<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasUlids;

    protected $fillable = ['title', 'slug', 'theme'];

    public function user() 
    {
        return $this->belongsTo(User::class);
    }
}
