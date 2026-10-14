<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

final class PasswordRules
{
    /**
     * Keep password creation and replacement rules identical across auth flows.
     */
    public static function required(): array
    {
        return [
            'required',
            'string',
            Password::min(8),
            'confirmed',
        ];
    }
}
