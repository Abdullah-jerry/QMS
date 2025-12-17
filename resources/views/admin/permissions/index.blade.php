@extends('layouts.app')

@section('title', 'Manage Permissions')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Manage Permissions</h2>
    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">Create Permission</a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Name</th>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $permissions->links() }}
    </div>
</div>
@endsection
