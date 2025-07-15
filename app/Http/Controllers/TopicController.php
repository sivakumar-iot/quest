<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Module; // Import Module model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TopicController extends Controller
{
    /**
     * Display a listing of the topics.
     */
    public function index()
    {
        $topics = Topic::with('module')->get(); // Eager load module relationship
        return view('admin.topics.index', compact('topics'));
    }

    /**
     * Show the form for creating a new topic.
     */
    public function create()
    {
        $modules = Module::all(); // Get all modules for the dropdown
        return view('admin.topics.create', compact('modules'));
    }

    /**
     * Store a newly created topic in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Ensure topic name is unique within its module
                Rule::unique('topics')->where(function ($query) use ($request) {
                    return $query->where('module_id', $request->module_id);
                }),
            ],
            'module_id' => ['required', 'exists:modules,id'],
        ]);

        Topic::create([
            'name' => $request->name,
            'module_id' => $request->module_id,
        ]);

        return redirect()->route('topics.index')->with('success', 'Topic created successfully!');
    }

    /**
     * Show the form for editing the specified topic.
     */
    public function edit(Topic $topic)
    {
        $modules = Module::all(); // Get all modules for the dropdown
        return view('admin.topics.edit', compact('topic', 'modules'));
    }

    /**
     * Update the specified topic in storage.
     */
    public function update(Request $request, Topic $topic)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Ensure topic name is unique within its module, ignoring current topic
                Rule::unique('topics')->where(function ($query) use ($request) {
                    return $query->where('module_id', $request->module_id);
                })->ignore($topic->id),
            ],
            'module_id' => ['required', 'exists:modules,id'],
        ]);

        $topic->update([
            'name' => $request->name,
            'module_id' => $request->module_id,
        ]);

        return redirect()->route('topics.index')->with('success', 'Topic updated successfully!');
    }

    /**
     * Remove the specified topic from storage.
     */
    public function destroy(Topic $topic)
    {
        $topic->delete();
        return redirect()->route('topics.index')->with('success', 'Topic deleted successfully!');
    }
}
