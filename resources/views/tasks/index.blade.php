@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem; display: flex; flex-direction: column; gap: 1rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.25rem;">Tasks</h1>
            <p style="color: var(--text-muted);">View and filter historical tasks.</p>
        </div>
        
        <!-- Filter Form -->
        <form action="{{ route('tasks.index') }}" method="GET" class="filter-bar" id="tasks-filter-form" style="margin: 0; align-items: center; gap: 0.75rem;">
            @if (Auth::user() && Auth::user()->role === 'admin')
                <select name="staff_id" class="js-custom-select" onchange="document.getElementById('tasks-filter-form').submit()">
                    <option value="all">All Staff</option>
                    @foreach($all_staff ?? [] as $staff)
                        <option value="{{ $staff->id }}" {{ (request('staff_id') == $staff->id) ? 'selected' : '' }}>
                            {{ $staff->full_name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <div style="position: relative; flex: 1 1 200px;">
                <i class='bx bx-search' style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks..." class="form-control" style="padding-left: 2.25rem; min-width: 200px; height: 100%; border-radius: var(--radius-sm);" onchange="document.getElementById('tasks-filter-form').submit()">
            </div>
            
            <select name="status" class="js-custom-select" onchange="document.getElementById('tasks-filter-form').submit()">
                <option value="all" {{ (request('status') === 'all' || !request('status')) ? 'selected' : '' }}>All Status</option>
                <option value="unresolved" {{ (request('status') === 'unresolved') ? 'selected' : '' }}>Unresolved</option>
                <option value="completed" {{ (request('status') === 'completed') ? 'selected' : '' }}>Completed</option>
            </select>

            <select name="time" class="js-custom-select" onchange="document.getElementById('tasks-filter-form').submit()">
                <option value="all" {{ (request('time') === 'all' || !request('time')) ? 'selected' : '' }}>All Time</option>
                <option value="today" {{ (request('time') === 'today') ? 'selected' : '' }}>Today</option>
                <option value="week" {{ (request('time') === 'week') ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ (request('time') === 'month') ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ (request('time') === 'year') ? 'selected' : '' }}>This Year</option>
            </select>
        </form>
    </div>
</div>

<div class="glass-card" style="margin-top: 1rem;">
    <div class="log-feed">
        @if ($tasks->isEmpty())
            <p style="color: var(--text-muted); text-align: center; padding: 3rem 0;">No tasks found matching your criteria.</p>
        @else
            @foreach($tasks as $log)
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
                    
                    @if($log->comments->count() > 0)
                    <div class="task-comments" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(0,0,0,0.05);">
                        @foreach($log->comments as $comment)
                            <div style="background: rgba(0,0,0,0.02); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 0.5rem; font-size: 0.9rem; border: 1px solid rgba(0,0,0,0.03);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                    <strong style="color: var(--primary);">{{ $comment->user->full_name ?? 'Unknown' }}</strong>
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p style="margin: 0; color: var(--text-main); line-height: 1.4;">{{ $comment->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                    @endif
                    
                    <form action="{{ route('dashboard.task.comment', $log->id) }}" method="POST" style="display: flex; gap: 0.5rem; margin-top: 1rem; padding-top: {{ $log->comments->count() > 0 ? '0' : '1rem' }}; border-top: {{ $log->comments->count() > 0 ? 'none' : '1px solid rgba(0,0,0,0.05)' }};">
                        @csrf
                        <input type="text" name="comment" class="form-control" placeholder="Add a comment..." required style="padding: 0.6rem 1rem; font-size: 0.9rem; border-radius: var(--radius-pill); background: rgba(255,255,255,0.8);">
                        <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.25rem; font-size: 0.85rem; border-radius: var(--radius-pill);"><i class='bx bx-send'></i> Post</button>
                    </form>
                </div>
            @endforeach
            
            <div style="margin-top: 1.5rem;">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
