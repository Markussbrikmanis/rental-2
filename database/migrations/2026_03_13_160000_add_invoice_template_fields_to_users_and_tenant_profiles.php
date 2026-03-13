<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('invoice_sender_name')->nullable()->after('invoice_number_format');
            $table->text('invoice_sender_address')->nullable()->after('invoice_sender_name');
            $table->string('invoice_sender_registration_number')->nullable()->after('invoice_sender_address');
            $table->string('invoice_sender_vat_number')->nullable()->after('invoice_sender_registration_number');
            $table->string('invoice_sender_bank_name')->nullable()->after('invoice_sender_vat_number');
            $table->string('invoice_sender_swift_code')->nullable()->after('invoice_sender_bank_name');
            $table->string('invoice_sender_account_number')->nullable()->after('invoice_sender_swift_code');
            $table->text('invoice_payment_terms_text')->nullable()->after('invoice_sender_account_number');
            $table->text('invoice_footer_text')->nullable()->after('invoice_payment_terms_text');
            $table->string('invoice_logo_path')->nullable()->after('invoice_footer_text');
        });

        Schema::table('tenant_profiles', function (Blueprint $table): void {
            $table->string('billing_name')->nullable()->after('registration_number');
            $table->text('billing_address')->nullable()->after('billing_name');
            $table->string('billing_registration_number')->nullable()->after('billing_address');
            $table->string('billing_vat_number')->nullable()->after('billing_registration_number');
            $table->string('billing_bank_name')->nullable()->after('billing_vat_number');
            $table->string('billing_swift_code')->nullable()->after('billing_bank_name');
            $table->string('billing_account_number')->nullable()->after('billing_swift_code');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_profiles', function (Blueprint $table): void {
            $table->dropColumn([
                'billing_name',
                'billing_address',
                'billing_registration_number',
                'billing_vat_number',
                'billing_bank_name',
                'billing_swift_code',
                'billing_account_number',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'invoice_sender_name',
                'invoice_sender_address',
                'invoice_sender_registration_number',
                'invoice_sender_vat_number',
                'invoice_sender_bank_name',
                'invoice_sender_swift_code',
                'invoice_sender_account_number',
                'invoice_payment_terms_text',
                'invoice_footer_text',
                'invoice_logo_path',
            ]);
        });
    }
};
