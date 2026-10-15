<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_shippings')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('user_id')
            ->get()
            ->each(function (object $user): void {
                $hasDefault = DB::table('user_shippings')
                    ->where('user_id', $user->user_id)
                    ->where('is_default', true)
                    ->exists();

                if (!$hasDefault) {
                    $firstAddress = DB::table('user_shippings')
                        ->where('user_id', $user->user_id)
                        ->orderBy('id')
                        ->first();

                    if ($firstAddress) {
                        DB::table('user_shippings')
                            ->where('id', $firstAddress->id)
                            ->update(['is_default' => true]);
                    }
                }
            });
    }

    public function down(): void
    {
        // The migration only promotes an existing address; it never removes one.
    }
};
