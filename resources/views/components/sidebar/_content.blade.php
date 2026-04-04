{{--
    Shared sidebar markup used by the main container component.
--}}
<aside
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-[#050938] px-4 py-6 text-slate-100 transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
>
    <div class="mb-8 flex items-center gap-3 px-2">
        <x-application-logo />
        <div>
            <p class="text-sm font-semibold leading-4">RNM Hardware</p>
            <p class="text-xs text-indigo-200">Management System</p>
        </div>

        <button
            type="button"
            @click="mobileOpen = false"
            class="ms-auto rounded-md p-2 text-indigo-900 hover:bg-white/10 lg:hidden"
            aria-label="Close sidebar"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="space-y-1">
        <x-sidebar.link
            href="{{ route('dashboard') }}"
            :active="request()->routeIs('dashboard')"
            @click="mobileOpen = false"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h7.5v4.5h-7.5v-4.5zm9 0h7.5v10.5h-7.5V6.75zm-9 6h7.5v4.5h-7.5v-4.5z" />
            </svg>
            <span>Dashboard</span>
        </x-sidebar.link>

        @can('pos.access')
            <x-sidebar.link
                href="{{ route('pos') }}"
                :active="request()->routeIs('pos')"
                @click="mobileOpen = false"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25h16.5M3.75 12h16.5M8.25 15.75h7.5" />
                </svg>
                <span>POS</span>
            </x-sidebar.link>
        @endcan

        <x-sidebar.link href="#">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25h16.5M3.75 12h16.5M8.25 15.75h7.5" />
            </svg>
            <span>Sales</span>
        </x-sidebar.link>

        <x-sidebar.dropdown label="Inventory">
            <x-slot:icon>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h10.5A2.25 2.25 0 0119.5 6v12a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 18V6a2.25 2.25 0 012.25-2.25z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25h7.5m-7.5 3h7.5m-7.5 3h4.5" />
                </svg>
            </x-slot:icon>

            <a
                href="#"
                class="block rounded-lg bg-indigo-500 px-3 py-2 text-sm text-white hover:bg-indigo-500/90"
            >
                Stock Overview
            </a>
            <a
                href="#"
                class="block rounded-lg bg-indigo-500 px-3 py-2 text-sm text-white hover:bg-indigo-500/90"
            >
                Stock In
            </a>
            <a
                href="#"
                class="block rounded-lg bg-indigo-500 px-3 py-2 text-sm text-white hover:bg-indigo-500/90"
            >
                Stock Out
            </a>
            <a
                href="#"
                class="block rounded-lg px-3 py-2 text-sm text-indigo-100 hover:bg-white/10"
            >
                Stock Movements
            </a>
            <a
                href="#"
                class="block rounded-lg px-3 py-2 text-sm text-indigo-100 hover:bg-white/10"
            >
                Archives
            </a>
        </x-sidebar.dropdown>

        <x-sidebar.dropdown label="Audit Logs">
            <x-slot:icon>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3.75h10.5A2.25 2.25 0 0119.5 6v12a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 18V6a2.25 2.25 0 012.25-2.25z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25h7.5m-7.5 3h7.5m-7.5 3h4.5" />
                </svg>
            </x-slot:icon>

            <a
                href="#"
                class="block rounded-lg bg-indigo-500 px-3 py-2 text-sm text-white hover:bg-indigo-500/90"
            >
                User Activity
            </a>
            <a
                href="#"
                class="block rounded-lg px-3 py-2 text-sm text-indigo-100 hover:bg-white/10"
            >
                System Logs
            </a>
            <a
                href="#"
                class="block rounded-lg px-3 py-2 text-sm text-indigo-100 hover:bg-white/10"
            >
                Archives
            </a>
        </x-sidebar.dropdown>

        <a href="#" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-indigo-100 transition hover:bg-white/10">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12.75h16.5M7.5 8.25h9m-9 9h9M4.5 5.25h15A1.5 1.5 0 0121 6.75v10.5a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 17.25V6.75a1.5 1.5 0 011.5-1.5z" />
            </svg>
            Supplier Records
        </a>

        <x-sidebar.link
            href="{{ route('profile.edit') }}"
            :active="request()->routeIs('profile.*')"
            @click="mobileOpen = false"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.5a7.5 7.5 0 0115 0" />
            </svg>
            <span>Users</span>
        </x-sidebar.link>
    </nav>

    <div class="mt-auto">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-indigo-100 transition hover:bg-white/10"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 15l3-3m0 0l-3-3m3 3H9" />
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>
