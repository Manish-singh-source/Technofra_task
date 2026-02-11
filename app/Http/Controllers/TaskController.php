<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Staff;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('project', 'attachments')->orderByDesc('created_at')->get();
        $staff = Staff::all()->keyBy('id');
        $today = now()->startOfDay();

        // Calculate summary statistics
        $runningTasks = $tasks->where('status', 'in_progress')->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $lateTasks = $tasks->filter(function ($task) use ($today) {
            return $task->deadline
                && $task->deadline->lt($today)
                && !in_array($task->status, ['completed', 'cancelled', 'on_hold'], true);
        })->count();
        $delayedTasks = $tasks->where('status', 'on_hold')->count();

        return view('task', compact('tasks', 'staff', 'runningTasks', 'completedTasks', 'lateTasks', 'delayedTasks'));
    }

    public function create(Request $request)
    {
        $projects = Project::orderBy('project_name')->get();
        $staff = Staff::orderBy('first_name')->orderBy('last_name')->get();
        $selectedProjectId = $request->query('project_id');

        return view('add-task', compact('projects', 'staff', 'selectedProjectId'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_title' => 'required|string|max:255',
            'project_related' => 'nullable|exists:projects,id',
            'priority' => 'nullable|in:High,Medium,Low',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:staff,id',
            'followers' => 'nullable|array',
            'followers.*' => 'exists:staff,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'task_description' => 'nullable|string',
            'attach_files' => 'nullable|array',
            'attach_files.*' => 'file|max:10240', // 10MB max per file
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Convert priority to lowercase
        $priority = strtolower($request->priority ?? 'medium');

        // Tags are already an array from the form
        $tags = $request->tags ?? [];

        $task = Task::create([
            'title' => $request->task_title,
            'description' => $request->task_description,
            'project_id' => $request->project_related,
            'followers' => $request->followers,
            'assignees' => $request->assignees,
            'tags' => $tags,
            'status' => 'not_started',
            'priority' => $priority,
            'start_date' => $request->start_date,
            'deadline' => $request->due_date,
        ]);

        // Handle file uploads
        if ($request->hasFile('attach_files')) {
            $files = $request->file('attach_files');
            foreach ($files as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('task_attachments', $fileName, 'public');

                TaskAttachment::create([
                    'task_id' => $task->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('task')->with('success', 'Task created successfully!');
    }

    public function show($id)
    {
        $task = Task::with('project', 'attachments')->findOrFail($id);
        $staff = Staff::all()->keyBy('id');

        return view('task-details', compact('task', 'staff'));
    }

    public function edit($id)
    {
        $task = Task::with('attachments')->findOrFail($id);
        $projects = \App\Models\Project::all();
        $staff = Staff::all();

        return view('edit-task', compact('task', 'projects', 'staff'));
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'task_title' => 'required|string|max:255',
            'project_related' => 'nullable|exists:projects,id',
            'priority' => 'nullable|in:High,Medium,Low',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:staff,id',
            'followers' => 'nullable|array',
            'followers.*' => 'exists:staff,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'task_description' => 'nullable|string',
            'status' => 'nullable|in:not_started,in_progress,on_hold,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Convert priority to lowercase
        $priority = strtolower($request->priority ?? 'medium');

        // Tags are already an array from the form
        $tags = $request->tags ?? [];

        $task->update([
            'title' => $request->task_title,
            'description' => $request->task_description,
            'project_id' => $request->project_related,
            'followers' => $request->followers,
            'assignees' => $request->assignees,
            'tags' => $tags,
            'status' => $request->status ?? 'not_started',
            'priority' => $priority,
            'start_date' => $request->start_date,
            'deadline' => $request->due_date,
        ]);

        return redirect()->route('task')->with('success', 'Task updated successfully!');
    }
}
