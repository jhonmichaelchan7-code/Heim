<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-[#155d49] hover:bg-[#114a3b] active:bg-[#0c352a] text-white font-bold text-sm rounded-xl shadow-sm hover:shadow transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#155d49] focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
