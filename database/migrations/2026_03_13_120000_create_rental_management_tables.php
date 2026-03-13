<?php

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\NotificationChannel;
use App\Enums\PaymentMethod;
use App\Enums\PropertyUnitStatus;
use App\Enums\MeterType;
use App\Enums\MeterReadingSource;
use App\Enums\ChargeFrequency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('property_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default(PropertyUnitStatus::Vacant->value);
            $table->decimal('area', 10, 2)->nullable();
            $table->string('unit_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tenant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('personal_code')->nullable();
            $table->string('registration_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_profile_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('billing_start_date');
            $table->unsignedTinyInteger('due_day')->default(10);
            $table->string('currency')->default('EUR');
            $table->string('status')->default(LeaseStatus::Draft->value);
            $table->decimal('deposit', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('lease_charge_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 12, 2);
            $table->string('frequency')->default(ChargeFrequency::Monthly->value);
            $table->unsignedInteger('interval_count')->default(1);
            $table->string('interval_unit')->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('auto_invoice_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('period_from');
            $table->date('period_to');
            $table->string('status')->default(InvoiceStatus::Draft->value);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['lease_id', 'period_from', 'period_to']);
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->nullableMorphs('source');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->date('paid_at');
            $table->decimal('amount', 12, 2);
            $table->string('method')->default(PaymentMethod::BankTransfer->value);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_unit_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default(MeterType::Other->value);
            $table->string('unit')->default('gab.');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_id')->constrained()->cascadeOnDelete();
            $table->date('reading_date');
            $table->decimal('value', 12, 3);
            $table->string('source')->default(MeterReadingSource::Manual->value);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('kind')->default('overdue');
            $table->string('channel')->default(NotificationChannel::Email->value);
            $table->string('status')->default('pending');
            $table->string('recipient')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminders');
        Schema::dropIfExists('meter_readings');
        Schema::dropIfExists('meters');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('lease_charge_rules');
        Schema::dropIfExists('leases');
        Schema::dropIfExists('tenant_profiles');
        Schema::dropIfExists('property_units');
    }
};
