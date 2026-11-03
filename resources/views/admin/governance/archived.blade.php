@extends('admin.layouts.app')

@section('page_title', 'Archived Accounts')
@section('page_description', 'Restore field or administrator accounts without recreating identities or losing historical ownership.')

@section('content')
<div class="row">
    <div class="col-md-7"><div class="x_panel"><div class="x_title"><h2>Field accounts <small>{{ $users->count() }} archived</small></h2><div class="clearfix"></div></div><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Name</th><th>Username</th><th>Archived</th><th></th></tr></thead><tbody>
    @forelse($users as $user)<tr><td><strong>{{ $user->name }}</strong><br><small>User type #{{ $user->user_type_id }}</small></td><td>{{ $user->username }}</td><td>{{ optional($user->deleted_at)->format('M d, Y H:i') }}</td><td class="text-right"><button type="button" class="btn btn-success btn-xs" onclick="submitAdminAction('{{ route('SuperAdmin.governance.restore',['user',$user->id]) }}','PATCH','Restore this account?')">Restore</button></td></tr>
    @empty<tr><td colspan="4" class="text-center text-muted" style="padding:30px">No archived field accounts.</td></tr>@endforelse
    </tbody></table></div></div></div>
    <div class="col-md-5"><div class="x_panel"><div class="x_title"><h2>Administrators <small>{{ $administrators->count() }} archived</small></h2><div class="clearfix"></div></div><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Administrator</th><th>Archived</th><th></th></tr></thead><tbody>
    @forelse($administrators as $administrator)<tr><td><strong>{{ $administrator->name }}</strong><br><small>{{ $administrator->email }}</small></td><td>{{ optional($administrator->deleted_at)->format('M d, Y H:i') }}</td><td class="text-right"><button type="button" class="btn btn-success btn-xs" onclick="submitAdminAction('{{ route('SuperAdmin.governance.restore',['administrator',$administrator->id]) }}','PATCH','Restore this administrator?')">Restore</button></td></tr>
    @empty<tr><td colspan="3" class="text-center text-muted" style="padding:30px">No archived administrators.</td></tr>@endforelse
    </tbody></table></div></div></div>
</div>
@endsection
