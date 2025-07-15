<!-- resources/views/admin/questions/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Bulk Questions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('questions.bulk') }}">
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

                        <!-- CSV Upload Section -->
                        <div class="mt-6">
                            <x-input-label for="csv_file" :value="__('Upload Questions CSV')" />
                            <input type="file" id="csv_file" name="csv_file" accept=".csv"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <x-input-error :messages="$errors->get('csv_file')" class="mt-2" />
                        </div>
                        
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
            const moduleIdSelect = document.getElementById('module_id');
            const topicIdSelect = document.getElementById('topic_id');

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
            moduleIdSelect.addEventListener('change', function() {
                loadTopics(this.value);
            });

            // Load topics if a module was already selected (e.g., on validation error)
            if (moduleIdSelect.value) {
                loadTopics(moduleIdSelect.value);
            }
        });
    </script>
</x-app-layout>