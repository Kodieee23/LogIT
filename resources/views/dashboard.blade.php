@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem; display: flex; flex-direction: column; gap: 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.25rem;">Dashboard</h1>
            <p style="color: var(--text-muted);">Overview of IT support activities.</p>
        </div>
        
        <!-- Filter Form -->
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

            <div style="position: relative;">
                <i class='bx bx-search' style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks..." class="form-control" style="padding-left: 2.25rem; min-width: 200px; height: 100%; border-radius: var(--radius-sm);" onchange="document.getElementById('dashboard-filter-form').submit()">
            </div>
            <select name="time" class="js-custom-select" onchange="document.getElementById('dashboard-filter-form').submit()">
                <option value="all" {{ (request('time') === 'all') ? 'selected' : '' }}>All Time</option>
                <option value="today" {{ (request('time', 'today') === 'today') ? 'selected' : '' }}>Today</option>
                <option value="week" {{ (request('time') === 'week') ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ (request('time') === 'month') ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ (request('time') === 'year') ? 'selected' : '' }}>This Year</option>
            </select>
            <input type="hidden" name="status" value="{{ request('status', 'all') }}">
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
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="glass-card stat-card">
        <div class="icon" style="color: var(--primary);">
            <i class='bx bx-task'></i>
        </div>
        <h3>Total Tasks</h3>
        <div class="value">{{ $total_tasks ?? 0 }}</div>
    </div>
    
    <div class="glass-card stat-card" style="border-left: 4px solid var(--status-red);">
        <div class="icon" style="color: var(--status-red);">
            <i class='bx bx-error-circle'></i>
        </div>
        <h3>Unresolved</h3>
        <div class="value">{{ $unresolved_tasks ?? 0 }}</div>
    </div>
    
    <div class="glass-card stat-card" style="border-left: 4px solid var(--status-green);">
        <div class="icon" style="color: var(--status-green);">
            <i class='bx bx-check-circle'></i>
        </div>
        <h3>Completed</h3>
        <div class="value">{{ $completed_tasks ?? 0 }}</div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="dashboard-grid" style="{{ (Auth::user() && Auth::user()->role === 'admin') ? 'grid-template-columns: 1fr;' : '' }}">
    <!-- Left Column: Activity Feed -->
    <div>
        <div class="glass-card" style="height: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 1.5rem; font-weight: 700; margin: 0;">
                    {{ $status_filter === 'unresolved' ? 'Unresolved Tasks (Notifications)' : 'Recent Task Logs' }}
                </h2>
                
                <div class="status-filters">
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" class="filter-pill {{ request('status', 'all') === 'all' ? 'active' : '' }}">All</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'unresolved']) }}" class="filter-pill {{ request('status') === 'unresolved' ? 'active' : '' }}">Unresolved</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'completed']) }}" class="filter-pill {{ request('status') === 'completed' ? 'active' : '' }}">Completed</a>
                </div>
            </div>
            
            <div class="log-feed">
                @if (empty($recent_logs))
                    <p style="color: var(--text-muted); text-align: center; padding: 2rem 0;">No tasks found for this filter.</p>
                @else
                    @foreach($recent_logs as $log)
                        <div class="log-item">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                                <div style="font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; display: flex; justify-content: space-between;">
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
                            <p style="font-size: 0.95rem; color: var(--text-main); line-height: 1.6; word-wrap: break-word;">
                                {!! nl2br(e($log->description)) !!}
                            </p>
                            <div class="log-meta" style="margin-top: 0.5rem;">
                                @if (Auth::user() && Auth::user()->role === 'admin')
                                    <span><i class='bx bx-user'></i> {{ $log->user->full_name ?? 'Unknown' }}</span>
                                @endif
                                <span><i class='bx bx-buildings'></i> {{ $log->department }} ({{ $log->staff_helped }})</span>
                                <span><i class='bx bx-calendar'></i> {{ $log->created_at->format('M j, Y - h:i A') }}</span>
                            </div>
                            
                            @if (!(Auth::user() && Auth::user()->role === 'admin') && $log->user_id == Auth::id() && $log->priority !== 'green')
                                <form action="{{ route('dashboard.task.update', $log->id) }}" method="POST" style="margin-top: 1rem; display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="new_status" class="js-custom-select" style="min-width: 140px;">
                                        <option value="green">Mark Resolved</option>
                                        <option value="yellow" {{ $log->priority === 'yellow' ? 'selected' : '' }}>Mark Medium</option>
                                        <option value="red" {{ $log->priority === 'red' ? 'selected' : '' }}>Mark Urgent</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem; margin-left: 0.5rem;">Update</button>
                                </form>
                            @endif
                            
                            <div class="task-comments" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                                @foreach($log->comments as $comment)
                                    <div style="background: rgba(0,0,0,0.05); padding: 0.5rem; border-radius: var(--radius-sm); margin-bottom: 0.5rem; font-size: 0.9rem;">
                                        <strong style="color: var(--primary);">{{ $comment->user->full_name ?? 'Unknown' }}</strong>
                                        <span style="color: var(--text-muted); font-size: 0.8rem; margin-left: 0.5rem;">{{ $comment->created_at->diffForHumans() }}</span>
                                        <p style="margin: 0.25rem 0 0 0; color: var(--text-main); line-height: 1.4;">{{ $comment->comment }}</p>
                                    </div>
                                @endforeach
                                <form action="{{ route('dashboard.task.comment', $log->id) }}" method="POST" style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                                    @csrf
                                    <input type="text" name="comment" class="form-control" placeholder="Add a comment..." required style="padding: 0.5rem; font-size: 0.9rem;">
                                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Send</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Log Task Form (Staff Only) -->
    @if (!(Auth::user() && Auth::user()->role === 'admin'))
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
