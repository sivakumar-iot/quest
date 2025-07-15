<!-- resources/views/test/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Test: ') . $test->name }}
        </h2>
        <div id="timer" class="text-lg font-bold text-red-600 mt-2">Time Remaining: --:--</div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div id="test-content">
                        {{-- Questions will be loaded here dynamically --}}
                        <div id="question-display">
                            {{-- Question and options will be rendered by JavaScript --}}
                            <h3 class="text-xl font-semibold mb-4">Loading Question...</h3>
                            <div class="options-container space-y-2">
                                <!-- Options go here -->
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <button id="prev-button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 disabled:opacity-50" disabled>
                                {{ __('Previous') }}
                            </button>
                            <button id="next-button" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                {{ __('Next') }}
                            </button>
                            <button id="submit-button" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 hidden">
                                {{ __('Submit Test') }}
                            </button>
                        </div>
                    </div>

                    {{-- Custom Modal for Warnings (e.g., cheating attempts) --}}
                    <div id="custom-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                            <div class="mt-3 text-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Warning!</h3>
                                <div class="mt-2 px-7 py-3">
                                    <p class="text-sm text-gray-500" id="modal-message"></p>
                                </div>
                                <div class="items-center px-4 py-3">
                                    <button id="modal-close-button" class="px-4 py-2 bg-indigo-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        OK
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script>
        // Disable right-click
        document.addEventListener('contextmenu', e => e.preventDefault());

        // Tab/Window Visibility Detection
        let tabSwitchCount = 0;
        const maxTabSwitches = 3; // Max allowed tab switches before warning/submission

        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                tabSwitchCount++;
                console.warn('Tab switched or window minimized! Count:', tabSwitchCount);
                logCheatingAttempt('tab_switch');
                if (tabSwitchCount >= maxTabSwitches) {
                    showCustomModal('Warning!', `You have switched tabs or minimized the window ${tabSwitchCount} times. Further attempts may result in automatic test submission.`);
                    // Optionally, auto-submit here if critical
                    // submitTestForm();
                } else {
                    showCustomModal('Warning!', `You have switched tabs or minimized the window. Please remain on the test page. You have ${maxTabSwitches - tabSwitchCount} warnings left.`);
                }
            }
        });

        // Custom Modal Logic (instead of alert/confirm)
        const customModal = document.getElementById('custom-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalMessage = document.getElementById('modal-message');
        const modalCloseButton = document.getElementById('modal-close-button');

        function showCustomModal(title, message) {
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            customModal.classList.remove('hidden');
        }

        modalCloseButton.addEventListener('click', function() {
            customModal.classList.add('hidden');
        });

        // Prevent copy/paste (basic measure, can be bypassed)
        document.addEventListener('copy', e => e.preventDefault());
        document.addEventListener('cut', e => e.preventDefault());
        document.addEventListener('paste', e => e.preventDefault());

        // Prevent F12 (Developer Tools) - not foolproof
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I') || (e.ctrlKey && e.shiftKey && e.key === 'J')) {
                e.preventDefault();
                showCustomModal('Security Alert', 'Developer tools are disabled during the test.');
            }
        });

        const testAttemptId = {{ $testAttempt->id }};
        // CORRECTED LINE: Use $questionsForJs instead of $testQuestions
        const questions = @json($questionsForJs);
        const totalQuestions = questions.length;
        let currentQuestionIndex = 0;

        const questionDisplay = document.getElementById('question-display');
        const prevButton = document.getElementById('prev-button');
        const nextButton = document.getElementById('next-button');
        const submitButton = document.getElementById('submit-button');
        const timerDisplay = document.getElementById('timer');

        let intervalId;
        let remainingSeconds = {{ $remainingSeconds }}; // Initial remaining time from controller

        function startTimer() {
            intervalId = setInterval(() => {
                remainingSeconds--;
                const minutes = Math.floor(remainingSeconds / 60);
                const seconds = remainingSeconds % 60;
                timerDisplay.textContent = `Time Remaining: ${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

                if (remainingSeconds <= 0) {
                    clearInterval(intervalId);
                    timerDisplay.textContent = 'Time Remaining: 00:00 - Time Expired!';
                    showCustomModal('Time Expired!', 'Your test will be automatically submitted.');
                    setTimeout(submitTestForm, 2000); // Auto-submit after a brief delay
                }
            }, 1000);
        }

        async function saveAnswer(questionId, selectedOptions) {
            try {
                const response = await fetch(`{{ url('/test/' . $testAttempt->id . '/save-answer') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        selected_options: selectedOptions
                    })
                });
                const data = await response.json();
                if (!response.ok) {
                    console.error('Failed to save answer:', data.message);
                }
            } catch (error) {
                console.error('Error saving answer:', error);
            }
        }

        function renderQuestion() {
            if (totalQuestions === 0) {
                questionDisplay.innerHTML = '<h3 class="text-xl font-semibold mb-4">No questions available for this test.</h3>';
                prevButton.disabled = true;
                nextButton.disabled = true;
                submitButton.classList.remove('hidden'); // Show submit even if no questions
                return;
            }

            const question = questions[currentQuestionIndex];
            let optionsHtml = '';

            if (question.question_type === 'multiple_choice') {
                if (question.options && question.options.length > 0) {
                    question.options.forEach((option, index) => {
                        const isChecked = question.answered_options.includes(option);
                        optionsHtml += `
                            <label class="flex items-center space-x-2 p-3 border rounded-md cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="question_${question.id}_options" value="${option}" class="form-radio text-indigo-600" ${isChecked ? 'checked' : ''}>
                                <span>${option}</span>
                            </label>
                        `;
                    });
                } else {
                    optionsHtml = '<p class="text-red-500">No options defined for this multiple choice question.</p>';
                }
            } else if (question.question_type === 'yes_no') {
                const isYesChecked = question.answered_options.includes('yes');
                const isNoChecked = question.answered_options.includes('no');
                optionsHtml = `
                    <label class="flex items-center space-x-2 p-3 border rounded-md cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="question_${question.id}_options" value="yes" class="form-radio text-indigo-600" ${isYesChecked ? 'checked' : ''}>
                        <span>Yes</span>
                    </label>
                    <label class="flex items-center space-x-2 p-3 border rounded-md cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="question_${question.id}_options" value="no" class="form-radio text-indigo-600" ${isNoChecked ? 'checked' : ''}>
                        <span>No</span>
                    </label>
                `;
            } else {
                optionsHtml = '<p class="text-red-500">Unsupported question type.</p>';
            }

            questionDisplay.innerHTML = `
                <p class="text-sm text-gray-500 mb-2">Question ${currentQuestionIndex + 1} of ${totalQuestions}</p>
                <h3 class="text-xl font-semibold mb-4">${question.question_text}</h3>
                <div class="options-container space-y-2">
                    ${optionsHtml}
                </div>
            `;

            // Add event listeners to save answer on option change
            const optionInputs = questionDisplay.querySelectorAll(`input[name="question_${question.id}_options"]`);
            optionInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const selectedOptions = [this.value]; // For radio buttons, only one value
                    questions[currentQuestionIndex].answered_options = selectedOptions; // Update local state
                    saveAnswer(question.id, selectedOptions);
                });
            });

            updateNavigationButtons();
        }

        function updateNavigationButtons() {
            prevButton.disabled = currentQuestionIndex === 0;
            nextButton.disabled = currentQuestionIndex === totalQuestions - 1;

            if (currentQuestionIndex === totalQuestions - 1) {
                nextButton.classList.add('hidden');
                submitButton.classList.remove('hidden');
            } else {
                nextButton.classList.remove('hidden');
                submitButton.classList.add('hidden');
            }
        }

        prevButton.addEventListener('click', () => {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                renderQuestion();
            }
        });

        nextButton.addEventListener('click', () => {
            if (currentQuestionIndex < totalQuestions - 1) {
                currentQuestionIndex++;
                renderQuestion();
            }
        });

        submitButton.addEventListener('click', () => {
            // Confirm submission with a custom modal
            showCustomModal('Confirm Submission', 'Are you sure you want to submit your test? You cannot make changes after submission.');
            // Add a specific button to the modal for submission, or make the "OK" button trigger it
            // For simplicity, let's assume the "OK" button (modalCloseButton) will be replaced with a confirm/cancel
            // For now, we'll make a direct call after a small delay for the user to read the modal.
            // A better UX would be to have two buttons in the modal: "Confirm" and "Cancel".
            modalCloseButton.textContent = 'Confirm Submission';
            modalCloseButton.onclick = function() {
                customModal.classList.add('hidden');
                submitTestForm();
            };
        });

        function submitTestForm() {
            clearInterval(intervalId); // Stop the timer
            showCustomModal('Submitting Test...', 'Please wait while your test is being submitted. Do not close this window.');
            customModal.querySelector('#modal-close-button').disabled = true; // Disable close button during submission

            fetch(`{{ url('/test/' . $testAttempt->id . '/submit') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url; // Redirect to results page
                } else {
                    // Handle non-redirect response if any (e.g., error)
                    return response.json().then(data => {
                        showCustomModal('Submission Error', data.message || 'An error occurred during submission.');
                        customModal.querySelector('#modal-close-button').disabled = false;
                        customModal.querySelector('#modal-close-button').textContent = 'OK';
                        customModal.querySelector('#modal-close-button').onclick = function() { customModal.classList.add('hidden'); };
                    });
                }
            })
            .catch(error => {
                console.error('Error submitting test:', error);
                showCustomModal('Network Error', 'Could not submit test. Please check your internet connection.');
                customModal.querySelector('#modal-close-button').disabled = false;
                customModal.querySelector('#modal-close-button').textContent = 'OK';
                customModal.querySelector('#modal-close-button').onclick = function() { customModal.classList.add('hidden'); };
            });
        }

        // Function to log cheating attempts via AJAX
        async function logCheatingAttempt(type) {
            try {
                await fetch(`{{ url('/test/' . $testAttempt->id . '/log-cheating') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ type: type })
                });
            } catch (error) {
                console.error('Failed to log cheating attempt:', error);
            }
        }

        // Initial render and timer start
        renderQuestion();
        startTimer();

    </script>
</x-app-layout>
