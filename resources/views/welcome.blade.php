<!-- resources/views/welcome.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <title>{{ config('app.name', 'Kevell Corp') }}</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net" />
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

  <!-- Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    body {
      background-image: url("/build/assets/images/benjamin-voros-phIFdC6lA4E-unsplash.jpg");
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }

    .left-section-overlay {
      background-color: rgba(0, 0, 0, 0.7);
    }

    .right-section-bg {
      background-color: rgba(255, 255, 255, 0.9);
    }
  </style>
</head>

<body class="font-sans antialiased">
  <div class="h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-7xl flex flex-col md:flex-row rounded-lg overflow-hidden h-3/4 ">
      <!-- Left Section: Welcome and Take Test -->
      <div class="md:w-2/3 p-10 flex flex-col justify-center items-center text-center text-white left-section-overlay">
        <h1 class="text-5xl font-bold mb-4">Welcome to Kevell Corp</h1>
        <h2 class="text-3xl font-bold mb-4 text-gray-300">Online Examination Platform</h2>

        <p class="text-lg mb-6 max-w-md my-4">
          Kevell Corp is dedicated to providing cutting-edge solutions and services. Our online examination platform
          is designed to offer a seamless and secure testing experience for students and efficient management for
          administrators. Prepare to excel with our comprehensive and user-friendly system.
        </p>

        <div class="flex space-x-4 mb-8">
          {{-- Placeholder for social icons --}}
          <a href="https://www.linkedin.com/company/kevellcorp/posts/?feedView=all" class="social-icon">
            <i class="fab fa-linkedin"></i></a>
          <a href="https://www.facebook.com/kevellcorp/" class="social-icon">
            <i class="fab fa-facebook"></i></a>
          <a href="https://www.instagram.com/kevell_corp/?hl=en" class="social-icon">
            <i class="fab fa-instagram"></i></a>
          <a href="https://www.youtube.com/channel/UCKx_sy3b1ocPPA0ueDv6VqA/videos" class="social-icon">
            <i class="fab fa-youtube"></i></a>
        </div>

        <a href="{{ route('test.entry') }}"
          class="inline-flex items-center px-8 py-4 bg-indigo-600 border border-transparent rounded-md text-xl font-semibold text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
          {{ __('Take a Test') }}
        </a>
      </div>

      <!-- Right Section: Admin Login -->
      <div class="md:w-1/3 p-10 right-section-bg flex flex-col justify-center">
        <h2 class="text-4xl font-bold text-gray-800 text-center mb-8">
          Admin
        </h2>

        @if (session('status'))
          <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
          </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
          @csrf
          <!-- Email Address -->
          <div class="mb-6">
            <x-input-label for="email" :value="__('Email Address')" class="text-lg text-gray-700" />
            <x-text-input id="email"
              class="block mt-2 w-full p-3 border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
              type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
          </div>

          <!-- Password -->
          <div class="mt-4 mb-6">
            <x-input-label for="password" :value="__('Password')" class="text-lg text-gray-700" />
            <x-text-input id="password"
              class="block mt-2 w-full p-3 border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
              type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
          </div>

          <!-- Remember Me -->
          <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
              <input id="remember_me" type="checkbox"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember" />
              <span class="ms-2 text-sm text-gray-600">{{ __('Remember Me') }}</span>
            </label>

            @if (Route::has('password.request'))
              <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('password.request') }}">
                {{ __('Lost your password?') }}
              </a>
            @endif
          </div>

          <div class="flex items-center justify-center mt-8">
            <x-purple-button>{{ __('Sign in') }} </x-purple-button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>
