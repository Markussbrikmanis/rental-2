<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceDocumentService
{
    public function __construct(
        private readonly InvoiceStatusService $invoiceStatusService,
    ) {
    }

    public function view(Invoice $invoice): View
    {
        return view('client.invoices.document', $this->viewData($invoice, 'browser'));
    }

    public function pdfResponse(Invoice $invoice): Response
    {
        return Pdf::loadView('client.invoices.document', $this->viewData($invoice, 'pdf'))
            ->setPaper('a4')
            ->download($this->fileName($invoice));
    }

    public function pdfBinary(Invoice $invoice): string
    {
        return Pdf::loadView('client.invoices.document', $this->viewData($invoice, 'pdf'))
            ->setPaper('a4')
            ->output();
    }

    public function fileName(Invoice $invoice): string
    {
        $number = Str::slug($invoice->number ?: 'invoice');

        return 'invoice-'.$number.'.pdf';
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(Invoice $invoice, string $renderMode = 'browser'): array
    {
        $invoice->loadMissing([
            'lease.propertyUnit.property.user',
            'lease.tenantProfile',
            'lines.source',
            'payments',
        ]);

        $invoice = $this->invoiceStatusService->refresh($invoice);

        $owner = $invoice->lease->propertyUnit->property->user;
        $tenant = $invoice->lease->tenantProfile;
        $subtotalWithoutTax = (float) $invoice->subtotal;
        $vatEnabled = (bool) $owner->invoice_vat_enabled;
        $vatRate = $vatEnabled ? (float) $owner->invoice_vat_rate : 0.0;
        $vatTotal = round(((float) $invoice->total) - $subtotalWithoutTax, 2);

        return [
            'invoice' => $invoice,
            'sender' => [
                'name' => $owner->invoice_sender_name ?: $owner->name,
                'address' => $owner->invoice_sender_address,
                'registration_number' => $owner->invoice_sender_registration_number,
                'vat_number' => $owner->invoice_sender_vat_number,
                'bank_name' => $owner->invoice_sender_bank_name,
                'swift_code' => $owner->invoice_sender_swift_code,
                'account_number' => $owner->invoice_sender_account_number,
            ],
            'recipient' => [
                'name' => $tenant->billing_name ?: $tenant->company_name ?: $tenant->full_name,
                'address' => $tenant->billing_address,
                'registration_number' => $tenant->billing_registration_number ?: $tenant->registration_number,
                'vat_number' => $tenant->billing_vat_number,
                'bank_name' => $tenant->billing_bank_name,
                'swift_code' => $tenant->billing_swift_code,
                'account_number' => $tenant->billing_account_number,
            ],
            'logo_data_uri' => $this->logoDataUri($owner->invoice_logo_path),
            'payment_terms_text' => $owner->invoice_payment_terms_text
                ?: __('app.rental.documents.default_payment_terms', [
                    'due_date' => $invoice->due_date->locale(app()->getLocale())->translatedFormat('Y. \\g\\a\\d\\a j. F'),
                    'days' => max($invoice->issue_date->diffInDays($invoice->due_date), 0),
                ]),
            'footer_text' => $owner->invoice_footer_text
                ?: __('app.rental.documents.default_footer_text'),
            'subtotal_without_tax' => $subtotalWithoutTax,
            'tax_total' => $vatTotal,
            'vat_enabled' => $vatEnabled,
            'vat_rate' => $vatRate,
            'total_in_words' => $this->amountInWords((float) $invoice->total),
            'render_mode' => $renderMode,
        ];
    }

    private function amountInWords(float $amount): string
    {
        $euros = (int) floor($amount);
        $cents = (int) round(($amount - $euros) * 100);

        if (class_exists(\NumberFormatter::class)) {
            $formatter = new \NumberFormatter(app()->getLocale(), \NumberFormatter::SPELLOUT);
            $eurosInWords = $formatter->format($euros);

            if (is_string($eurosInWords) && $eurosInWords !== '') {
                return Str::ucfirst($eurosInWords).' euro un '.str_pad((string) $cents, 2, '0', STR_PAD_LEFT).' centi';
            }
        }

        return number_format($amount, 2, ',', ' ').' EUR';
    }

    private function logoDataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path) ?: 'image/png';

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }
}
