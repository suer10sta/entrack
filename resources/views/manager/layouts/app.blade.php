<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DalyTrac Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Manager: responsive tables -> 2-col cards on mobile/tablet */
        @media (max-width: 1024px) {
            .manager-table-wrapper {
                overflow-x: visible !important;
            }

            .manager-table thead {
                display: none;
            }

            .manager-table tbody {
                display: block;
                width: 100%;
            }

            .manager-table tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.92));
                box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
            }

            .manager-table tbody tr.manager-table-group-row {
                display: block;
                padding: 0;
                border: 0;
                border-radius: 0;
                background: transparent;
                box-shadow: none;
                margin-bottom: 10px;
            }

            .manager-table tbody tr.manager-table-group-row td {
                grid-column: auto;
                border-radius: 12px;
            }

            .manager-table tbody tr.manager-table-group-row td[data-label]::before {
                content: none !important;
            }

            .manager-table tbody td {
                display: block;
                width: 100%;
                padding: 10px 12px !important;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.78);
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
            }

            .manager-table tbody td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            .manager-table tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 6px;
            }

            /* Manager: mobile accordion (collapse table-cards) */
            .manager-table tbody tr.manager-card-row.is-collapsed td {
                display: none !important;
            }

            .manager-table tbody tr.manager-card-row.is-collapsed td.manager-toggle-cell {
                display: block !important;
                grid-column: span 2 / span 2;
            }

            .manager-table tbody td.manager-toggle-cell[data-label]::before {
                content: none !important;
            }

            .manager-chevron {
                transition: transform 180ms ease;
            }

            .manager-card-row.is-collapsed .manager-chevron {
                transform: rotate(-90deg);
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen overflow-x-hidden">
        {{-- Sidebar (sticky within full-page scroll) --}}
        @include('manager.sidebar')

        {{-- Main Wrapper (Navbar + Content) --}}
        <div class="flex flex-col flex-1">
            {{-- Navbar --}}
            @include('manager.navbar')

            {{-- Main Content Area --}}
            <main class="flex-1 overflow-x-hidden bg-[#eaebef] p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Manager sidebar toggle (mobile)
        function toggleManagerSidebar(shouldOpen) {
            const sidebar = document.getElementById('managerSidebar');
            const backdrop = document.getElementById('managerSidebarBackdrop');
            if (!sidebar || !backdrop) return;

            const willOpen = shouldOpen === undefined ? sidebar.classList.contains('-translate-x-full') : shouldOpen;

            if (willOpen) {
                sidebar.classList.remove('hidden');
                sidebar.classList.add('flex');
                backdrop.classList.remove('hidden');

                // Ensure starting state then animate in
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('opacity-0');

                requestAnimationFrame(() => {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    backdrop.classList.remove('opacity-0');
                });
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                backdrop.classList.add('opacity-0');

                window.setTimeout(() => {
                    if (sidebar.classList.contains('-translate-x-full')) {
                        sidebar.classList.add('hidden');
                        sidebar.classList.remove('flex');
                        backdrop.classList.add('hidden');
                    }
                }, 200);
            }
        }
    </script>

    <script>
        // Manager: collapse/expand mobile table-cards
        (function initManagerCardsAccordion() {
            const mq = window.matchMedia('(max-width: 1024px)');

            function setRowCollapsed(row, collapsed) {
                if (!row) return;
                row.classList.toggle('is-collapsed', !!collapsed);
                row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            }

            function initForViewport() {
                const rows = Array.from(document.querySelectorAll('tr.manager-card-row'));
                const isMobile = mq.matches;

                for (const row of rows) {
                    const toggleCell = row.querySelector('td.manager-toggle-cell[data-manager-card-toggle]');
                    if (!toggleCell) continue;

                    if (!toggleCell.dataset.managerToggleBound) {
                        toggleCell.dataset.managerToggleBound = '1';
                        toggleCell.addEventListener('click', (e) => {
                            // Allow normal link clicks inside the toggle header
                            const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                            if (tag === 'a' || tag === 'button' || tag === 'input' || tag === 'select' || tag === 'textarea' || tag === 'label') return;

                            // Prevent row-level navigation handlers (onclick on <tr>)
                            e.preventDefault();
                            e.stopPropagation();

                            const nowCollapsed = row.classList.contains('is-collapsed');
                            setRowCollapsed(row, !nowCollapsed);
                        });
                    }

                    setRowCollapsed(row, isMobile);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initForViewport);
            } else {
                initForViewport();
            }

            if (mq.addEventListener) mq.addEventListener('change', initForViewport);
            else mq.addListener(initForViewport);
        })();
    </script>

    @stack('scripts')
</body>
</html>


