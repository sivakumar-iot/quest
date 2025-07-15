<!-- resources/views/test/entry.blade.php -->
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Enter your details and the test code to begin your exam.') }}
    </div>

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if (session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('test.start') }}">
        @csrf

        <!-- Test Code -->
        <div>
            <x-input-label for="test_code" :value="__('Test Code')" />
            <x-text-input id="test_code" class="block mt-1 w-full" type="text" name="test_code" :value="old('test_code')" required autofocus />
            <x-input-error :messages="$errors->get('test_code')" class="mt-2" />
        </div>

        <!-- Student Name -->
        <div class="mt-4">
            <x-input-label for="student_name" :value="__('Your Name')" />
            <x-text-input id="student_name" class="block mt-1 w-full" type="text" name="student_name" :value="old('student_name')" required />
            <x-input-error :messages="$errors->get('student_name')" class="mt-2" />
        </div>

        <!-- Father's Name -->
        <div class="mt-4">
            <x-input-label for="father_name" :value="__('Father\'s Name (Optional)')" />
            <x-text-input id="father_name" class="block mt-1 w-full" type="text" name="father_name" :value="old('father_name')" />
            <x-input-error :messages="$errors->get('father_name')" class="mt-2" />
        </div>

        <!-- Date of Birth -->
        <div class="mt-4">
            <x-input-label for="dob" :value="__('Date of Birth (Optional)')" />
            <x-text-input id="dob" class="block mt-1 w-full" type="date" name="dob" :value="old('dob')" />
            <x-input-error :messages="$errors->get('dob')" class="mt-2" />
        </div>

        <!-- Mobile -->
        <div class="mt-4">
            <x-input-label for="mobile" :value="__('Mobile Number (Optional)')" />
            <x-text-input id="mobile" class="block mt-1 w-full" type="text" name="mobile" :value="old('mobile')" />
            <x-input-error :messages="$errors->get('mobile')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Address (Optional)')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Start Test') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
