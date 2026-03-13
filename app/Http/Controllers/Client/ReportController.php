<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\OwnerReportExportService;
use App\Services\OwnerReportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, OwnerReportService $ownerReportService): View
    {
        $report = $ownerReportService->build($request->user(), $request->all());

        return view('client.reports.index', [
            'report' => $report,
        ]);
    }

    public function export(Request $request, OwnerReportExportService $ownerReportExportService): Response|StreamedResponse
    {
        $validated = $request->validate(
            [
                'format' => ['required', Rule::in(['pdf', 'csv', 'xlsx'])],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        return $ownerReportExportService->download(
            $request->user(),
            $request->all(),
            $validated['format'],
        );
    }
}
