<?php

use App\Enums\InvoiceKind;
use App\Enums\UtilityBillingMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('meters', 'utility_billing_mode')) {
            Schema::table('meters', function (Blueprint $table) {
                $table->string('utility_billing_mode')->default(UtilityBillingMode::None->value)->after('unit');
            });
        }

        if (! Schema::hasColumn('meters', 'rate_per_unit')) {
            Schema::table('meters', function (Blueprint $table) {
                $table->decimal('rate_per_unit', 12, 4)->nullable()->after('utility_billing_mode');
            });
        }

        Schema::table('invoices', function (Blueprint $table) {
            // MySQL may use the old composite unique index to satisfy the lease_id foreign key,
            // so add a dedicated lease_id index before dropping that unique key.
            if (! $this->indexExists('invoices', 'invoices_lease_id_index')) {
                $table->index('lease_id');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if ($this->indexExists('invoices', 'invoices_lease_id_period_from_period_to_unique')) {
                $table->dropUnique('invoices_lease_id_period_from_period_to_unique');
            }
        });

        if (! Schema::hasColumn('invoices', 'kind')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('kind')->default(InvoiceKind::Standard->value)->after('period_to');
            });
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (! $this->indexExists('invoices', 'invoices_lease_id_period_from_period_to_kind_unique')) {
                $table->unique(['lease_id', 'period_from', 'period_to', 'kind']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if ($this->indexExists('invoices', 'invoices_lease_id_period_from_period_to_kind_unique')) {
                $table->dropUnique('invoices_lease_id_period_from_period_to_kind_unique');
            }
        });

        if (Schema::hasColumn('invoices', 'kind')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('kind');
            });
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (! $this->indexExists('invoices', 'invoices_lease_id_period_from_period_to_unique')) {
                $table->unique(['lease_id', 'period_from', 'period_to']);
            }
        });

        Schema::table('meters', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('meters', 'utility_billing_mode')) {
                $dropColumns[] = 'utility_billing_mode';
            }

            if (Schema::hasColumn('meters', 'rate_per_unit')) {
                $dropColumns[] = 'rate_per_unit';
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $database = $connection->getDatabaseName();

        return $connection->table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }
};
