<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskLog;
use App\Models\Category;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $isAdmin = auth()->user()->role === 'admin';
        $currentUserId = auth()->id();

        $query = TaskLog::with(['user', 'category', 'comments.user'])->latest();

        if (!$isAdmin) {
            $query->where('user_id', $currentUserId);
        } elseif ($request->filled('staff_id') && $request->staff_id !== 'all') {
            $query->where('user_id', $request->staff_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('department', 'like', "%{$search}%")
                  ->orWhere('staff_helped', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $status_filter = $request->get('status', 'all');
        $time = $request->get('time', $status_filter === 'unresolved' ? 'all' : 'today');
        
        if ($time !== 'all') {
            $now = \Carbon\Carbon::now();
            if ($time === 'today') {
                $query->whereDate('created_at', $now->toDateString());
            } elseif ($time === 'week') {
                $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
            } elseif ($time === 'month') {
                $query->whereMonth('created_at', $now->month)
                      ->whereYear('created_at', $now->year);
            } elseif ($time === 'year') {
                $query->whereYear('created_at', $now->year);
            }
        }

        $allFilteredLogs = $query->get();

        $total_tasks = $allFilteredLogs->where('department', '!=', 'System Auth')->count();
        $unresolved_tasks = $allFilteredLogs->whereIn('priority', ['yellow', 'red'])->count();
        $completed_tasks = $allFilteredLogs->where('department', '!=', 'System Auth')->where('priority', 'green')->count();

        if ($status_filter === 'unresolved') {
            $recent_logs = $allFilteredLogs->whereIn('priority', ['yellow', 'red']);
        } elseif ($status_filter === 'completed') {
            $recent_logs = $allFilteredLogs->where('priority', 'green');
        } else {
            $recent_logs = $allFilteredLogs;
        }

        $categories = Category::all();
        $all_staff = User::where('role', 'staff')->get();

        return view('dashboard', compact(
            'recent_logs', 'categories', 'all_staff', 
            'total_tasks', 'unresolved_tasks', 'completed_tasks', 'status_filter'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'department' => 'required|string|max:255',
            'staff_helped' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:green,yellow,red',
        ]);

        TaskLog::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'department' => $request->department,
            'staff_helped' => $request->staff_helped,
            'description' => $request->description,
            'priority' => $request->priority,
        ]);

        return redirect()->route('dashboard')->with('success_msg', 'Task logged successfully.');
    }

    public function updateStatus(Request $request, TaskLog $task)
    {
        $request->validate([
            'new_status' => 'required|in:green,yellow,red',
        ]);

        if ($task->user_id !== auth()->id()) {
            abort(403);
        }

        $task->update(['priority' => $request->new_status]);

        return back()->with('success_msg', 'Task status updated successfully.');
    }

    public function storeComment(Request $request, TaskLog $task)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $task->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return back()->with('success_msg', 'Comment added.');
    }

    public function unresolvedCount()
    {
        // Admin sees all, Staff sees their own
        if (auth()->user()->role === 'admin') {
            $count = TaskLog::whereIn('priority', ['yellow', 'red'])->count();
        } else {
            $count = TaskLog::where('user_id', auth()->id())
                ->whereIn('priority', ['yellow', 'red'])
                ->count();
        }
        return response()->json(['count' => $count]);
    }
}
