<?php

namespace App\Http\Controllers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\models\TaskLists;
use Inertia\Inertia;
use Inertia\Response;

class ListController extends Controller
{
    //
    public function index(): Response
    {
        $lists = TaskLists::query()
        ->select(['nTaskListNo', 'cTaskListName', 'cTaskListsColor', 'created_at'])
        ->withCount('tasks')
        ->latest()
        ->get();

        return Inertia::render('lists/index', [
            'lists' => $lists
        ]); 
    }


    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cTaskListName' => ['required', 'string', 'max:255'],
            'cTaskListsColor' => ['required', 'string', 'max:64'], // Assuming color is a hex code
        ]);

        $list = TaskLists::create($validated);

        // return redirect()->route('lists.index')->with('success', 'Task list created successfully.');
        return redirect()->route('lists.index');

        // $request->validate([
        //     'nTaskListName' => 'required|string|max:255',
        //     'nTaskListColor' => 'required|string|max:7', // Assuming color is a hex code
        // ]);

        // TaskLists::create([
        //     'nTaskListName' => $request->nTaskListName,
        //     'nTaskListColor' => $request->nTaskListColor,
        // ]);

        // return redirect()->route('lists.index')->with('success', 'Task list created successfully.');
    }


    public function update(Request $request, TaskLists $list): RedirectResponse {
        $validated = $request->validate([
            'cTaskListName' => ['required', 'string', 'max:255'],
            'cTaskListsColor' => ['required', 'string', 'max:64'], // Assuming color is a hex code
        ]);

        $list->update($validated);

        return redirect()->route('lists.index');
    }


    public function destroy(TaskLists $list): RedirectResponse {
        $list->delete();

        return redirect()->route('lists.index');
    }
}
