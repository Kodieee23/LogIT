@extends('layouts.app')

@section('content')
@if (session('success_msg'))
    <div class="alert alert-success" style="background-color: var(--status-green-bg); color: var(--status-green); border: 1px solid rgba(16,185,129,0.2); border-radius: var(--radius-sm);">
        <i class='bx bx-check-circle'></i> {{ session('success_msg') }}
    </div>
@endif
@if (session('error_msg'))
    <div class="alert alert-error" style="border-radius: var(--radius-sm);">
        <i class='bx bx-error-circle'></i> {{ session('error_msg') }}
    </div>
@endif

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.5rem;">Admin Panel</h1>
        <p style="color: var(--text-muted);">Manage users, categories, and system data.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('admin.export.csv') }}" class="btn" style="background: var(--status-green); color: white; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3); padding: 0.75rem 1.5rem;">
            <i class='bx bx-export'></i> Export CSV
        </a>
        <a href="{{ route('admin.export.pdf') }}" class="btn btn-outline" style="padding: 0.75rem 1.5rem; color: var(--status-red); border-color: var(--status-red);">
            <i class='bx bxs-file-pdf'></i> Export PDF
        </a>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Left Column: Lists (Users & Categories) -->
    <div>
        <div class="glass-card" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem;"><i class='bx bx-group'></i> System Users</h3>
            <div class="table-container">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users ?? [] as $user)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $user->full_name }}</div>
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>
                                    <span class="badge {{ $user->role === 'admin' ? 'red' : 'green' }}">
                                        {{ strtoupper($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                        <button onclick="editUser('{{ $user->id }}', '{{ addslashes($user->full_name) }}', '{{ addslashes($user->username) }}', '{{ $user->role }}')" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; border-color: var(--primary); color: var(--primary);">Edit</button>

                                        @if ($user->id !== Auth::id())
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; border-color: var(--status-red); color: var(--status-red);">Delete</button>
                                            </form>

                                            <form action="{{ route('admin.users.reset_password', $user->id) }}" method="POST" onsubmit="const pw = prompt('Enter a new password for {{ $user->username }} (min 6 characters):'); if(pw && pw.length >= 6){ this.new_password.value = pw; return true; } else if(pw) { alert('Password must be at least 6 characters.'); } return false;" style="margin: 0;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="new_password" value="">
                                                <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; border-color: var(--status-yellow); color: var(--status-yellow);">Reset Pass</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-card">
            <h3 style="margin-bottom: 1.5rem;"><i class='bx bx-list-ul'></i> Active Categories</h3>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @foreach($categories ?? [] as $cat)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background: rgba(0,0,0,0.05); border-radius: var(--radius-sm); border: 1px solid rgba(255,255,255,0.1);">
                        <form action="{{ route('admin.categories.update', $cat->id) }}" method="POST" style="display: flex; gap: 0.5rem; flex: 1;">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $cat->name }}" class="form-control" style="padding: 0.25rem 0.5rem; min-height: unset; height: auto;" required>
                            <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.85rem;">Save</button>
                        </form>
                        <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" style="margin-left: 0.5rem;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn" style="padding: 0.25rem 0.5rem; background: transparent; color: var(--status-red);"><i class='bx bx-trash'></i></button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column: Forms -->
    <div style="position: sticky; top: 2rem; display: flex; flex-direction: column; gap: 0;">
        <div class="glass-card">
            <h3 id="user-form-title" style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-user-plus' style="color: var(--primary);"></i> Add New User</h3>
            <form id="user-form" action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input type="text" id="user_full_name" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="user_username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="user_password" name="password" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" for="role">Role</label>
                    <select name="role" id="user_role" class="js-custom-select" style="width: 100%;">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" id="user-submit-btn" class="btn btn-primary" style="width: 100%;">Add User</button>
            </form>
        </div>

        <div class="glass-card">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-category' style="color: var(--primary);"></i> Add Category</h3>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="name">Category Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-outline" style="width: 100%;">Add Category</button>
            </form>
        </div>
        <div class="glass-card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-pie-chart-alt-2' style="color: var(--primary);"></i> Tasks by Category</h3>
            <canvas id="tasksChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editUser(id, fullName, username, role) {
        document.getElementById('user-form-title').innerHTML = "<i class='bx bx-edit' style='color: var(--primary);'></i> Edit User";
        const form = document.getElementById('user-form');
        form.action = `/admin/users/${id}`;
        if (!document.getElementById('method-put')) {
            form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT" id="method-put">');
        }
        document.getElementById('user_full_name').value = fullName;
        document.getElementById('user_username').value = username;
        document.getElementById('user_role').value = role;
        document.getElementById('user_password').required = false;
        document.getElementById('user_password').parentElement.style.display = 'none'; // hide password on edit
        document.getElementById('user-submit-btn').textContent = "Update User";
        
        if (!document.getElementById('cancel-edit-btn')) {
            const cancelBtn = document.createElement('button');
            cancelBtn.type = 'button';
            cancelBtn.id = 'cancel-edit-btn';
            cancelBtn.className = 'btn btn-outline';
            cancelBtn.style.marginTop = '0.5rem';
            cancelBtn.style.width = '100%';
            cancelBtn.textContent = 'Cancel Edit';
            cancelBtn.onclick = function() {
                document.getElementById('user-form-title').innerHTML = "<i class='bx bx-user-plus' style='color: var(--primary);'></i> Add New User";
                form.action = "{{ route('admin.users.store') }}";
                const methodPut = document.getElementById('method-put');
                if(methodPut) methodPut.remove();
                form.reset();
                document.getElementById('user_password').required = true;
                document.getElementById('user_password').parentElement.style.display = 'block';
                document.getElementById('user-submit-btn').textContent = "Add User";
                this.remove();
            };
            form.appendChild(cancelBtn);
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('tasksChart').getContext('2d');
        const tasksChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: '# of Tasks',
                    data: {!! json_encode($chartData) !!},
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.6)',
                        'rgba(16, 185, 129, 0.6)',
                        'rgba(245, 158, 11, 0.6)',
                        'rgba(239, 68, 68, 0.6)',
                        'rgba(139, 92, 246, 0.6)',
                        'rgba(236, 72, 153, 0.6)',
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(236, 72, 153, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#6b7280',
                            font: {
                                size: 13
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
