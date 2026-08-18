<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class UsernameGenerator
{
    public static function fromName(string $name): string
    {
        $base = Str::slug($name, '');
        $base = $base !== '' ? Str::substr($base, 0, 24) : 'user';

        return self::firstAvailable($base);
    }

    protected static function firstAvailable(string $base): string
    {
        $reserved = config('reserved-usernames');
        $candidate = $base;
        $suffix = 0;

        while (
            in_array(strtolower($candidate), $reserved, true)
            || User::where('username', $candidate)->exists()
        ) {
            $suffix++;
            $candidate = $base.$suffix;
        }

        return $candidate;
    }
}