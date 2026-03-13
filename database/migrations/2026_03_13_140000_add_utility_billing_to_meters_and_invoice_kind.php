<?php

use App\Enums\InvoiceKind;
use App\Enums\UtilityBillingMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meters', function (Blueprint $table) {
            $table->string('utility_billing_mode')->default(UtilityBillingMode::None->value)->after('unit');
            $table->decimal('rate_per_unit', 12, 4)->nullable()->after('utility_billing_mode');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['lease_id', 'period_from', 'period_to']);
            $table->string('kind')->default(InvoiceKind::Standard->value)->after('period_to');
            $table->unique(['lease_id', 'period_from', 'period_to', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['lease_id', 'period_from', 'period_to', 'kind']);
            $table->dropColumn('kind');
            $table->unique(['lease_id', 'period_from', 'period_to']);
        });

        Schema::table('meters', function (Blueprint $table) {
            $table->dropColumn(['utility_billing_mode', 'rate_per_unit']);
        });
    }
};
