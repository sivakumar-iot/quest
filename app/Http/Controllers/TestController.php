<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\Module;
use App\Models\Question; // Import Question model
use App\Models\TestRule; // Import TestRule model
use App\Models\TestQuestion; // Import TestQuestion model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // For database transactions
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    /**
     * Display a listing of the tests.
     */
    public function index()
    {
        $tests = Test::all();
        return view('admin.tests.index', compact('tests'));
    }

    /**
     * Show the form for creating a new test.
     */
    public function create()
    {
        $modules = Module::all();
        return view('admin.tests.create', compact('modules'));
    }

    /**
     * Store a newly created test in storage.
     */
    public function store(Request $request)
    {
        $Validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'test_code' => ['required', 'string', 'max:255', 'unique:tests'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'is_enabled' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'pass_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'rules' => ['required', 'array', 'min:1'], // Renamed from configurations to rules
            'rules.*.module_id' => ['nullable', 'exists:modules,id'],
            'rules.*.topic_id' => ['nullable', 'exists:topics,id'],
            'rules.*.question_type' => ['nullable', 'string', Rule::in(['multiple_choice', 'yes_no'])],
            'rules.*.number_of_questions' => ['required', 'integer', 'min:1'],
        ]);


        if ($Validator->fails()) {
            log::error('Validation failed', $Validator->errors()->toArray());
            return redirect()->back()->withErrors($Validator)->withInput();
        }

        $validatedData = $Validator->validated();
        Log::info("Validated data: " . json_encode($validatedData));

        // $validatedData = $request->validate([
        //     'name' => ['required', 'string', 'max:255'],
        //     'test_code' => ['required', 'string', 'max:255', 'unique:tests'],
        //     'duration_minutes' => ['required', 'integer', 'min:1'],
        //     'is_enabled' => ['nullable', 'boolean'],
        //     'description' => ['nullable', 'string'],
        //     'instructions' => ['nullable', 'string'],
        //     'pass_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
        //     'rules' => ['required', 'array', 'min:1'], // Renamed from configurations to rules
        //     'rules.*.module_id' => ['nullable', 'exists:modules,id'],
        //     'rules.*.topic_id' => ['nullable', 'exists:topics,id'],
        //     'rules.*.question_type' => ['nullable', 'string', Rule::in(['multiple_choice', 'yes_no'])],
        //     'rules.*.number_of_questions' => ['required', 'integer', 'min:1'],
        // ]);

        DB::beginTransaction();
        try {
            $test = Test::create([
                'name' => $validatedData['name'],
                'test_code' => $validatedData['test_code'],
                'duration_minutes' => $validatedData['duration_minutes'],
                'is_enabled' => $request->has('is_enabled'),
                'description' => $validatedData['description'],
                'instructions' => $validatedData['instructions'],
                'pass_percentage' => $validatedData['pass_percentage'],
                'total_questions' => 0, // Initialize, will update later
            ]);

            // Store test generation rules
            foreach ($validatedData['rules'] as $ruleData) {
                $test->rules()->create($ruleData); // Use rules() relationship
            }

            // Generate actual test questions based on rules
            $selectedQuestionIds = [];
            $questionOrder = 0;

            foreach ($test->rules as $rule) {
                $query = Question::query();

                if ($rule->module_id) {
                    $query->where('module_id', $rule->module_id);
                }
                if ($rule->topic_id) {
                    $query->where('topic_id', $rule->topic_id);
                }
                if ($rule->question_type) {
                    $query->where('question_type', $rule->question_type);
                }

                // Get questions that are enabled and not already selected for this test
                $questions = $query->where('is_enabled', true)
                    ->whereNotIn('id', $selectedQuestionIds) // Avoid duplicate questions in the same test
                    ->inRandomOrder()
                    ->limit($rule->number_of_questions)
                    ->get();

                foreach ($questions as $question) {
                    $test->testQuestions()->create([
                        'question_id' => $question->id,
                        'question_order' => ++$questionOrder,
                    ]);
                    $selectedQuestionIds[] = $question->id; // Add to already selected list
                }
            }

            // Update total_questions on the Test model
            $test->update(['total_questions' => count($selectedQuestionIds)]);

            DB::commit();
            return redirect()->route('tests.index')->with('success', 'Test created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error or return a more specific error message
            return back()->withInput()->withErrors(['error' => 'Failed to create test: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified test.
     */
    public function edit(Test $test)
    {
        $modules = Module::all();
        // Eager load rules to pass to the view
        $test->load('rules'); // Load rules relationship
        return view('admin.tests.edit', compact('test', 'modules'));
    }

    /**
     * Update the specified test in storage.
     */
    public function update(Request $request, Test $test)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'test_code' => ['required', 'string', 'max:255', Rule::unique('tests')->ignore($test->id)],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'is_enabled' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'pass_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'rules' => ['required', 'array', 'min:1'], // Renamed from configurations to rules
            'rules.*.module_id' => ['nullable', 'exists:modules,id'],
            'rules.*.topic_id' => ['nullable', 'exists:topics,id'],
            'rules.*.question_type' => ['nullable', 'string', Rule::in(['multiple_choice', 'yes_no'])],
            'rules.*.number_of_questions' => ['required', 'integer', 'min:1'],
        ]);

        DB::beginTransaction();
        try {
            $test->update([
                'name' => $validatedData['name'],
                'test_code' => $validatedData['test_code'],
                'duration_minutes' => $validatedData['duration_minutes'],
                'is_enabled' => $request->has('is_enabled'),
                'description' => $validatedData['description'],
                'instructions' => $validatedData['instructions'],
                'pass_percentage' => $validatedData['pass_percentage'],
            ]);

            // Delete existing rules and re-create them
            $test->rules()->delete();
            foreach ($validatedData['rules'] as $ruleData) {
                $test->rules()->create($ruleData);
            }

            // Re-generate actual test questions based on updated rules
            $test->testQuestions()->delete(); // Delete old selected questions
            $selectedQuestionIds = [];
            $questionOrder = 0;

            foreach ($test->rules as $rule) {
                $query = Question::query();

                if ($rule->module_id) {
                    $query->where('module_id', $rule->module_id);
                }
                if ($rule->topic_id) {
                    $query->where('topic_id', $rule->topic_id);
                }
                if ($rule->question_type) {
                    $query->where('question_type', $rule->question_type);
                }

                $questions = $query->where('is_enabled', true)
                    ->whereNotIn('id', $selectedQuestionIds)
                    ->inRandomOrder()
                    ->limit($rule->number_of_questions)
                    ->get();

                foreach ($questions as $question) {
                    $test->testQuestions()->create([
                        'question_id' => $question->id,
                        'question_order' => ++$questionOrder,
                    ]);
                    $selectedQuestionIds[] = $question->id;
                }
            }

            $test->update(['total_questions' => count($selectedQuestionIds)]);

            DB::commit();
            return redirect()->route('tests.index')->with('success', 'Test updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update test: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified test from storage.
     */
    public function destroy(Test $test)
    {
        // Deleting the test will cascade and delete associated rules and test_questions
        $test->delete();
        return redirect()->route('tests.index')->with('success', 'Test deleted successfully!');
    }
}
