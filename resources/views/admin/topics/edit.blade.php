<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Topic') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('topics.update', $topic->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Module Selection (Moved to Top) -->
                        <div>
                            <x-input-label for="module_id" :value="__('Select Module')" />
                            <select id="module_id" name="module_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Select Module --</option>
                                @foreach ($modules as $module)
                                    <option value="{{ $module->id }}" {{ old('module_id', $topic->module_id) == $module->id ? 'selected' : '' }}>
                                        {{ $module->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('module_id')" class="mt-2" />
                        </div>

                        <!-- Topic Name -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Topic Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name', $topic->name) }}" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between mt-6">

                            <x-cancel-button  href="{{ route('topics.index') }}" >
                                {{ __('Cancel') }}
                            </x-cancel-button>


                            {{-- <a href="{{ route('topics.index') }}" class="btn-cancel">
                                {{ __('Cancel') }}
                            </a> --}}

                            <x-primary-button>
                                {{ __('Update Topic') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
