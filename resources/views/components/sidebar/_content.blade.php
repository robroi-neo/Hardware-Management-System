{{--
    Shared sidebar markup used by the main container component.
--}}
<aside
    :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed left-0 top-2 bottom-2 z-50 flex w-64 flex-col overflow-y-auto bg-white rounded-xl px-4 py-6 text-slate-100 transition-transform duration-200 ease-out lg:sticky lg:top-2 lg:h-[calc(100vh-1rem)] lg:translate-x-0"
>
    <div class="mb-8 flex items-center gap-3 px-2">
        <x-application-logo />
        <div>
            <p class="text-sm font-semibold leading-4 text-slate-900">RNM Hardware</p>
            <p class="text-xs text-slate-500">Management System</p>
        </div>

        <button
            type="button"
            @click="mobileOpen = false"
            class="ms-auto rounded-md p-2 text-slate-900 hover:bg-black/10 lg:hidden"
            aria-label="Close sidebar"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="border-white/10">
        <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            General
        </p>
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
        <x-sidebar.dropdown
            label="POS"
            :open="request()->routeIs('pos*')"
        >
            <x-slot:icon>
                {{-- POS: card/terminal icon --}}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4.5" y="5.25" width="15" height="13.5" rx="2.25" ry="2.25" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9h9m-9 4.5h3.75" />
                </svg>
            </x-slot:icon>

            <x-sidebar.item
                href="{{ route('pos') }}"
                :active="request()->routeIs('pos')"
            >
                New Sale
            </x-sidebar.item>
            <x-sidebar.item
                href="{{ route('pos.transactions') }}"
                :active="request()->routeIs('pos.transactions')"
            >
                Transactions
            </x-sidebar.item>
        </x-sidebar.dropdown>
        @endcan

        @canany(['purchases.create', 'purchases.view-history'])
        <x-sidebar.dropdown
            label="Purchasing"
            :open="request()->routeIs('purchasing.*')"
        >
            <x-slot:icon>
                {{-- POS: card/terminal icon --}}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4.5" y="5.25" width="15" height="13.5" rx="2.25" ry="2.25" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9h9m-9 4.5h3.75" />
                </svg>
            </x-slot:icon>

            <x-sidebar.item
                href="{{ route('purchasing.new-invoice') }}"
                :active="request()->routeIs('purchasing.new-invoice')"
            >
                New Invoice
            </x-sidebar.item>
            <x-sidebar.item
                href="{{ route('purchasing.invoice-history') }}"
                :active="request()->routeIs('purchasing.invoice-history')"
            >
                Invoice History
            </x-sidebar.item>
        </x-sidebar.dropdown>
        @endcanany

        @canany(['inventory.view-overview', 'inventory.update', 'inventory.view-movements', 'inventory.archive'])
        <x-sidebar.dropdown
            label="Inventory"
            :open="request()->routeIs('inventory.*')"
        >
            <x-slot:icon>
                {{-- Inventory: box icon --}}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-4.5-9 4.5 9 4.5 9-4.5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5v9l9 4.5 9-4.5v-9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v9" />
                </svg>
            </x-slot:icon>

            <x-sidebar.item
                href="{{ route('inventory.overview') }}"
                :active="request()->routeIs('inventory.overview')"
            >
                Stock Overview
            </x-sidebar.item>
            <x-sidebar.item
                href="{{ route('inventory.manual-stock-in') }}"
                :active="request()->routeIs('inventory.manual-stock-in')"
            >
                Manual Stock In
            </x-sidebar.item>
            <x-sidebar.item
                href="{{ route('inventory.stock-out') }}"
                :active="request()->routeIs('inventory.stock-out')"
            >
                Stock Out
            </x-sidebar.item>
            <x-sidebar.item
                href="{{ route('inventory.stock-movements') }}"
                :active="request()->routeIs('inventory.stock-movements')"
            >
                Stock Movements
            </x-sidebar.item>
            <x-sidebar.item
                href="{{ route('inventory.archives') }}"
                :active="request()->routeIs('inventory.archives')"
            >
                Archives
            </x-sidebar.item>
        </x-sidebar.dropdown>
        @endcanany
        <!-- Admin Section Divider -->
        <div class="mt-6 border-white/10 pt-4">
            <p class="px-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                Admin
            </p>
        </div>

        @canany(['audit.user-activity.view', 'audit.system-logs.view'])
        <x-sidebar.dropdown
            label="Audit Logs"
            :open="request()->routeIs('audit-logs.*')"
        >
            <x-slot:icon>
                {{-- Audit Logs: document with check icon --}}
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75h6.75L21 9v9.75A2.25 2.25 0 0118.75 21H9A2.25 2.25 0 016.75 18.75V6A2.25 2.25 0 019 3.75z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 12.75l2.25 2.25 3.75-3.75" />
                </svg>
            </x-slot:icon>

            <x-sidebar.item
                href="{{ route('audit-logs.user-activity') }}"
                :active="request()->routeIs('audit-logs.user-activity')"
            >
                User Activity
            </x-sidebar.item>
            <x-sidebar.item
                href="{{ route('audit-logs.system-logs') }}"
                :active="request()->routeIs('audit-logs.system-logs')"
            >
                System Logs
            </x-sidebar.item>
            <x-sidebar.item
                href="{{ route('audit-logs.archives') }}"
                :active="request()->routeIs('audit-logs.archives')"
            >
                Archives
            </x-sidebar.item>
        </x-sidebar.dropdown>
        @endcanany

        @can('suppliers.view')
        <x-sidebar.item
            href="{{ route('suppliers.index') }}"
            :active="request()->routeIs('suppliers.*')"
            class="flex items-center gap-3"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12.75h16.5M7.5 8.25h9m-9 9h9M4.5 5.25h15A1.5 1.5 0 0121 6.75v10.5a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 17.25V6.75a1.5 1.5 0 011.5-1.5z" />
            </svg>
            <span>Supplier Records</span>
        </x-sidebar.item>
        @endcan

        @canany(['users.create', 'users.view-list', 'users.edit'])
        <x-sidebar.dropdown
            label="Users"
            :open="request()->routeIs('users.*')"
        >
            <x-slot:icon>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.5a7.5 7.5 0 0115 0" />
                </svg>
            </x-slot:icon>

            <x-sidebar.item
                href="{{ route('users.create') }}"
                :active="request()->routeIs('users.create')"
            >
                Add New User
            </x-sidebar.item>
            <x-sidebar.item
                href="{{ route('users.index') }}"
                :active="request()->routeIs('users.index')"
            >
                Manage Users
            </x-sidebar.item>
        </x-sidebar.dropdown>
        @endcanany
    </nav>

    <div class="mt-auto">
        <form method="POST" action="{{ route('logout') }}" onsubmit="sessionStorage.removeItem('pos_terminal_id')">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-black/10"
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
