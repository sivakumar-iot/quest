<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Module;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function show()
    {
        return redirect()->route('questions.index');
    }
    /**
     * Display a listing of the questions.
     */
    public function index()
    {
        $questions = Question::with(['module', 'topic'])->get();
        return view('admin.questions.index', compact('questions'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create()
    {
        $modules = Module::all();
        // Topics will be loaded dynamically via AJAX in the frontend
        return view('admin.questions.create', compact('modules'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request)
    {
        Log::info("QuestionController::store called");

        $validator = Validator::make($request->all(), [
            'module_id' => ['required', 'exists:modules,id'],
            'topic_id' => ['required', 'exists:topics,id'],
            'question_text' => ['required', 'string'],
            'question_type' => ['required', Rule::in(['multiple_choice', 'yes_no'])],
            'options' => ['array', 'nullable'],
            'options.*' => ['string', 'max:255', 'nullable'],
            'correct_answer_mc' => ['required_if:question_type,multiple_choice', 'integer'],
            'correct_answer_yn' => ['required_if:question_type,yes_no', Rule::in(['yes', 'no'])],
            'timer_enabled' => ['boolean'],
            'timer_value' => ['required_if:timer_enabled,true', 'integer'],
            'is_enabled' => ['boolean'],
            'is_random_options' => ['boolean'],
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();
        Log::info("Validated data: " . json_encode($validatedData));

        $options = null;
        $correctAnswer = null;

        if ($validatedData['question_type'] === 'multiple_choice') {
            // Filter out empty options and re-index
            $options = array_values(array_filter($validatedData['options']));
            $correctAnswer = [$options[$validatedData['correct_answer_mc']]]; // Store the actual option text
        } elseif ($validatedData['question_type'] === 'yes_no') {
            $correctAnswer = [$validatedData['correct_answer_yn']];
        }

        Log::info("Options: " . json_encode($options));

        Question::create([
            'module_id' => $validatedData['module_id'],
            'topic_id' => $validatedData['topic_id'],
            'question_text' => $validatedData['question_text'],
            'question_type' => $validatedData['question_type'],
            'options' => $options, // Stored as JSON
            'correct_answer' => $correctAnswer, // Stored as JSON
            'timer_enabled' => $request->has('timer_enabled'),
            'timer_value' => $validatedData['timer_value'] ?? null,
            'is_enabled' => $request->has('is_enabled'),
            'is_random_options' => $request->has('is_random_options'),
        ]);

        Log::info("Question created successfully");

        return redirect()->route('questions.index')->with('success', 'Question created successfully!');
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Question $question)
    {
        $modules = Module::all();
        // Topics for the selected module will be pre-loaded in the view via JS or passed if needed
        return view('admin.questions.edit', compact('question', 'modules'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(Request $request, Question $question)
    {
        $validator = Validator::make($request->all(), [
            'module_id' => ['required', 'exists:modules,id'],
            'topic_id' => ['required', 'exists:topics,id'],
            'question_text' => ['required', 'string'],
            'question_type' => ['required', Rule::in(['multiple_choice', 'yes_no'])],
            'options' => ['array', 'nullable'],
            'options.*' => ['string', 'max:255', 'nullable'],
            'correct_answer_mc' => ['required_if:question_type,multiple_choice', 'integer'],
            'correct_answer_yn' => ['required_if:question_type,yes_no', Rule::in(['yes', 'no'])],
            'timer_enabled' => ['boolean'],
            'timer_value' => ['required_if:timer_enabled,true', 'integer'],
            'is_enabled' => ['boolean'],
            'is_random_options' => ['boolean'],
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }

        $validatedData = $validator->validated();

        $options = null;
        $correctAnswer = null;

        if ($validatedData['question_type'] === 'multiple_choice') {
            $options = array_values(array_filter($validatedData['options']));
            $correctAnswer = [$options[$validatedData['correct_answer_mc']]];
        } elseif ($validatedData['question_type'] === 'yes_no') {
            $correctAnswer = [$validatedData['correct_answer_yn']];
        }

        $question->update([
            'module_id' => $validatedData['module_id'],
            'topic_id' => $validatedData['topic_id'],
            'question_text' => $validatedData['question_text'],
            'question_type' => $validatedData['question_type'],
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'timer_enabled' => $request->has('timer_enabled'),
            'timer_value' => $validatedData['timer_value'] ?? null,
            'is_enabled' => $request->has('is_enabled'),
            'is_random_options' => $request->has('is_random_options'),
        ]);

        return redirect()->route('questions.index')->with('success', 'Question updated successfully!');
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('questions.index')->with('success', 'Question deleted successfully!');
    }

    public function bulk(Request $request)
    {
        return "bulk upload";
    }
}
