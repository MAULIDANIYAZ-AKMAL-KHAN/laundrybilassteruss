<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laundry_items', function (Blueprint $table) {
            $table->string('estimated_duration')->default('Belum ditentukan');
        });
    }

    public function down(): void
    {
        Schema::table('laundry_items', function (Blueprint $table) {
            $table->dropColumn('estimated_duration');
        });
    }
};
