 <?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('assignedTo')->latest()->get();
        $users = User::where('id', '!=', auth()->id())->get();
        $allUsers = User::all();

        return view('tasks', compact('tasks', 'users', 'allUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
            'category' => 'required|in:development,design,testing',
            'priority' => 'required|in:low,medium,high',
            'deadline' => 'required|date',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'assigned_to' => $validated['assigned_to'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'deadline' => $validated['deadline'],
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    public function updateStatus(Task $task, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,in-progress,completed',
        ]);

        $task->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }
}
