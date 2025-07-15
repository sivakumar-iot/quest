<button
  {{ $attributes->merge([
      'type' => 'submit',
      'class' =>
          'w-full py-3  inline-flex items-center px-4 py-2 text-xl text-center flex items-center justify-center bg-purple-500  text-white hover:bg-gray-200 hover:border-purple-500 hover:text-purple-500 active:bg-purple-700 focus:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest transition ease-in-out duration-150',
  ]) }}>
  {{ $slot }}
</button>
