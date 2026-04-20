<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', __('logtracker::ui.audit_panel')) | LogTracker</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/framer-motion@10.16.4/dist/framer-motion.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide-icons/0.279.0/umd/lucide.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5',
                            700: '#4338ca', 800: '#3730a3', 900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Outfit', sans-serif; background: #f8fafc; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

        /* Shimmer */
        @keyframes shimmer { from { background-position: -400px 0; } to { background-position: 400px 0; } }
        .animate-shimmer {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 400px 100%;
            animation: shimmer 1.4s ease infinite;
        }

        /* Sidebar */
        #lt-sidebar {
            width: 260px;
            min-width: 260px;
            background: #1e1b4b;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: sticky;
            top: 0;
            transition: width 0.25s ease, min-width 0.25s ease;
            overflow: hidden;
        }
        #lt-sidebar.collapsed {
            width: 72px;
            min-width: 72px;
        }
        .lt-logo {
            padding: 24px 20px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .lt-logo-text {
            font-size: 18px;
            font-weight: 900;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            transition: opacity 0.2s ease;
        }
        #lt-sidebar.collapsed .lt-logo-text { opacity: 0; width: 0; }
        .lt-toggle {
            width: 30px; height: 30px;
            border-radius: 8px;
            background: rgba(255,255,255,0.08);
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: #a5b4fc;
            flex-shrink: 0;
            transition: background 0.2s;
        }
        .lt-toggle:hover { background: rgba(255,255,255,0.15); }

        .lt-nav { padding: 16px 12px; flex: 1; overflow-y: auto; }
        .lt-nav-label {
            font-size: 9px; font-weight: 800; letter-spacing: 0.12em;
            text-transform: uppercase; color: rgba(165,180,252,0.5);
            padding: 0 8px; margin: 16px 0 6px;
            white-space: nowrap; overflow: hidden;
            transition: opacity 0.2s;
        }
        #lt-sidebar.collapsed .lt-nav-label { opacity: 0; }

        .lt-nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 11px 12px; border-radius: 10px;
            color: rgba(199,210,254,0.75);
            text-decoration: none; font-size: 14px; font-weight: 600;
            margin-bottom: 2px; white-space: nowrap;
            transition: background 0.15s, color 0.15s;
            border: 1px solid transparent;
        }
        .lt-nav-item:hover {
            background: rgba(255,255,255,0.07);
            color: #ffffff;
        }
        .lt-nav-item.active {
            background: rgba(99,102,241,0.25);
            color: #ffffff;
            border-color: rgba(99,102,241,0.4);
        }
        .lt-nav-item.active .lt-nav-icon { color: #a5b4fc; }
        .lt-nav-icon { width: 18px; height: 18px; flex-shrink: 0; }
        .lt-nav-text { overflow: hidden; transition: opacity 0.2s, width 0.2s; }
        #lt-sidebar.collapsed .lt-nav-text { opacity: 0; width: 0; overflow: hidden; }

        .lt-footer {
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .lt-lang-btn {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 10px 12px; border-radius: 10px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            color: rgba(199,210,254,0.7);
            font-size: 13px; font-weight: 700;
            cursor: pointer; white-space: nowrap;
            transition: background 0.15s, color 0.15s;
        }
        .lt-lang-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
        #lt-sidebar.collapsed .lt-lang-text { display: none; }

        /* Main */
        #lt-main { flex: 1; min-width: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        #lt-topbar {
            height: 60px; min-height: 60px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px;
        }
        .lt-page-title { font-size: 13px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; }
        .lt-badge {
            font-size: 10px; font-weight: 800; color: #6366f1;
            background: #eef2ff; border: 1px solid #c7d2fe;
            padding: 3px 10px; border-radius: 99px; letter-spacing: 0.05em;
        }
        #lt-content { flex: 1; overflow-y: auto; padding: 28px; }
    </style>

    @yield('styles')
</head>
<body>
    <div id="logtracker-root" style="display: flex; height: 100vh; overflow: hidden;"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;
        const { motion, AnimatePresence } = Motion;

        const MENU = [
            { id: 'audit',    label: 'Audit Trail',  icon: 'history',    url: "{{ route('logtracker.index') }}" },
            { id: 'insights', label: 'Insights',     icon: 'bar-chart-2',url: "{{ route('logtracker.insights') }}" },
            { id: 'system',   label: 'System Logs',  icon: 'terminal',   url: "{{ route('logtracker.system-logs') }}" },
        ];

        const PAGE_TITLES = {
            audit: 'Audit Trail',
            insights: 'Analytics & Insights',
            system: 'System Logs',
        };

        function SidebarLayout({ children, activePage }) {
            const [collapsed, setCollapsed] = useState(false);

            useEffect(() => {
                if (window.lucide) window.lucide.createIcons();
            }, [collapsed]);

            const toggleLang = () => {
                const locale = "{{ app()->getLocale() === 'en' ? 'bn' : 'en' }}";
                const url = new URL(window.location.href);
                url.searchParams.set('locale', locale);
                window.location.href = url.toString();
            };

            return (
                <div style=@{{ display: 'flex', height: '100vh', overflow: 'hidden', width: '100%' }}>
                    {/* ── Sidebar ── */}
                    <div id="lt-sidebar" className={collapsed ? 'collapsed' : ''}>
                        <div className="lt-logo">
                            <span className="lt-logo-text">LogTracker</span>
                            <button className="lt-toggle" onClick={() => { setCollapsed(!collapsed); setTimeout(() => window.lucide?.createIcons(), 50); }}>
                                <i data-lucide={collapsed ? 'chevron-right' : 'chevron-left'} style=@{{ width:16, height:16 }}></i>
                            </button>
                        </div>

                        <nav className="lt-nav">
                            <div className="lt-nav-label">Navigation</div>
                            {MENU.map(item => (
                                <a key={item.id} href={item.url}
                                    className={`lt-nav-item ${activePage === item.id ? 'active' : ''}`}
                                    title={collapsed ? item.label : ''}>
                                    <i data-lucide={item.icon} className="lt-nav-icon"></i>
                                    <span className="lt-nav-text">{item.label}</span>
                                </a>
                            ))}
                        </nav>

                        <div className="lt-footer">
                            <button className="lt-lang-btn" onClick={toggleLang}>
                                <i data-lucide="globe" style=@{{ width:16, height:16, flexShrink:0 }}></i>
                                <span className="lt-lang-text">{{ app()->getLocale() === 'bn' ? 'বাংলা' : 'English' }}</span>
                            </button>
                        </div>
                    </div>

                    {/* ── Main ── */}
                    <div id="lt-main">
                        <div id="lt-topbar">
                            <span className="lt-page-title">{PAGE_TITLES[activePage] || 'LogTracker'}</span>
                            <span className="lt-badge">AUDIT ENGINE v2.0</span>
                        </div>
                        <div id="lt-content">
                            {children}
                        </div>
                    </div>
                </div>
            );
        }

        window.SidebarLayout = SidebarLayout;
    </script>
    @yield('scripts')
</body>
</html>
