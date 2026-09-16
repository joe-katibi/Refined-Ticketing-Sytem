<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spec section 4.1 defines explicit ticket-number prefixes per visit type
 * (Installation=INS, Wi-Fi Extender=WiE, Support=SUP, Shifting=SHI). The store()
 * flow previously derived the prefix by stripping non-letters from `type_name` and
 * upper-casing the first 3 characters — this happens to work for "Support"->"SUP"
 * and "Shifting"->"SHI" but produces "WIF" (not "WiE") for "Wi-Fi Extender", so it
 * cannot represent the spec's prefixes for every type. Adding an explicit,
 * admin-editable prefix column removes the guesswork.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_types', function (Blueprint $table) {
            $table->string('code_prefix', 10)->nullable()->after('type_name');
        });

        // Backfill existing rows using the same derivation the app used previously,
        // so no existing type silently changes its ticket prefix.
        $types = \Illuminate\Support\Facades\DB::table('appointment_types')->get();
        foreach ($types as $type) {
            $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $type->type_name), 0, 3));
            if ($prefix === '') {
                $prefix = 'TKT';
            }
            \Illuminate\Support\Facades\DB::table('appointment_types')
                ->where('id', $type->id)
                ->update(['code_prefix' => $prefix]);
        }
    }

    public function down(): void
    {
        Schema::table('appointment_types', function (Blueprint $table) {
            $table->dropColumn('code_prefix');
        });
    }
};
