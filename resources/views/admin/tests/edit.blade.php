<!-- resources/views/admin/tests/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Test') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('tests.update', $test) }}">
                        @csrf
                        @method('PUT') {{-- Use PUT method for updates --}}

                        <!-- Test Name -->
                        <div>
                            <x-input-label for="name" :value="__('Test Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $test->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Test Code/Password -->
                        <div class="mt-4">
                            <x-input-label for="test_code" :value="__('Test Access Code')" />
                            <x-text-input id="test_code" class="block mt-1 w-full" type="text" name="test_code" :value="old('test_code', $test->test_code)" required />
                            <x-input-error :messages="$errors->get('test_code')" class="mt-2" />
                        </div>

                        <!-- Duration (Minutes) -->
                        <div class="mt-4">
                            <x-input-label for="duration_minutes" :value="__('Duration (Minutes)')" />
                            <x-text-input id="duration_minutes" class="block mt-1 w-full" type="number" name="duration_minutes" :value="old('duration_minutes', $test->duration_minutes)" required min="1" />
                            <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $test->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Instructions -->
                        <div class="mt-4">
                            <x-input-label for="instructions" :value="__('Instructions')" />
                            <textarea id="instructions" name="instructions" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('instructions', $test->instructions) }}</textarea>
                            <x-input-error :messages="$errors->get('instructions')" class="mt-2" />
                        </div>

                        <!-- Pass Percentage -->
                        <div class="mt-4">
                            <x-input-label for="pass_percentage" :value="__('Pass Percentage (optional)')" />
                            <x-text-input id="pass_percentage" class="block mt-1 w-full" type="number" name="pass_percentage" :value="old('pass_percentage', $test->pass_percentage)" min="0" max="100" />
                            <x-input-error :messages="$errors->get('pass_percentage')" class="mt-2" />
                        </div>

                        <!-- Test Enabled -->
                        <div class="mt-4 flex items-center">
                            <input type="checkbox" id="is_enabled" name="is_enabled" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" value="1" {{ old('is_enabled', $test->is_enabled) ? 'checked' : '' }}>
                            <x-input-label for="is_enabled" class="ml-2" :value="__('Test Enabled')" />
                        </div>

                        <h3 class="font-semibold text-xl text-gray-800 leading-tight mt-6 mb-4">
                            {{ __('Test Question Rules') }}
                        </h3>

                        <div id="test_rules_container">
                            {{-- This section is dynamically managed by JavaScript --}}
                        </div>

                        <button type="button" id="add_rule_button" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Add Another Question Rule') }}
                        </button>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Test') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addRuleButton = document.getElementById('add_rule_button');
            const rulesContainer = document.getElementById('test_rules_container');
            let ruleIndex = 0; // Start index for new blocks

            function setupDynamicTopicLoading(moduleSelect, topicSelect, initialTopicId = null) {
                moduleSelect.addEventListener('change', async function() {
                    const moduleId = this.value;
                    topicSelect.innerHTML = '<option value="">-- Loading Topics --</option>';
                    if (!moduleId) {
                        topicSelect.innerHTML = '<option value="">-- Any Topic --</option>';
                        return;
                    }
                    try {
                        const response = await fetch(`/api/modules/${moduleId}/topics`);
                        const topics = await response.json();
                        topicSelect.innerHTML = '<option value="">-- Any Topic --</option>';
                        topics.forEach(topic => {
                            const option = document.createElement('option');
                            option.value = topic.id;
                            option.textContent = topic.name;
                            topicSelect.appendChild(option);
                        });
                        if (initialTopicId) {
                            topicSelect.value = initialTopicId;
                            initialTopicId = null; // Only apply once
                        }
                    } catch (error) {
                        console.error('Error loading topics:', error);
                        topicSelect.innerHTML = '<option value="">-- Error Loading Topics --</option>';
                    }
                });
                // Trigger change event to load topics for pre-selected module on page load
                if (moduleSelect.value) {
                    moduleSelect.dispatchEvent(new Event('change'));
                }
            }

            function addRuleBlock(initialData = {}) {
                const newBlock = document.createElement('div');
                newBlock.classList.add('test-rule-block', 'p-4', 'border', 'rounded-md', 'mb-4', 'bg-gray-50');

                const currentRuleIndex = ruleIndex; // Use a local variable for this block's index
                const moduleOptionsHtml = `
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}">{{ $module->name }}</option>
                    @endforeach
                `;

                newBlock.innerHTML = `
                    <h4 class="font-medium text-md mb-2">Rule #${currentRuleIndex + 1}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="rule_module_id_${currentRuleIndex}" :value="__('Module (optional)')" />
                            <select id="rule_module_id_${currentRuleIndex}" name="rules[${currentRuleIndex}][module_id]" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Any Module --</option>
                                ${moduleOptionsHtml}
                            </select>
                        </div>
                        <div>
                            <x-input-label for="rule_topic_id_${currentRuleIndex}" :value="__('Topic (optional)')" />
                            <select id="rule_topic_id_${currentRuleIndex}" name="rules[${currentRuleIndex}][topic_id]" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Any Topic --</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="rule_question_type_${currentRuleIndex}" :value="__('Question Type (optional)')" />
                            <select id="rule_question_type_${currentRuleIndex}" name="rules[${currentRuleIndex}][question_type]" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Any Type --</option>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="yes_no">Yes/No</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="rule_num_questions_${currentRuleIndex}" :value="__('Number of Questions')" />
                            <x-text-input id="rule_num_questions_${currentRuleIndex}" class="block mt-1 w-full" type="number" name="rules[${currentRuleIndex}][number_of_questions]" value="${initialData.number_of_questions || 1}" required min="1" />
                        </div>
                    </div>
                    <div class="flex justify-end mt-3">
                        <button type="button" class="remove-rule-block text-red-600 hover:text-red-900 text-sm">Remove Rule</button>
                    </div>
                `;
                rulesContainer.appendChild(newBlock);

                const newModuleSelect = newBlock.querySelector(`#rule_module_id_${currentRuleIndex}`);
                const newTopicSelect = newBlock.querySelector(`#rule_topic_id_${currentRuleIndex}`);
                const newQuestionTypeSelect = newBlock.querySelector(`#rule_question_type_${currentRuleIndex}`);

                // Set initial values if provided
                if (initialData.module_id) newModuleSelect.value = initialData.module_id;
                if (initialData.question_type) newQuestionTypeSelect.value = initialData.question_type;

                setupDynamicTopicLoading(newModuleSelect, newTopicSelect, initialData.topic_id);

                newBlock.querySelector('.remove-rule-block').addEventListener('click', function() {
                    newBlock.remove();
                    updateRuleNumbers();
                });

                ruleIndex++; // Increment global index after adding a block
            }

            function updateRuleNumbers() {
                const ruleBlocks = rulesContainer.querySelectorAll('.test-rule-block');
                ruleBlocks.forEach((block, index) => {
                    block.querySelector('h4').textContent = `Rule #${index + 1}`;
                });
            }

            addRuleButton.addEventListener('click', () => addRuleBlock());

            // Load existing configurations on page load for edit form
            @if (isset($test) && $test->rules->isNotEmpty())
                ruleIndex = 0; // Reset index for existing blocks
                @foreach ($test->rules as $rule)
                    addRuleBlock({
                        module_id: "{{ $rule->module_id }}",
                        topic_id: "{{ $rule->topic_id }}",
                        question_type: "{{ $rule->question_type }}",
                        number_of_questions: "{{ $rule->number_of_questions }}"
                    });
                @endforeach
            @else
                // If no existing rules (create form or empty edit), add one empty block
                if (rulesContainer.children.length === 0) {
                    addRuleBlock();
                }
            @endif
        });
    </script>
</x-app-layout>
