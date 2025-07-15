<!-- resources/views/test/results.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Test Results: ') . $test->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('info'))
                        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('info') }}</span>
                        </div>
                    @endif

                    <h3 class="text-2xl font-bold mb-4">Thank you, {{ $testAttempt->student_name }}!</h3>
                    <p class="mb-2">Your test has been successfully submitted.</p>

                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h4 class="text-xl font-semibold mb-3">Summary:</h4>
                        <p><strong>Test Name:</strong> {{ $test->name }}</p>
                        <p><strong>Student Name:</strong> {{ $testAttempt->student_name }}</p>
                        <p><strong>Started At:</strong> {{ $testAttempt->started_at->format('Y-m-d H:i:s') }}</p>
                        <p><strong>Completed At:</strong> {{ $testAttempt->completed_at->format('Y-m-d H:i:s') }}</p>
                        <p><strong>Your Score:</strong> {{ $testAttempt->score }} out of {{ $test->total_questions }}</p>
                        @if ($test->pass_percentage)
                            <p><strong>Pass Percentage:</strong> {{ $test->pass_percentage }}%</p>
                            <p><strong>Result:</strong>
                                @if (($testAttempt->score / $test->total_questions * 100) >= $test->pass_percentage)
                                    <span class="font-bold text-green-600">Passed!</span>
                                @else
                                    <span class="font-bold text-red-600">Failed.</span>
                                @endif
                            </p>
                        @endif
                    </div>

                    <div class="mt-8">
                        <h4 class="text-xl font-semibold mb-3">Your Answers:</h4>
                        @forelse ($testQuestions as $index => $testQuestion)
                            @php
                                $question = $testQuestion->question;
                                $answer = $answers[$question->id] ?? null;
                                $selectedOption = $answer ? ($answer->selected_options[0] ?? 'No Answer') : 'No Answer';
                                $isCorrect = $answer ? ($answer->is_correct ? 'Correct' : 'Incorrect') : 'N/A';
                                $correctAnswerText = $question->correct_answer[0] ?? 'N/A';
                            @endphp
                            <div class="mb-6 p-4 border rounded-lg {{ $answer && $answer->is_correct ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50' }}">
                                <p class="font-semibold">Q{{ $index + 1 }}: {{ $question->question_text }}</p>
                                <p class="text-sm text-gray-600">Type: {{ str_replace('_', ' ', ucfirst($question->question_type)) }}</p>
                                <p class="mt-2">Your Answer: <span class="font-medium">{{ $selectedOption }}</span></p>
                                <p>Correct Answer: <span class="font-medium">{{ $correctAnswerText }}</span></p>
                                <p>Result: <span class="font-bold {{ $answer && $answer->is_correct ? 'text-green-600' : 'text-red-600' }}">{{ $isCorrect }}</span></p>
                            </div>
                        @empty
                            <p>No questions were part of this test.</p>
                        @endforelse
                    </div>

                    <div class="mt-8 text-center">
                        <a href="{{ route('test.entry') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Take Another Test') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
