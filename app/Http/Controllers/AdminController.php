<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all();
        $categories = Category::all();

        $tasksByCategory = \App\Models\TaskLog::select('category_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->where('department', '!=', 'System Auth')
            ->groupBy('category_id')
            ->with('category')
            ->get();
        
        $chartLabels = [];
        $chartData = [];
        foreach ($tasksByCategory as $taskCount) {
            $chartLabels[] = $taskCount->category ? $taskCount->category->name : 'Uncategorized';
            $chartData[] = $taskCount->total;
        }

        return view('admin', compact('users', 'categories', 'chartLabels', 'chartData'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,staff',
        ]);

        User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin')->with('success_msg', 'User created successfully.');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'role' => 'required|in:admin,staff',
        ]);

        $user->update([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'role' => $request->role,
        ]);

        return redirect()->route('admin')->with('success_msg', 'User updated successfully.');
    }

    public function destroyUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error_msg', 'You cannot delete yourself.');
        }

        $user->delete();
        return redirect()->route('admin')->with('success_msg', 'User deleted successfully.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password),
        ]);

        return back()->with('success_msg', "Password for {$user->username} has been reset successfully.");
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:categories']);
        Category::create(['name' => $request->name]);
        return redirect()->route('admin')->with('success_msg', 'Category added.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|string|max:255|unique:categories,name,' . $category->id]);
        $category->update(['name' => $request->name]);
        return redirect()->route('admin')->with('success_msg', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        // Prevent deleting categories that are in use by catching constraint violation
        try {
            $category->delete();
            return redirect()->route('admin')->with('success_msg', 'Category deleted.');
        } catch (\Exception $e) {
            return redirect()->route('admin')->with('error_msg', 'Cannot delete category because it is in use.');
        }
    }

    public function exportCsv()
    {
        $tasks = \App\Models\TaskLog::with(['user', 'category'])->where('department', '!=', 'System Auth')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=tasks.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Date', 'Logged By', 'Department', 'Staff Helped', 'Category', 'Priority', 'Description'];

        $callback = function() use($tasks, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($tasks as $task) {
                fputcsv($file, [
                    $task->id,
                    $task->created_at->format('Y-m-d H:i:s'),
                    $task->user->full_name ?? 'Unknown',
                    $task->department,
                    $task->staff_helped,
                    $task->category->name ?? 'Uncategorized',
                    $task->priority,
                    $task->description
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $tasks = \App\Models\TaskLog::with(['user', 'category'])->where('department', '!=', 'System Auth')->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.tasks-pdf', compact('tasks'));
        return $pdf->download('tasks.pdf');
    }
}
