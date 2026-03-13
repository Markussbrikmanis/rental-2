<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('invoice_number_format')->default('{year}-{num}')->after('role');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_number_unique');
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['number']);
            $table->unique('number');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('invoice_number_format');
        });
    }
};
