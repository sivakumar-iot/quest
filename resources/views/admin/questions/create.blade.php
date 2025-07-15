<!-- resources/views/admin/questions/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Question') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('questions.store') }}">
                        @csrf

                        <!-- Module Selection -->
                        <div>
                            <x-input-label for="module_id" :value="__('Select Module')" />
                            <select id="module_id" name="module_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Select Module --</option>
                                {{-- Loop through modules passed from controller --}}
                                @foreach ($modules as $module)
                                    <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                                        {{ $module->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('module_id')" class="mt-2" />
                        </div>

                        <!-- Topic Selection (will be dynamically loaded via JS based on module_id) -->
                        <div class="mt-4">
                            <x-input-label for="topic_id" :value="__('Select Topic')" />
                            <select id="topic_id" name="topic_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Select Topic --</option>
                                {{-- Topics will be loaded here via AJAX/JavaScript --}}
                            </select>
                            <x-input-error :messages="$errors->get('topic_id')" class="mt-2" />
                        </div>

                        <!-- Question Text -->
                        <div class="mt-4">
                            <x-input-label for="question_text" :value="__('Question Text')" />
                            <textarea id="question_text" name="question_text" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('question_text') }}</textarea>
                            <x-input-error :messages="$errors->get('question_text')" class="mt-2" />
                        </div>

                        <!-- Question Type -->
                        <div class="mt-4">
                            <x-input-label for="question_type" :value="__('Question Type')" />
                            <select id="question_type" name="question_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Select Type --</option>
                                <option value="multiple_choice" {{ old('question_type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                <option value="yes_no" {{ old('question_type') == 'yes_no' ? 'selected' : '' }}>Yes/No</option>
                                {{-- Add more types as needed --}}
                            </select>
                            <x-input-error :messages="$errors->get('question_type')" class="mt-2" />
                        </div>

                        <!-- Conditional Fields for Multiple Choice -->
                        <div id="multiple_choice_options" class="mt-4 p-4 border rounded-md" style="display: {{ old('question_type') == 'multiple_choice' ? 'block' : 'none' }};">
                            <h3 class="font-semibold text-lg mb-2">Multiple Choice Options</h3>
                            <div id="options_container">
                                {{-- Options will be added here. For simplicity, showing 4 fixed options --}}
                                @for ($i = 1; $i <= 4; $i++)
                                    <div class="flex items-center mt-2">
                                        <x-text-input type="text" name="options[]" placeholder="Option {{ $i }}" class="block w-full" value="{{ old('options.' . ($i-1)) }}" />
                                        <input type="radio" name="correct_answer_mc" value="{{ $i-1 }}" class="ml-2 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('correct_answer_mc') == ($i-1) ? 'checked' : '' }}>
                                        <label class="ml-1 text-sm text-gray-600">Correct</label>
                                    </div>
                                @endfor
                                {{-- You might want a "Add Option" button with JavaScript --}}
                            </div>
                            <x-input-error :messages="$errors->get('options')" class="mt-2" />
                            <x-input-error :messages="$errors->get('correct_answer_mc')" class="mt-2" />
                        </div>

                        <!-- Conditional Fields for Yes/No -->
                        <div id="yes_no_options" class="mt-4 p-4 border rounded-md" style="display: {{ old('question_type') == 'yes_no' ? 'block' : 'none' }};">
                            <h3 class="font-semibold text-lg mb-2">Yes/No Answer</h3>
                            <div class="flex items-center space-x-4">
                                <input type="radio" name="correct_answer_yn" value="yes" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('correct_answer_yn') == 'yes' ? 'checked' : '' }}>
                                <label class="text-sm text-gray-600">Yes</label>

                                <input type="radio" name="correct_answer_yn" value="no" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('correct_answer_yn') == 'no' ? 'checked' : '' }}>
                                <label class="text-sm text-gray-600">No</label>
                            </div>
                            <x-input-error :messages="$errors->get('correct_answer_yn')" class="mt-2" />
                        </div>

                        <!-- Timer Settings -->
                        <div class="mt-4 flex items-center">
                            <input type="checkbox" id="timer_enabled" name="timer_enabled" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" value="1" {{ old('timer_enabled') ? 'checked' : '' }}>
                            <x-input-label for="timer_enabled" class="ml-2" :value="__('Enable Timer for this Question')" />
                        </div>
                        <div id="timer_value_field" class="mt-2" style="display: {{ old('timer_enabled') ? 'block' : 'none' }};">
                            <x-input-label for="timer_value" :value="__('Timer Value (seconds)')" />
                            <x-text-input id="timer_value" class="block mt-1 w-full" type="number" name="timer_value" :value="old('timer_value', 0)"  />
                            <x-input-error :messages="$errors->get('timer_value')" class="mt-2" />
                        </div>

                        <!-- Question Status & Random Options -->
                        <div class="mt-4 flex items-center space-x-6">
                            <div>
                                <input type="checkbox" id="is_enabled" name="is_enabled" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" value="1" {{ old('is_enabled', true) ? 'checked' : '' }}>
                                <x-input-label for="is_enabled" class="ml-2 inline-block" :value="__('Question Enabled')" />
                            </div>
                            <div>
                                <input type="checkbox" id="is_random_options" name="is_random_options" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" value="1" {{ old('is_random_options') ? 'checked' : '' }}>
                                <x-input-label for="is_random_options" class="ml-2 inline-block" :value="__('Randomize Options Order')" />
                            </div>
                        </div>



                        {{-- value="1"
  {{ old('is_featured') ? 'checked="checked"' : '' }} --}}

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Save Question') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script>
        console.log('Script is running!');

        document.addEventListener('DOMContentLoaded', function () {
            const questionTypeSelect = document.getElementById('question_type');
            const multipleChoiceOptions = document.getElementById('multiple_choice_options');
            const yesNoOptions = document.getElementById('yes_no_options');
            const timerEnabledCheckbox = document.getElementById('timer_enabled');
            const timerValueField = document.getElementById('timer_value_field');
            const moduleIdSelect = document.getElementById('module_id');
            const topicIdSelect = document.getElementById('topic_id');

            function toggleQuestionTypeFields() {
                multipleChoiceOptions.style.display = 'none';
                yesNoOptions.style.display = 'none';

                if (questionTypeSelect.value === 'multiple_choice') {
                    multipleChoiceOptions.style.display = 'block';
                } else if (questionTypeSelect.value === 'yes_no') {
                    yesNoOptions.style.display = 'block';
                }
            }

            function toggleTimerValueField() {
                timerValueField.style.display = timerEnabledCheckbox.checked ? 'block' : 'none';
            }

            async function loadTopics(moduleId) {
                topicIdSelect.innerHTML = '<option value="">-- Loading Topics --</option>';
                if (!moduleId) {
                    topicIdSelect.innerHTML = '<option value="">-- Select Topic --</option>';
                    return;
                }

                try {
                    // const response = await fetch(`/api/modules/${moduleId}/topics`);
                    const response = await fetch(`/api/modules/${moduleId}/topics`);

                    console.log(response);

                    const topics = await response.json();

                    topicIdSelect.innerHTML = '<option value="">-- Select Topic --</option>';
                    topics.forEach(topic => {
                        const option = document.createElement('option');
                        option.value = topic.id;
                        option.textContent = topic.name;
                        topicIdSelect.appendChild(option);
                    });
                    // Pre-select old topic_id if available
                    const oldTopicId = "{{ old('topic_id') }}";
                    if (oldTopicId) {
                        topicIdSelect.value = oldTopicId;
                    }
                } catch (error) {
                    console.error('Error loading topics:', error);
                    topicIdSelect.innerHTML = '<option value="">-- Error Loading Topics --</option>';
                }
            }

            // Event Listeners
            questionTypeSelect.addEventListener('change', toggleQuestionTypeFields);
            timerEnabledCheckbox.addEventListener('change', toggleTimerValueField);
            moduleIdSelect.addEventListener('change', function() {
                loadTopics(this.value);
            });

            // Initial calls on page load
            toggleQuestionTypeFields();
            toggleTimerValueField();
            // Load topics if a module was already selected (e.g., on validation error)
            if (moduleIdSelect.value) {
                loadTopics(moduleIdSelect.value);
            }
        });
    </script>
    @endstack
</x-app-layout>