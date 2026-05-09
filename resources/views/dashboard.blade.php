@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem; display: flex; flex-direction: column; gap: 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.25rem;">Dashboard</h1>
            <p style="color: var(--text-muted);">Overview of IT support activities.</p>
        </div>
        
        <!-- Filter Form for Stats -->
        <form action="{{ route('dashboard') }}" method="GET" class="filter-bar" id="dashboard-filter-form" style="margin: 0; align-items: center; gap: 0.75rem;">
            @if (Auth::user() && Auth::user()->role === 'admin')
                <select name="staff_id" class="js-custom-select" onchange="document.getElementById('dashboard-filter-form').submit()">
                    <option value="all">All Staff</option>
                    @foreach($all_staff ?? [] as $staff)
                        <option value="{{ $staff->id }}" {{ (request('staff_id') == $staff->id) ? 'selected' : '' }}>
                            {{ $staff->full_name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <select name="time" class="js-custom-select" onchange="document.getElementById('dashboard-filter-form').submit()">
                <option value="all" {{ (request('time') === 'all') ? 'selected' : '' }}>All Time</option>
                <option value="today" {{ (request('time', 'today') === 'today') ? 'selected' : '' }}>Today</option>
                <option value="week" {{ (request('time') === 'week') ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ (request('time') === 'month') ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ (request('time') === 'year') ? 'selected' : '' }}>This Year</option>
            </select>
        </form>
    </div>
</div>

@if (session('success_msg'))
    <div class="alert alert-success" style="background-color: var(--status-green-bg); color: var(--status-green); border: 1px solid rgba(16,185,129,0.2);">
        <i class='bx bx-check-circle'></i> {{ session('success_msg') }}
    </div>
@endif
@if (session('error_msg'))
    <div class="alert alert-error">
        <i class='bx bx-error-circle'></i> {{ session('error_msg') }}
    </div>
@endif

<!-- Stats Area -->
<div class="stats-grid" style="max-width: 1000px;">
    <div class="glass-card stat-card">
        <div class="icon" style="color: var(--primary);">
            <i class='bx bx-task'></i>
        </div>
        <h3>Total Tasks</h3>
        <div class="value">{{ $total_tasks ?? 0 }}</div>
    </div>
    
    <div class="glass-card stat-card status-red">
        <div class="icon" style="color: var(--status-red);">
            <i class='bx bx-error-circle'></i>
        </div>
        <h3>Unresolved</h3>
        <div class="value">{{ $unresolved_tasks ?? 0 }}</div>
    </div>
    
    <div class="glass-card stat-card status-green">
        <div class="icon" style="color: var(--status-green);">
            <i class='bx bx-check-circle'></i>
        </div>
        <h3>Completed</h3>
        <div class="value">{{ $completed_tasks ?? 0 }}</div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="dashboard-grid">
    <!-- Left Column: Activity Feeds -->
    <div style="display: flex; flex-direction: column; gap: 2rem;">
        
        @if ($unresolved_logs && $unresolved_logs->count() > 0)
        <!-- Attention Required -->
        <div class="glass-card" style="border-left: 4px solid var(--status-red);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0; color: var(--status-red); display: flex; align-items: center; gap: 0.5rem;">
                    <i class='bx bx-error-circle'></i> Tasks Requiring Attention
                </h2>
            </div>
            
            <div class="log-feed">
                @foreach($unresolved_logs as $log)
                    <div class="log-item" style="border-color: rgba(239, 68, 68, 0.2); background: rgba(239, 68, 68, 0.02);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                                @if($log->department === 'System Auth')
                                    <span class="badge red" style="display: flex; align-items: center; gap: 0.25rem;">
                                        <i class='bx bx-error-circle'></i> SYSTEM ALERT
                                    </span>
                                @else
                                    <span class="badge" style="background: var(--bg-tertiary); color: var(--text-main); display: flex; align-items: center; gap: 0.25rem;">
                                        <i class='bx bx-folder'></i> {{ $log->category->name ?? 'Uncategorized' }}
                                    </span>
                                @endif
                                <span class="badge {{ $log->priority }}">
                                    @if($log->priority === 'red') <i class='bx bxs-circle' style="font-size: 0.5rem; margin-right: 0.25rem;"></i> URGENT
                                    @elseif($log->priority === 'yellow') <i class='bx bxs-circle' style="font-size: 0.5rem; margin-right: 0.25rem;"></i> MEDIUM
                                    @endif
                                </span>
                            </div>
                        </div>
                        <p style="font-size: 0.95rem; color: var(--text-main); line-height: 1.6; word-wrap: break-word; margin-bottom: 0;">
                            {!! nl2br(e($log->description)) !!}
                        </p>
                        <div class="log-meta" style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid rgba(0,0,0,0.05);">
                            @if (Auth::user() && Auth::user()->role === 'admin')
                                <span><i class='bx bx-user'></i> {{ $log->user->full_name ?? 'Unknown' }}</span>
                            @endif
                            <span><i class='bx bx-buildings'></i> {{ $log->department }} ({{ $log->staff_helped }})</span>
                            <span><i class='bx bx-calendar'></i> {{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        
                        <div style="margin-top: 1rem;">
                            <a href="{{ route('tasks.index') }}?search={{ urlencode($log->department) }}" class="btn btn-outline" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">View Details</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent Tasks -->
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0;">
                    Recent Tasks
                </h2>
                <a href="{{ route('tasks.index') }}" style="font-size: 0.9rem; color: var(--primary); font-weight: 600;">View All <i class='bx bx-right-arrow-alt'></i></a>
            </div>
            
            <div class="log-feed">
                @if (empty($recent_logs) || $recent_logs->isEmpty())
                    <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No recent tasks.</p>
                @else
                    @foreach($recent_logs as $log)
                        <div class="log-item">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                                <div style="font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; display: flex; gap: 0.5rem;">
                                @if($log->department === 'System Auth')
                                    <span class="badge red" style="display: flex; align-items: center; gap: 0.25rem;">
                                        <i class='bx bx-error-circle'></i> SYSTEM ALERT
                                    </span>
                                @else
                                    <span class="badge" style="background: var(--bg-tertiary); color: var(--text-main); display: flex; align-items: center; gap: 0.25rem;">
                                        <i class='bx bx-folder'></i> {{ $log->category->name ?? 'Uncategorized' }}
                                    </span>
                                @endif
                                <span class="badge {{ $log->priority }}">
                                    @if($log->priority === 'red') <i class='bx bxs-circle' style="font-size: 0.5rem; margin-right: 0.25rem;"></i> URGENT
                                    @elseif($log->priority === 'yellow') <i class='bx bxs-circle' style="font-size: 0.5rem; margin-right: 0.25rem;"></i> MEDIUM
                                    @else <i class='bx bxs-check-circle' style="font-size: 0.8rem; margin-right: 0.25rem;"></i> RESOLVED
                                    @endif
                                </span>
                            </div>
                        </div>
                        <p style="font-size: 0.95rem; color: var(--text-main); line-height: 1.6; word-wrap: break-word;">
                            {!! nl2br(e($log->description)) !!}
                        </p>
                        <div class="log-meta" style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid rgba(0,0,0,0.05);">
                            @if (Auth::user() && Auth::user()->role === 'admin')
                                <span><i class='bx bx-user'></i> {{ $log->user->full_name ?? 'Unknown' }}</span>
                            @endif
                            <span><i class='bx bx-buildings'></i> {{ $log->department }} ({{ $log->staff_helped }})</span>
                            <span><i class='bx bx-calendar'></i> {{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Log Task Form (Staff Only) or Analytics (Admin) -->
    @if (Auth::user() && Auth::user()->role === 'admin')
    <div>
        <div class="glass-card" style="position: sticky; top: 2rem;">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i class='bx bx-pie-chart-alt-2' style="color: var(--primary);"></i> Tasks by Category</h3>
            <canvas id="tasksChart" width="400" height="200"></canvas>
        </div>
    </div>
    @else
    <div>
        <div class="glass-card" style="position: sticky; top: 2rem;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 700; color: var(--primary);">
                Log New Task
            </h2>
            <form action="{{ route('dashboard.task.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="js-custom-select" required>
                        <option value="">Select a category</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="department">Department</label>
                    <input type="text" name="department" id="department" class="form-control" required placeholder="e.g. HR, Finance">
                </div>

                <div class="form-group">
                    <label class="form-label" for="staff_helped">Staff Member Helped</label>
                    <input type="text" name="staff_helped" id="staff_helped" class="form-control" required placeholder="Name of staff">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3" required placeholder="What was done?"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="priority">Status / Priority</label>
                    <select name="priority" id="priority" class="js-custom-select" required>
                        <option value="green">🟢 Resolved / Completed</option>
                        <option value="yellow" selected>🟡 Medium (Needs monitoring)</option>
                        <option value="red">🔴 High (Unresolved/Urgent)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Notes (Optional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Follow-up actions or remarks"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Submit Log
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
@if (Auth::user() && Auth::user()->role === 'admin')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('tasksChart');
        if (ctx) {
            const tasksChart = new Chart(ctx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: {!! json_encode($chartLabels ?? []) !!},
                    datasets: [{
                        label: '# of Tasks',
                        data: {!! json_encode($chartData ?? []) !!},
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
        }
    });
</script>
@endif
@endpush
