{{--
    Shared sidebar markup used by the main container component.
--}}
<aside
    :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed left-0 top-0 bottom-0 z-50 flex w-64 flex-col overflow-y-auto bg-indigo-950 px-4 py-6 text-white transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
>
    <div class="mb-8 flex items-center gap-3 px-2">
        <x-application-logo />
        <div>
            <p class="text-base font-semibold leading-4 text-white">RNM Hardware</p>
            <p class="text-sm text-slate-300">Management System</p>
        </div>

        <button
            type="button"
            @click="mobileOpen = false"
            class="ms-auto rounded-md p-2 text-white hover:bg-white/10 lg:hidden"
            aria-label="Close sidebar"
        >
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="border-white/10 pb-2">
        <p class="px-3 text-sm font-semibold uppercase tracking-wide text-slate-300">
            General
        </p>
    </div>

    <nav class="space-y-1">
        @can('dashboard.view')
        <x-sidebar.link
            href="{{ route('dashboard') }}"
            :active="request()->routeIs('dashboard')"
            @click="mobileOpen = false"
        >
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_840_1468)">
                    <path d="M18.5714 9.28564H12.8571C12.4626 9.28564 12.1428 9.60544 12.1428 9.99993V18.5714C12.1428 18.9658 12.4626 19.2856 12.8571 19.2856H18.5714C18.9659 19.2856 19.2857 18.9658 19.2857 18.5714V9.99993C19.2857 9.60544 18.9659 9.28564 18.5714 9.28564Z" fill="#F9FAFB" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18.5714 0.714233H12.8571C12.4626 0.714233 12.1428 1.03403 12.1428 1.42852V4.29995C12.1428 4.69444 12.4626 5.01423 12.8571 5.01423H18.5714C18.9659 5.01423 19.2857 4.69444 19.2857 4.29995V1.42852C19.2857 1.03403 18.9659 0.714233 18.5714 0.714233Z" fill="#F9FAFB" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7.14293 0.714233H1.42864C1.03415 0.714233 0.714355 1.03403 0.714355 1.42852V9.99995C0.714355 10.3944 1.03415 10.7142 1.42864 10.7142H7.14293C7.53742 10.7142 7.85721 10.3944 7.85721 9.99995V1.42852C7.85721 1.03403 7.53742 0.714233 7.14293 0.714233Z" fill="#F9FAFB" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7.14293 14.9857H1.42864C1.03415 14.9857 0.714355 15.3055 0.714355 15.7V18.5714C0.714355 18.9659 1.03415 19.2857 1.42864 19.2857H7.14293C7.53742 19.2857 7.85721 18.9659 7.85721 18.5714V15.7C7.85721 15.3055 7.53742 14.9857 7.14293 14.9857Z" fill="#F9FAFB" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
                </g>
                <defs>
                    <clipPath id="clip0_840_1468">
                        <rect width="20" height="20" fill="white"/>
                    </clipPath>
                </defs>
            </svg>

            <span class="text-base">Dashboard</span>
        </x-sidebar.link>
        @endcan

        @can('pos.access')
        <x-sidebar.dropdown
            label="POS"
            :open="request()->routeIs('pos*')"
        >
            <x-slot:icon>
                <svg width="18" height="19" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 2.00024L14.829 13.3574C14.7773 13.698 14.6043 14.0085 14.3418 14.2317C14.0793 14.4548 13.7449 14.5756 13.4004 14.5717H4.40043C4.0889 14.5879 3.78062 14.5018 3.52266 14.3264C3.26471 14.151 3.07125 13.8959 2.97186 13.6002L1.07186 7.88596C1.00101 7.67112 0.982206 7.44253 1.01698 7.21901C1.05175 6.99548 1.1391 6.78341 1.27186 6.60024C1.41015 6.4055 1.59507 6.24854 1.8097 6.14372C2.02433 6.0389 2.26181 5.98957 2.50043 6.00024H15.5719" fill="#F9FAFB"/>
                    <path d="M15.5719 6.00024H2.50043C2.26181 5.98957 2.02433 6.0389 1.8097 6.14372C1.59507 6.24854 1.41015 6.4055 1.27186 6.60024C1.1391 6.78341 1.05175 6.99548 1.01698 7.21901C0.982206 7.44253 1.00101 7.67112 1.07186 7.88596L2.97186 13.6002C3.07125 13.8959 3.26471 14.151 3.52266 14.3264C3.78062 14.5018 4.0889 14.5879 4.40043 14.5717H13.4004C13.7449 14.5756 14.0793 14.4548 14.3418 14.2317C14.6043 14.0085 14.7773 13.698 14.829 13.3574L16 2.00024L19 1.00024" stroke="#F9FAFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4.28582 19.286C4.68031 19.286 5.0001 18.9662 5.0001 18.5717C5.0001 18.1772 4.68031 17.8574 4.28582 17.8574C3.89133 17.8574 3.57153 18.1772 3.57153 18.5717C3.57153 18.9662 3.89133 19.286 4.28582 19.286Z" fill="#F9FAFB" stroke="#F9FAFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M13.5715 19.286C13.966 19.286 14.2857 18.9662 14.2857 18.5717C14.2857 18.1772 13.966 17.8574 13.5715 17.8574C13.177 17.8574 12.8572 18.1772 12.8572 18.5717C12.8572 18.9662 13.177 19.286 13.5715 19.286Z" fill="#F9FAFB" stroke="#F9FAFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>

            </x-slot:icon>
            @can('sales.create')
            <x-sidebar.item
                href="{{ route('pos') }}"
                :active="request()->routeIs('pos')"
            >
                New Sale
            </x-sidebar.item>
            @endcan
            @can('sales.view-history')
            <x-sidebar.item
                href="{{ route('pos.transactions') }}"
                :active="request()->routeIs('pos.transactions')"
            >
                Transactions
            </x-sidebar.item>
            @endcan

        </x-sidebar.dropdown>
        @endcan

        @canany(['purchases.create', 'purchases.view-history'])
        <x-sidebar.dropdown
            label="Purchasing"
            :open="request()->routeIs('purchasing.*')"
        >
            <x-slot:icon>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.5715 2.14282H15.7143C16.0932 2.14282 16.4566 2.29333 16.7245 2.56124C16.9924 2.82915 17.1429 3.19251 17.1429 3.57139V17.8571C17.1429 18.236 16.9924 18.5993 16.7245 18.8673C16.4566 19.1352 16.0932 19.2857 15.7143 19.2857H4.28575C3.90687 19.2857 3.54351 19.1352 3.2756 18.8673C3.00769 18.5993 2.85718 18.236 2.85718 17.8571V3.57139C2.85718 3.19251 3.00769 2.82915 3.2756 2.56124C3.54351 2.29333 3.90687 2.14282 4.28575 2.14282H6.42861" fill="#F9FAFB"/>
                    <path d="M13.5715 2.14282H15.7143C16.0932 2.14282 16.4566 2.29333 16.7245 2.56124C16.9924 2.82915 17.1429 3.19251 17.1429 3.57139V17.8571C17.1429 18.236 16.9924 18.5994 16.7245 18.8673C16.4566 19.1352 16.0932 19.2857 15.7143 19.2857H4.28575C3.90687 19.2857 3.54351 19.1352 3.2756 18.8673C3.00769 18.5994 2.85718 18.236 2.85718 17.8571V3.57139C2.85718 3.19251 3.00769 2.82915 3.2756 2.56124C3.54351 2.29333 3.90687 2.14282 4.28575 2.14282H6.42861" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12.143 0.714233H7.85728C7.0683 0.714233 6.42871 1.35383 6.42871 2.1428V2.85709C6.42871 3.64607 7.0683 4.28566 7.85728 4.28566H12.143C12.932 4.28566 13.5716 3.64607 13.5716 2.85709V2.1428C13.5716 1.35383 12.932 0.714233 12.143 0.714233Z" fill="#050938" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.42871 7.85706H13.5716" stroke="#050938" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.42871 11.4285H13.5716" stroke="#050938" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.42871 15H13.5716" stroke="#050938" stroke-linecap="round" stroke-linejoin="round"/>
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
                <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_855_3653)">
                        <path d="M17.8572 0.714355H2.14293C1.35395 0.714355 0.714355 1.35395 0.714355 2.14293V17.8572C0.714355 18.6462 1.35395 19.2858 2.14293 19.2858H17.8572C18.6462 19.2858 19.2858 18.6462 19.2858 17.8572V2.14293C19.2858 1.35395 18.6462 0.714355 17.8572 0.714355Z" fill="#F9FAFB" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13 0V8.18182C13 8.39881 12.921 8.60692 12.7803 8.76036C12.6397 8.9138 12.4489 9 12.25 9H7.75C7.55109 9 7.36032 8.9138 7.21967 8.76036C7.07902 8.60692 7 8.39881 7 8.18182V0" fill="#050938"/>
                        <path d="M13 0V8.18182C13 8.39881 12.921 8.60692 12.7803 8.76036C12.6397 8.9138 12.4489 9 12.25 9H7.75C7.55109 9 7.36032 8.9138 7.21967 8.76036C7.07902 8.60692 7 8.39881 7 8.18182V0" stroke="#050938" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.1428 15.7144H15.7143" stroke="#050938" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_855_3653">
                            <rect width="20" height="20" fill="white"/>
                        </clipPath>
                    </defs>
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
                Manual Stock Out
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

        @can('suppliers.view')
        <div class="mt-6 border-white/10 pb-2 pt-4">
            <p class="px-3 text-sm font-semibold uppercase tracking-wide text-slate-300">
                Admin
            </p>
        </div>
        <x-sidebar.item
            href="{{ route('suppliers.index') }}"
            :active="request()->routeIs('suppliers.*')"
            class="flex items-center gap-3 text-base"
        >
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.1429 10.7142V7.8571C17.1429 7.47822 16.9923 7.11486 16.7244 6.84695C16.4565 6.57904 16.0932 6.42853 15.7143 6.42853H2.14286C1.76398 6.42853 1.40061 6.57904 1.1327 6.84695C0.864796 7.11486 0.714286 7.47822 0.714286 7.8571V17.8571C0.714286 18.236 0.864796 18.5993 1.1327 18.8673C1.40061 19.1352 1.76398 19.2857 2.14286 19.2857H15.7143C16.0932 19.2857 16.4565 19.1352 16.7244 18.8673C16.9923 18.5993 17.1429 18.236 17.1429 17.8571V14.2857H14.1685V10.7142H17.1429Z" fill="#F9FAFB" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M4.44448 3.88886L13.6556 1.13732C13.7466 1.11167 13.8419 1.10473 13.9357 1.11692C14.0294 1.12911 14.1198 1.16018 14.2012 1.20824C14.2826 1.2563 14.3535 1.32037 14.4095 1.39657C14.4655 1.47277 14.5055 1.55953 14.527 1.6516L14.927 3.2516" stroke="#F9FAFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18.5714 10.7142H15C14.6055 10.7142 14.2857 11.034 14.2857 11.4285V13.5714C14.2857 13.9659 14.6055 14.2857 15 14.2857H18.5714C18.9659 14.2857 19.2857 13.9659 19.2857 13.5714V11.4285C19.2857 11.034 18.9659 10.7142 18.5714 10.7142Z" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>

            <span>Supplier Records</span>
        </x-sidebar.item>
        @endcan

        @canany(['audit.user-activity.view', 'audit.system-logs.view'])

        <x-sidebar.item
            href="{{ route('audit-logs.user-activity') }}"
            :active="request()->routeIs('audit-logs.*')"
        >
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_840_1477)">
                    <path d="M2.61842 0.0137294C2.73226 0.00739029 2.87405 0.00405984 2.98915 0.00394458C5.35989 0.00156742 7.73203 0.00380288 10.1026 0.00280639L12.2091 0.00242956C12.6391 0.00253281 13.3118 -0.0180502 13.7065 0.0588042C14.2495 0.167004 14.7492 0.429452 15.1448 0.814264C16.2109 1.85395 15.9698 3.02378 15.987 4.36863C15.9897 4.57981 15.9832 4.79116 15.9855 5.00259C15.7621 4.85511 15.2025 4.67819 14.9434 4.60718C13.2278 4.14837 11.3997 4.37211 9.84772 5.23089C8.30114 6.10213 7.16645 7.54764 6.69328 9.24947C6.21916 10.9567 6.45088 12.7812 7.337 14.3182C8.26508 15.9275 9.72597 16.9864 11.5187 17.4733C12.647 17.7514 13.8284 17.735 14.9484 17.4258C15.3248 17.3199 15.6355 17.1868 16 17.0664C15.9594 17.8132 15.8579 18.2718 15.3955 18.8911C14.991 19.4328 14.2144 19.8986 13.5373 19.9608C12.9934 20.0107 12.2422 19.9892 11.6771 19.9893L8.18181 19.9895L4.50784 19.9909C3.84456 19.9918 3.03379 20.0282 2.39247 19.9489C1.20498 19.802 0.145191 18.7188 0.0273105 17.538C-0.00698802 17.1945 0.000789721 16.826 0.000690565 16.4745L0.0011524 14.8642L0.0017958 9.66079L0.000963829 4.87268L0.00104852 3.43945C0.0012686 3.13526 -0.00804743 2.73924 0.030123 2.44351C0.0980792 1.92173 0.312475 1.42947 0.648858 1.02293C1.15748 0.396316 1.82747 0.0998067 2.61842 0.0137294ZM8.99129 4.61242C9.78385 4.5381 10.0652 3.77657 9.51322 3.24658C9.2974 3.03934 8.45285 3.08683 8.15458 3.0845C7.60525 3.08021 7.05658 3.08765 6.5073 3.08441C5.6575 3.10069 4.78766 3.059 3.94108 3.08909C3.7016 3.10417 3.44411 3.14376 3.28892 3.34044C2.87896 3.85989 3.17416 4.56944 3.8543 4.59919C4.45147 4.62534 5.01925 4.61544 5.60456 4.6154L8.99129 4.61242ZM5.85424 7.6864C6.98664 7.62349 6.99559 6.17909 5.86017 6.1667C5.2445 6.16 4.61962 6.14552 4.00417 6.16245C3.71563 6.17405 3.5156 6.18173 3.29973 6.41712C3.15793 6.57017 3.08458 6.77369 3.09638 6.9813C3.14347 7.78084 4.0888 7.69212 4.64465 7.68965L5.85424 7.6864ZM4.80913 10.76C5.07478 10.7483 5.27016 10.7088 5.462 10.5011C5.60838 10.3445 5.68285 10.1346 5.66766 9.92147C5.61383 9.10125 4.59038 9.22601 4.00633 9.23531C3.71943 9.24746 3.51296 9.25538 3.29886 9.49007C3.1553 9.64504 3.08257 9.85202 3.09793 10.062C3.11118 10.253 3.20207 10.4304 3.34972 10.5535C3.69176 10.8402 4.3526 10.7635 4.80913 10.76Z" fill="#F9FAFB"/>
                    <path d="M12.8896 6.00105C13.809 5.98615 14.5728 6.1283 15.4031 6.54691C16.6147 7.15593 17.5344 8.22261 17.9593 9.51154C18.4133 10.8938 18.2477 12.2262 17.5987 13.5089C18.0023 13.9059 19.7714 15.5846 19.9325 15.9149C20.017 16.0881 20.0192 16.3032 19.9558 16.4833C19.8875 16.6769 19.7377 16.846 19.552 16.934C19.354 17.0279 19.1253 17.019 18.9294 16.9239C18.5719 16.7501 17.4578 15.5393 17.113 15.1823C17.0123 15.0781 16.8372 14.8528 16.7007 14.8095C16.6646 14.798 16.6439 14.8209 16.6125 14.8385C15.7115 15.6323 14.6682 16.1576 13.4538 16.2257C12.0713 16.3167 10.7106 15.8447 9.68073 14.9167C8.6812 14.0184 8.07987 12.7588 8.00939 11.4159C7.92541 10.0279 8.40742 8.66516 9.34522 7.63941C10.249 6.63602 11.5495 6.06865 12.8896 6.00105ZM12.4094 11.1803C12.017 10.8753 11.429 10.3054 10.949 10.3656C10.7513 10.3895 10.5721 10.4937 10.4532 10.6536C10.3293 10.8165 10.2755 11.022 10.3037 11.2247C10.3675 11.6774 10.8715 11.9092 11.2059 12.1895C11.6013 12.4717 12.0414 12.9741 12.5669 12.8906C12.659 12.876 12.7195 12.8491 12.7981 12.8001C13.1866 12.5576 15.605 10.7462 15.7918 10.4884C15.899 10.3404 15.9292 10.1389 15.8977 9.96147C15.8642 9.77277 15.7664 9.58212 15.6056 9.47264C15.4211 9.34702 15.1728 9.32359 14.9566 9.35162C14.6944 9.43506 14.3303 9.7404 14.0973 9.91707L13.0519 10.7101C12.8656 10.8513 12.6021 11.0628 12.4094 11.1803Z" fill="#F9FAFB"/>
                </g>
                <defs>
                    <clipPath id="clip0_840_1477">
                        <rect width="20" height="20" fill="white"/>
                    </clipPath>
                </defs>
            </svg>
            
             <span>Audit Logs</span>
        </x-sidebar.item>
            <!--
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
                -->
        @endcanany



        @canany(['users.create', 'users.view-list', 'users.edit'])
        <x-sidebar.dropdown
            label="Users"
            :open="request()->routeIs('users.*')"
        >
            <x-slot:icon>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_840_1439)">
                        <path d="M10 10.0001C12.5642 10.0001 14.6429 7.92139 14.6429 5.35721C14.6429 2.79303 12.5642 0.714355 10 0.714355C7.43586 0.714355 5.35718 2.79303 5.35718 5.35721C5.35718 7.92139 7.43586 10.0001 10 10.0001Z" fill="#F9FAFB" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M18.8285 19.2857C18.2288 17.4155 17.0506 15.7841 15.4639 14.6266C13.8772 13.4691 11.964 12.8455 9.99996 12.8455C8.03595 12.8455 6.12268 13.4691 4.53598 14.6266C2.94928 15.7841 1.77113 17.4155 1.17139 19.2857H18.8285Z" fill="#F9FAFB" stroke="#F9FAFB" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_840_1439">
                            <rect width="20" height="20" fill="white"/>
                        </clipPath>
                    </defs>
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

    <div class="mt-auto pt-6">
        <form method="POST" action="{{ route('logout') }}" onsubmit="sessionStorage.removeItem('pos_terminal_id')">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-base text-white transition hover:bg-indigo-600/50"
            >
                <svg width="18" height="18" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_840_1419)">
                        <path d="M13.5715 15.0001V17.8572C13.5715 18.2361 13.421 18.5995 13.1531 18.8674C12.8852 19.1353 12.5218 19.2858 12.1429 19.2858H2.14293C1.76405 19.2858 1.40068 19.1353 1.13277 18.8674C0.864865 18.5995 0.714355 18.2361 0.714355 17.8572V2.14293C0.714355 1.76405 0.864865 1.40068 1.13277 1.13277C1.40068 0.864865 1.76405 0.714355 2.14293 0.714355H12.1429C12.5218 0.714355 12.8852 0.864865 13.1531 1.13277C13.421 1.40068 13.5715 1.76405 13.5715 2.14293V5.00007" stroke="#F9FAFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9.28589 10H19.2859" stroke="#F9FAFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16.4287 7.14282L19.2859 9.99997L16.4287 12.8571" stroke="#F9FAFB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_840_1419">
                            <rect width="20" height="20" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>
