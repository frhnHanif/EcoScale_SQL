{{-- resources/views/components/navbar.blade.php --}}

<div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex flex-wrap justify-center sm:justify-start space-x-4 sm:space-x-8" aria-label="Tabs">

        {{-- Overview Tab --}}
        <a href="/" {{-- The href is still '/', as this is the user-friendly entry point --}}
            class="group inline-flex items-center px-1 py-4 border-b-2 text-sm font-medium
                   {{ request()->is('dashboard') ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                   transition-colors duration-200 ease-in-out">
            {{-- Icon: Home/Overview --}}
            <svg xmlns="http://www.w3.org/2000/svg"
                class="mr-2 h-5 w-5 {{ request()->is('dashboard') ? 'text-teal-500 group-hover:text-teal-600' : 'text-gray-400 group-hover:text-gray-500' }} transition-colors duration-200 ease-in-out"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span>Overview</span>
        </a>

        {{-- Fakultas Tab --}}
        <a href="/fakultas"
            class="group inline-flex items-center px-1 py-4 border-b-2 text-sm font-medium
                   {{ request()->is('fakultas') ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                   transition-colors duration-200 ease-in-out">
            {{-- Icon: Academic Cap (for faculties) --}}
            <svg xmlns="http://www.w3.org/2000/svg"
                class="mr-2 h-5 w-5 {{ request()->is('fakultas') ? 'text-teal-500 group-hover:text-teal-600' : 'text-gray-400 group-hover:text-gray-500' }} transition-colors duration-200 ease-in-out"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                <path
                    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-5.998 12.078 12.078 0 01.665-6.479L12 14z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-5.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
            </svg>
            <span>Fakultas</span>
        </a>

        {{-- Analitik Tab --}}
        <a href="/analitik"
            class="group inline-flex items-center px-1 py-4 border-b-2 text-sm font-medium
                   {{ request()->is('analitik') ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                   transition-colors duration-200 ease-in-out">
            {{-- Icon: Chart Bar (for analytics) --}}
            <svg xmlns="http://www.w3.org/2000/svg"
                class="mr-2 h-5 w-5 {{ request()->is('analitik') ? 'text-teal-500 group-hover:text-teal-600' : 'text-gray-400 group-hover:text-gray-500' }}"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Analitik</span>
        </a>

        {{-- Laporan Tab --}}
        <a href="/laporan"
            class="group inline-flex items-center px-1 py-4 border-b-2 text-sm font-medium
                   {{ request()->is('laporan') ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}
                   transition-colors duration-200 ease-in-out">
            {{-- Icon: Document Report (for reports) --}}
            <svg xmlns="http://www.w3.org/2000/svg"
                class="mr-2 h-5 w-5 {{ request()->is('laporan') ? 'text-teal-500 group-hover:text-teal-600' : 'text-gray-400 group-hover:text-gray-500' }} transition-colors duration-200 ease-in-out"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Laporan</span>
        </a>

    </nav>
</div>
