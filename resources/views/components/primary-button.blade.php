<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center bg-indigo-600 border rounded font-medium text-sm text-white tracking-normal hover:bg-indigo-700 focus:bg-indigo-700 active:ring-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
