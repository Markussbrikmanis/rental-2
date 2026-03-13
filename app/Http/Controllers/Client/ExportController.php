<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\OwnerExportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function index(Request $request, OwnerExportService $ownerExportService): View
    {
        return view('client.exports.index', [
            'datasets' => $ownerExportService->options($request->user()),
        ]);
    }

    public function download(Request $request, OwnerExportService $ownerExportService): StreamedResponse
    {
        $datasetKeys = array_keys($ownerExportService->options($request->user()));

        $validated = $request->validate(
            [
                'dataset' => ['required', Rule::in($datasetKeys)],
                'format' => ['required', Rule::in(['csv', 'xlsx'])],
            ],
            trans('app.validation.messages'),
            trans('app.validation.attributes'),
        );

        return $ownerExportService->download(
            $request->user(),
            $validated['dataset'],
            $validated['format'],
        );
    }
}
