<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Page extends Model
{
    use HasUlids;

    protected $fillable = ['title', 'slug', 'theme', 'is_public', 'is_listed'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateSlug(string $title, int $userId): string
    {
        $base = Str::slug($title);

        if ($base === '') {
            $base = 'page';
        }

        $slug = $base;
        $suffix = 1;

        while (self::where('user_id', $userId)->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }

    public static function slugRules(int $userId, ?string $ignoreId = null): array
    {
        return [
            'required',
            'string',
            'max:255',
            'alpha_dash',
            Rule::unique('pages')
                ->where('user_id', $userId)
                ->ignore($ignoreId),
        ];
    }

    public function links() 
    {
        return $this->hasMany(Link::class)->orderBy('position');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}