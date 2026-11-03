<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Model\ImportBatch;
use App\Services\Admin\PollingStationImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportController extends Controller
{
    public function index()
    {
        return view('admin.imports.index', ['batches' => ImportBatch::latest('id')->paginate(20)]);
    }

    public function preview(Request $request, PollingStationImportService $service)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);
        $batch = $service->preview($request->file('file'), Auth::guard('superAdmin')->id());
        return redirect()->route('SuperAdmin.imports.show', $batch)->with('message', 'Import preview created. Review all rows before committing.');
    }

    public function show(ImportBatch $batch)
    {
        return view('admin.imports.show', ['batch' => $batch, 'rows' => $batch->rows()->paginate(50)]);
    }

    public function commit(ImportBatch $batch, PollingStationImportService $service)
    {
        $service->commit($batch);
        return redirect()->back()->with('message', 'Polling stations imported successfully.');
    }

    public function rollback(ImportBatch $batch, PollingStationImportService $service)
    {
        $service->rollback($batch);
        return redirect()->back()->with('message', 'Import batch rolled back safely.');
    }
}
