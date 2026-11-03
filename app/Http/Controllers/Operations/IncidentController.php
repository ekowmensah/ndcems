<?php

namespace App\Http\Controllers\Operations;

use App\Services\Operations\IncidentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncidentController extends OperationsController
{
    public function index(Request $request, IncidentService $service)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:open,acknowledged,in_progress,resolved'],
            'severity' => ['nullable', 'in:low,medium,high,critical'],
            'assignment' => ['nullable', 'in:mine,unassigned'],
        ]);
        return view('operations.incidents', $service->pageData(Auth::user(), $filters) + $this->viewContext());
    }

    public function update(Request $request, int $id, IncidentService $service)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,acknowledged,in_progress,resolved'],
            'assigned_to' => ['nullable', 'integer'],
            'due_at' => ['nullable', 'date'],
            'resolution_note' => ['nullable', 'required_if:status,resolved', 'string', 'min:10', 'max:2000'],
        ]);
        $service->update(Auth::user(), $id, $data);
        return redirect()->back()->with('message', 'Incident updated successfully.');
    }
}
