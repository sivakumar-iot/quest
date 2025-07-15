<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\TestQuestion; // To get ordered questions for the test
use App\Models\Question; // To get question details
use App\Models\TestAnswer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log; // For logging potential cheating attempts
use Carbon\Carbon; // For date/time manipulation

class TestAttemptController extends Controller
{
    /**
     * Show the form for a student to enter test code and personal details.
     */
    public function showTestEntryForm()
    {
        // Clear any previous test attempt data from session
        Session::forget('current_test_attempt_id');
        return view('test.entry');
    }

    /**
     * Start a new test attempt.
     * Validates student details and test code, creates TestAttempt.
     */
    public function startTest(Request $request)
    {
        $validatedData = $request->validate([
            'test_code' => ['required', 'string', 'exists:tests,test_code'],
            'student_name' => ['required', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $test = Test::where('test_code', $validatedData['test_code'])
            ->where('is_enabled', true)
            ->first();

        if (!$test) {
            return back()->withErrors(['test_code' => 'Invalid or inactive test code.'])->withInput();
        }

        // Prevent starting a test if it has no questions
        if ($test->total_questions === 0) {
            return back()->withErrors(['test_code' => 'This test has no questions configured. Please contact the administrator.'])->withInput();
        }

        // Create a new test attempt
        $testAttempt = TestAttempt::create([
            'test_id' => $test->id,
            'student_name' => $validatedData['student_name'],
            'father_name' => $validatedData['father_name'],
            'dob' => $validatedData['dob'],
            'mobile' => $validatedData['mobile'],
            'email' => $validatedData['email'],
            'started_at' => Carbon::now(), // Record the precise start time
            'is_completed' => false,
            'total_questions_answered' => 0,
            'score' => 0,
        ]);

        // Store the test attempt ID in the session for subsequent requests
        Session::put('current_test_attempt_id', $testAttempt->id);

        return redirect()->route('test.show', ['testAttempt' => $testAttempt->id]);
    }

    /**
     * Display the test questions.
     */
    public function showTestPage(TestAttempt $testAttempt)
    {
        // Ensure the attempt belongs to the current session or is valid
        if (Session::get('current_test_attempt_id') != $testAttempt->id || $testAttempt->is_completed) {
            return redirect()->route('test.entry')->with('error', 'Invalid test attempt or test already completed.');
        }

        $test = $testAttempt->test;

        $totalDurationInSeconds = $test->duration_minutes * 60;
        $elapsedSeconds = Carbon::now()->diffInSeconds($testAttempt->started_at);
        $remainingSeconds = $totalDurationInSeconds - $elapsedSeconds;

        // Log for debugging
        Log::info('TestAttempt ID: ' . $testAttempt->id);
        Log::info('Started At: ' . $testAttempt->started_at->toDateTimeString());
        Log::info('Test Duration (minutes): ' . $test->duration_minutes);
        Log::info('Total Duration (seconds): ' . $totalDurationInSeconds);
        Log::info('Current Time (Carbon::now()): ' . Carbon::now()->toDateTimeString());
        Log::info('Elapsed Seconds: ' . $elapsedSeconds);
        Log::info('Calculated Remaining Seconds: ' . $remainingSeconds);

        if ($remainingSeconds <= 0) {
            Log::warning('Test auto-submitting due to remainingSeconds <= 0');
            return $this->autoSubmitTest($testAttempt);
        }

        // Load questions in their defined order, eager load the actual question details
        $testQuestions = $test->testQuestions()->with('question')->get();

        // Load existing answers for this attempt to pre-fill if navigating back
        $existingAnswers = $testAttempt->answers->keyBy('question_id');

        // Prepare data for JavaScript, ensuring options and answers are always arrays
        $questionsForJs = $testQuestions->map(function ($tq) use ($existingAnswers) {
            $question = $tq->question;
            // Ensure selected_options is always an array
            $answeredOptions = $existingAnswers->has($question->id) ? (array)$existingAnswers[$question->id]->selected_options : [];
            // Ensure options is always an array
            $options = (array)$question->options;

            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'options' => $options,
                'timer_enabled' => $question->timer_enabled,
                'timer_value' => $question->timer_value,
                'answered_options' => $answeredOptions, // Pre-filled answer
            ];
        });

        // Pass $questionsForJs instead of $testQuestions directly
        return view('test.show', compact('testAttempt', 'test', 'questionsForJs', 'remainingSeconds', 'existingAnswers'));
    }

    /**
     * Save a single answer via AJAX.
     */
    public function saveAnswer(Request $request, TestAttempt $testAttempt)
    {
        if (Session::get('current_test_attempt_id') != $testAttempt->id || $testAttempt->is_completed) {
            return response()->json(['message' => 'Invalid attempt or test completed.'], 403);
        }

        $validated = $request->validate([
            'question_id' => ['required', 'exists:questions,id'],
            'selected_options' => ['nullable', 'array'], // Can be empty for some question types or if not answered
            'selected_options.*' => ['nullable', 'string'],
        ]);

        $question = Question::find($validated['question_id']);
        if (!$question) {
            return response()->json(['message' => 'Question not found.'], 404);
        }

        // Find or create the answer record
        $answer = TestAnswer::updateOrCreate(
            [
                'test_attempt_id' => $testAttempt->id,
                'question_id' => $question->id,
            ],
            [
                'selected_options' => $validated['selected_options'] ?? null,
                // is_correct will be calculated on final submission
                'is_correct' => null,
            ]
        );

        // Update total questions answered count (only if it's a new answer or updated)
        $testAttempt->total_questions_answered = $testAttempt->answers()->count();
        $testAttempt->save();

        return response()->json(['message' => 'Answer saved successfully.']);
    }

    /**
     * Submit the test manually.
     */
    public function submitTest(Request $request, TestAttempt $testAttempt)
    {
        if (Session::get('current_test_attempt_id') != $testAttempt->id || $testAttempt->is_completed) {
            return redirect()->route('test.entry')->with('error', 'Invalid test attempt or test already completed.');
        }

        // Perform final scoring and mark as completed
        $this->processTestSubmission($testAttempt);

        Session::forget('current_test_attempt_id'); // Clear session
        return redirect()->route('test.results', ['testAttempt' => $testAttempt->id]);
    }

    /**
     * Handle automatic test submission (e.g., when timer runs out).
     */
    protected function autoSubmitTest(TestAttempt $testAttempt)
    {
        if ($testAttempt->is_completed) {
            return redirect()->route('test.results', ['testAttempt' => $testAttempt->id]);
        }

        // Perform final scoring and mark as completed
        $this->processTestSubmission($testAttempt);

        Session::forget('current_test_attempt_id'); // Clear session
        return redirect()->route('test.results', ['testAttempt' => $testAttempt->id])->with('info', 'Your test was automatically submitted due to time expiry.');
    }

    /**
     * Logic to calculate score and mark test as completed.
     */
    protected function processTestSubmission(TestAttempt $testAttempt)
    {
        $totalCorrect = 0;
        $testQuestions = $testAttempt->test->testQuestions()->with('question')->get();

        foreach ($testQuestions as $testQuestion) {
            $question = $testQuestion->question;
            $answer = $testAttempt->answers()->where('question_id', $question->id)->first();

            if ($answer) {
                $isCorrect = false;
                $selectedOptions = $answer->selected_options;
                $correctAnswer = $question->correct_answer; // This is an array from JSON cast

                if ($question->question_type === 'multiple_choice') {
                    // For multiple choice, selected_options is an array of selected values (e.g., ["Option A"])
                    // correct_answer is an array of correct values (e.g., ["Option A"])
                    // Compare the first element as we only expect one correct answer for simple MCQs
                    if (!empty($selectedOptions) && !empty($correctAnswer) && $selectedOptions[0] === $correctAnswer[0]) {
                        $isCorrect = true;
                    }
                } elseif ($question->question_type === 'yes_no') {
                    // For yes/no, selected_options is an array (e.g., ["yes"])
                    // correct_answer is an array (e.g., ["yes"])
                    if (!empty($selectedOptions) && !empty($correctAnswer) && $selectedOptions[0] === $correctAnswer[0]) {
                        $isCorrect = true;
                    }
                }
                // Add logic for other question types if implemented

                $answer->update(['is_correct' => $isCorrect]);
                if ($isCorrect) {
                    $totalCorrect++;
                }
            }
        }

        $testAttempt->update([
            'score' => $totalCorrect,
            'completed_at' => Carbon::now(),
            'is_completed' => true,
        ]);
    }

    /**
     * Show test results.
     */
    public function showResults(TestAttempt $testAttempt)
    {
        // Ensure the test is completed to show results
        if (!$testAttempt->is_completed) {
            return redirect()->route('test.entry')->with('error', 'Test not completed yet.');
        }

        $test = $testAttempt->test;
        $testQuestions = $testAttempt->test->testQuestions()->with('question')->get();
        $answers = $testAttempt->answers->keyBy('question_id');

        return view('test.results', compact('testAttempt', 'test', 'testQuestions', 'answers'));
    }

    /**
     * Log potential cheating attempts (e.g., tab switching).
     */
    public function logCheatingAttempt(Request $request, TestAttempt $testAttempt)
    {
        if (Session::get('current_test_attempt_id') != $testAttempt->id || $testAttempt->is_completed) {
            return response()->json(['message' => 'Invalid attempt or test completed.'], 403);
        }

        $type = $request->input('type', 'unknown');
        Log::warning("Cheating attempt detected for TestAttempt ID: {$testAttempt->id}. Type: {$type}");

        // You might want to update a field in TestAttempt to mark suspicious activity
        // For example, add a 'cheating_flags' JSON column to test_attempts table
        // $testAttempt->increment('cheating_warnings_count');
        // $testAttempt->save();

        return response()->json(['status' => 'logged']);
    }
}
