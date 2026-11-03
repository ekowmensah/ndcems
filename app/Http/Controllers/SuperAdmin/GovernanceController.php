<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Model\GovernanceAudit;
use App\SuperAdmin;
use App\User;
use Illuminate\Http\Request;

class GovernanceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'event' => ['nullable', 'string', 'max:120'],
            'actor' => ['nullable', 'string', 'max:255'],
            'method' => ['nullable', 'in:POST,PUT,PATCH,DELETE'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $audits = GovernanceAudit::query()
            ->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event', 'like', '%'.$event.'%'))
            ->when($filters['actor'] ?? null, fn ($query, $actor) => $query->where('actor_name', 'like', '%'.$actor.'%'))
            ->when($filters['method'] ?? null, fn ($query, $method) => $query->where('method', $method))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.governance.index', compact('audits', 'filters'));
    }

    public function archivedAccounts()
    {
        $users = User::onlyTrashed()->latest('deleted_at')->get();
        $administrators = SuperAdmin::onlyTrashed()->latest('deleted_at')->get();

        return view('admin.governance.archived', compact('users', 'administrators'));
    }

    public function restoreAccount(string $type, int $id)
    {
        abort_unless(in_array($type, ['user', 'administrator'], true), 404);

        $account = $type === 'user'
            ? User::onlyTrashed()->findOrFail($id)
            : SuperAdmin::onlyTrashed()->findOrFail($id);
        $account->restore();

        return redirect()->back()->with('message', 'Account restored successfully.');
    }
}
