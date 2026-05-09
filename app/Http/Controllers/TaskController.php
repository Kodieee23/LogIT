<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskLog;
use App\Models\Category;
use App\Models\User;

class TaskController extends Controller
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
        if ($status_filter === 'unresolved') {
            $query->whereIn('priority', ['yellow', 'red']);
        } elseif ($status_filter === 'completed') {
            $query->where('priority', 'green');
        }

        $time = $request->get('time', 'all');
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

        $tasks = $query->paginate(15)->withQueryString();
        $categories = Category::all();
        $all_staff = User::where('role', 'staff')->get();

        return view('tasks.index', compact('tasks', 'categories', 'all_staff', 'status_filter', 'time'));
    }
}
