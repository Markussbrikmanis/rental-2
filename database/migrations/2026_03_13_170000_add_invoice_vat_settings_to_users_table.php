<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('invoice_vat_enabled')->default(false)->after('invoice_logo_path');
            $table->decimal('invoice_vat_rate', 5, 2)->default(21)->after('invoice_vat_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'invoice_vat_enabled',
                'invoice_vat_rate',
            ]);
        });
    }
};
