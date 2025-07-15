<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    public function index()
    {
        $module = Module::all();
        return view('admin.modules.index', compact('module'));
    }

    public function create()
    {
        return view('admin.modules.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:modules'],
        ]);

        Module::create([
            'name' => $request->name,
        ]);

        return redirect()->route('modules.index')->with('success', 'Module created successfully!');
    }

    public function edit(Module $module)
    {
        return view('admin.modules.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('modules')->ignore($module->id)],
        ]);

        $module->update([
            'name' => $request->name,
        ]);

        return redirect()->route('modules.index')->with('success', 'Module updated successfully!');
    }

    public function destroy(Module $module)
    {
        $module->delete();
        return redirect()->route('modules.index')->with('success', 'Module deleted successfully!');
    }
}
