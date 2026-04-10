<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\TaskLists;
use App\Models\Tasks;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    //
    public function index(Request $request): Response
    {
        $query = Tasks::query()->with('list:nTaskListNo,cTaskListName,cTaskListsColor');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('cTaskName', 'like', '%' . $request->search . '%')
                    ->orWhere('cTasksDescription', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('cTaskPriority')) {
            $query->where('cTaskPriority', $request->cTaskPriority);
        }

        if ($request->filled('nTaskListNo')) {
            $query->where('nTaskListNo', $request->nTaskListNo);
        }

        $tasks = $query->latest()->paginate(10)->withQueryString();

        $lists = TaskLists::select(['nTaskListNo', 'cTaskListName', 'cTaskListsColor'])->get();

        return Inertia::render('tasks/Tasks', [
            'tasks' => $tasks,
            'lists' => $lists,
            'filters' => $request->only(['search', 'cTaskPriority', 'nTaskListNo']),
        ]);
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cTaskName' => ['required', 'string', 'max:255'],
            'cTasksDescription' => ['nullable', 'string'],
            'cTaskPriority' => ['required', 'string', 'max:64'],
            'nTaskListNo' => ['required', 'exists:TASK_LISTS,nTaskListNo']
        ]);

        $validated['cCompleted'] = false; // new tasks are always incomplete
        Tasks::create($validated);

        return redirect()->back();
    }


    public function update(Request $request, Tasks $task): RedirectResponse
    {
        $validated = $request->validate([
            'cTaskName' => ['required', 'string', 'max:255'],
            'cTasksDescription' => ['nullable', 'string', 'max: 255'],
            'cTaskPriority' => ['required', 'string', 'max:64'], 
            'cCompleted' => ['required', 'boolean'],
            'nTaskListNo' => ['required', 'exists:TASK_LISTS,nTaskListNo'],
        ]);

        $validated['cCompleted'] = (bool) ($validated['cCompleted'] ?? false);
        $validated['cTaskPriority'] = $validated['cTaskPriority'] ?? 'Normal';

        $task->update($validated);

        return redirect()->back();
    }


    public function destroy(Tasks $task): RedirectResponse
    {
        $task->delete();

        return redirect()->back();
    }
}
