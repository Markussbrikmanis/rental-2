<?php

namespace App\Http\Controllers\Client;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice, InvoiceStatusService $invoiceStatusService): RedirectResponse
    {
        $this->ensureOwnedInvoice($request, $invoice);

        $invoice->payments()->create(
            $request->validate(
                [
                    'paid_at' => ['required', 'date'],
                    'amount' => ['required', 'numeric', 'min:0.01'],
                    'method' => ['required', Rule::in(PaymentMethod::values())],
                    'reference' => ['nullable', 'string', 'max:255'],
                    'notes' => ['nullable', 'string'],
                ],
                trans('app.validation.messages'),
                trans('app.validation.attributes'),
            ),
        );

        $invoiceStatusService->refresh($invoice->fresh());

        return redirect()
            ->route('client.invoices.show', $invoice)
            ->with('status', __('app.rental.payments.messages.recorded'));
    }

    private function ensureOwnedInvoice(Request $request, Invoice $invoice): void
    {
        abort_unless($invoice->lease->propertyUnit->property->user_id === $request->user()->id, 404);
    }
}
