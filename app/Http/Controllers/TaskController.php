<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\models\TaskLists;
use App\models\Tasks;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    //

    // public function index(TaskLists $list): Response
    // {
    //     $tasks = Tasks::query()
    //     ->where('nTaskListNo', $list->nTaskListNo)
    //     ->select(['nTaskNo', 'cTaskName', 'cTaskDescription', 'dTaskDueDate', 'bTaskCompleted', 'created_at'])
    //     ->latest()
    //     ->get();

    //     return Inertia::render('tasks/index', [
    //         'list' => $list,
    //         'tasks' => $tasks
    //     ]); 
    // }


    public function index(Request $request): Response {
        $query = Tasks::query()->with('list: cTaskNo, cTaskName, cTaskDescription');

        if($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('cTaskName', 'like', '%' . $request->search . '%')
                  ->orWhere('cTaskDescription', 'like', '%' . $request->search . '%');
            });
        }

        if($request->filled('cTaskPriority')) {
            $query->where('cTaskPriority', $request->cTaskPriority);
        }

        if($request->filled('cTaskListNo')) {
            $query->where('nTaskListNo', $request->cTaskListNo);
        }

        $tasks = $query->latest()->paginate(10)->withQueryString();

        $lists = TaskLists::select(['nTaskListNo', 'cTaskListName', 'cTaskListsColor'])->get();

        return Inertia::render('tasks/index', [
            'tasks' => $tasks,
            'lists' => $lists,
            'filters' => $request->only(['search', 'cTaskPriority', 'cTaskListNo']),
        ]);
    }


    public function restore(Request $request): RedirectResponse {
        $validated = $request->validate([
            'cTaskName' => ['required', 'string', 'max:255'],
            'cTaskDescription' => ['nullable', 'string'],
            'cTaskPriority' => ['required', 'string', 'max:64'],  // Assuming priority is a string like 'High', 'Medium', 'Low'
            'cCompleted' => ['required', 'boolean'],
            'nTaskListNo' => ['required', 'exists:lists,nTaskListNo'],
        ]);

        $validated['cCompleted'] = (bool) ($validated['cCompleted'] ?? false);
        $validated['cTaskPriority'] = $validated['cTaskPriority'] ?? 'Normal';

        Tasks::withTrashed()->create($validated);

        return redirect()->back();
    }


    public function update(Request $request, Tasks $task): RedirectResponse {
        $validated = $request->validate([
            'cTaskName' => ['required', 'string', 'max:255'],
            'cTaskDescription' => ['nullable', 'string'],
            'cTaskPriority' => ['required', 'string', 'max:64'],  // Assuming priority is a string like 'High', 'Medium', 'Low'
            'cCompleted' => ['required', 'boolean'],
            'nTaskListNo' => ['required', 'exists:lists,nTaskListNo'],
        ]);

        $validated['cCompleted'] = (bool) ($validated['cCompleted'] ?? false);
        $validated['cTaskPriority'] = $validated['cTaskPriority'] ?? 'Normal';

        $task->update($validated);

        return redirect()->back();
    }


    public function destroy(Tasks $task): RedirectResponse {
        $task->delete();

        return redirect()->back();
    }
}
