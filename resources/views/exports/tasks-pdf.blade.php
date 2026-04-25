<!DOCTYPE html>
<html>
<head>
    <title>Tasks Export</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>LogIT Tasks Report</h2>
    <p>Generated on {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Logged By</th>
                <th>Department</th>
                <th>Staff Helped</th>
                <th>Category</th>
                <th>Priority</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>{{ $task->id }}</td>
                    <td>{{ $task->created_at->format('Y-m-d') }}</td>
                    <td>{{ $task->user->full_name ?? 'N/A' }}</td>
                    <td>{{ $task->department }}</td>
                    <td>{{ $task->staff_helped }}</td>
                    <td>{{ $task->category->name ?? 'N/A' }}</td>
                    <td>{{ strtoupper($task->priority) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
