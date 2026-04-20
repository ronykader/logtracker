<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('logtracker::ui.audit_panel') }}</title>
    
    <!-- Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS for modern layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- React & Framer Motion for premium animations -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/framer-motion@10.16.4/dist/framer-motion.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide-icons/0.279.0/umd/lucide.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- Flatpickr for premium Date Picking -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .flatpickr-calendar {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(10px) !important;
            border: 1px solid rgba(0, 0, 0, 0.1) !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            border-radius: 12px !important;
        }
    </style>
    <script>
        // Pre-check for Babel
        console.log("Babel system ready.");
    </script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f5f7ff',
                            100: '#ebf0fe',
                            200: '#ced9fd',
                            300: '#b1c2fc',
                            400: '#7795f9',
                            500: '#3d67f7',
                            600: '#375dde',
                            700: '#2e4db9',
                            800: '#253e94',
                            900: '#1e3279',
                        },
                    }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer components {
            .glass-card {
                @apply bg-white/70 backdrop-blur-xl border border-white/20 shadow-xl shadow-slate-200/50;
            }
            .data-row {
                @apply transition-all duration-300 hover:bg-primary-50/50 cursor-pointer border-b border-slate-100;
            }
            .badge-pill {
                @apply px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

        /* Diff Highlighting */
        .diff-added { background-color: #dcfce7; color: #166534; padding: 2px 4px; border-radius: 4px; }
        .diff-removed { background-color: #fee2e2; color: #991b1b; text-decoration: line-through; padding: 2px 4px; border-radius: 4px; }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .animate-shimmer {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen">
    <div id="audit-panel-root">
        <div class="flex items-center justify-center min-h-screen text-slate-400 font-medium">
            Initializing Audit Dashboard...
        </div>
    </div>

    <script type="text/babel" data-presets="react">
        try {
            const { useState, useEffect, useMemo } = React;
            
            // Safety Mode: If Motion fails to load, we use standard divs
            const MotionSafe = window.Motion || window.framerMotion || { 
                motion: { 
                    div: (props) => <div {...props}>{props.children}</div>,
                    tr: (props) => <tr {...props}>{props.children}</tr>
                }, 
                AnimatePresence: ({children}) => <React.Fragment>{children}</React.Fragment> 
            };
            const { motion, AnimatePresence } = MotionSafe;

            // Translation Injection from Laravel
            const i18n = {
            panel_title: "{{ __('logtracker::ui.audit_panel') }}",
            subtitle: "{{ __('logtracker::ui.panel_subtitle') }}",
            columns: {
                date: "{{ __('logtracker::ui.table_header_date') }}",
                time: "{{ __('logtracker::ui.table_header_time') }}",
                user: "{{ __('logtracker::ui.table_header_user') }}",
                table: "{{ __('logtracker::ui.table_header_table') }}",
                type: "{{ __('logtracker::ui.table_header_type') }}",
                action: "{{ __('logtracker::ui.table_header_action') }}"
            },
            filters: {
                table: "{{ __('logtracker::ui.filter_label_table') }}",
                type: "{{ __('logtracker::ui.filter_label_type') }}",
                search: "{{ __('logtracker::ui.filter_label_search') }}",
                searchPlaceholder: "{{ __('logtracker::ui.filter_placeholder_search') }}",
                pageSize: "{{ __('logtracker::ui.filter_label_page_size') }}",
                allTables: "{{ __('logtracker::ui.all_tables') }}",
                allTypes: "{{ __('logtracker::ui.all_types') }}",
                refresh: "{{ __('logtracker::ui.filter_button_refresh') }}",
                reset: "{{ __('logtracker::ui.filter_button_reset') }}"
            },
            details: {
                title: "{{ __('logtracker::ui.details_title') }}",
                close: "{{ __('logtracker::ui.details_modal_close') }}",
                field: "{{ __('logtracker::ui.details_field_label') }}",
                old: "{{ __('logtracker::ui.details_old_value') }}",
                new: "{{ __('logtracker::ui.details_new_value') }}",
                url: "{{ __('logtracker::ui.details_url') }}",
                route: "{{ __('logtracker::ui.details_route') }}"
            },
            types: {
                create: "{{ __('logtracker::ui.log_type_create') }}",
                edit: "{{ __('logtracker::ui.log_type_edit') }}",
                delete: "{{ __('logtracker::ui.log_type_delete') }}"
            },
            empty: "{{ __('logtracker::ui.no_logs_found') }}",
            loading: "{{ __('logtracker::ui.loading_logs') }}"
        };

        const currentLocale = "{{ app()->getLocale() }}";

        const changeLanguage = (locale) => {
            const url = new URL(window.location.href);
            url.searchParams.set('locale', locale);
            window.location.href = url.toString();
        };

        const SkeletonRow = () => (
            <tr className="border-b border-slate-100 animate-pulse">
                <td className="p-4" colSpan={6}>
                    <div className="h-8 bg-slate-100 rounded-lg animate-shimmer"></div>
                </td>
            </tr>
        );

        function AuditPanel() {
            const [logs, setLogs] = useState([]);
            const [filters, setFilters] = useState({ tables: [], users: [], types: [] });
            const [params, setParams] = useState({ table: '', type: '', search: '', start_date: '', end_date: '' });
            const [page, setPage] = useState(1);
            const [pageSize, setPageSize] = useState(10);
            const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
            const [selectedLog, setSelectedLog] = useState(null);
            const [isMaximized, setIsMaximized] = useState(false);
            const [isModalOpen, setIsModalOpen] = useState(false);
            const [loading, setLoading] = useState(true);
            const [showInsights, setShowInsights] = useState(false);
            const [insights, setInsights] = useState({ top_tables: [], top_users: [], activity: [] });

            useEffect(() => {
                loadLogs();
                loadInsights();
            }, [page, pageSize]);

            useEffect(() => {
                if (showInsights && insights.top_tables.length > 0) {
                    renderCharts();
                }
            }, [showInsights, insights]);

            const renderCharts = () => {
                // Table Distribution Donut
                const tableOptions = {
                    series: insights.top_tables.map(t => t.count),
                    labels: insights.top_tables.map(t => t.table_name),
                    chart: { type: 'donut', height: 220 },
                    colors: ['#3d67f7', '#7795f9', '#ced9fd', '#b1c2fc', '#f5f7ff'],
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom', fontSize: '10px', fontWeight: 600 }
                };
                new ApexCharts(document.querySelector("#chart-tables-pie"), tableOptions).render();

                // Activity Area Chart
                const activityOptions = {
                    series: [{ name: 'Logs', data: insights.activity.map(a => a.count) }],
                    chart: { type: 'area', height: 200, toolbar: { show: false }, zoom: { enabled: false } },
                    colors: ['#10b981'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    xaxis: { categories: insights.activity.map(a => a.date.split('-').slice(1).join('/')), labels: { style: { fontSize: '9px', fontWeight: 600 } } },
                    yaxis: { show: false }
                };
                new ApexCharts(document.querySelector("#chart-activity-area"), activityOptions).render();
            };

            useEffect(() => {
                flatpickr(".datepicker-input", {
                    mode: "range",
                    dateFormat: "Y-m-d",
                    animate: true,
                    onClose: (selectedDates) => {
                        if (selectedDates.length === 2) {
                            setParams(prev => ({
                                ...prev,
                                start_date: selectedDates[0].toISOString().split('T')[0],
                                end_date: selectedDates[1].toISOString().split('T')[0]
                            }));
                        } else if (selectedDates.length === 0) {
                            setParams(prev => ({ ...prev, start_date: '', end_date: '' }));
                        }
                    }
                });
            }, []);

            const loadInsights = (overrideParams = {}) => {
                const queryParams = new URLSearchParams({
                    ...params,
                    ...overrideParams
                });
                fetch("{{ url(config('logtracker.api_prefix', 'api/audit-panel-data')) }}/insights?" + queryParams.toString())
                    .then(r => r.json())
                    .then(json => setInsights(json));
            };

            const exportToCSV = () => {
                const headers = ['Date', 'Time', 'User ID', 'Table', 'Type', 'Data'];
                const rows = logs.map(l => [
                    l.log_date,
                    l.log_time,
                    l.user_id,
                    l.table_name,
                    l.log_type,
                    JSON.stringify(l.data).replace(/"/g, '""')
                ]);
                
                let csvContent = "data:text/csv;charset=utf-8," 
                    + headers.join(",") + "\n"
                    + rows.map(e => e.join(",")).join("\n");
                
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", `audit_log_${new Date().toISOString().split('T')[0]}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            const loadLogs = (overrideParams = {}) => {
                setLoading(true);
                const currentParams = { ...params, ...overrideParams };
                const queryParams = new URLSearchParams({
                    page,
                    per_page: pageSize,
                    ...currentParams
                });

                fetch("{{ url(config('logtracker.api_prefix', 'api/audit-panel-data')) }}?" + queryParams.toString())
                    .then(r => r.json())
                    .then(json => {
                        setLogs(json.data || []);
                        setFilters(json.filters || { tables: [], users: [], types: [] });
                        setMeta(json.meta || meta);
                        setLoading(false);
                    });
                
                // Keep insights in sync with the same filters
                loadInsights(overrideParams);
            };

            const handleFilterChange = (e) => {
                const { name, value } = e.target;
                setParams(prev => ({ ...prev, [name]: value }));
            };

            const executeSearch = (e) => {
                e.preventDefault();
                setPage(1);
                loadLogs({ page: 1 });
            };

            const resetFilters = () => {
                const defaults = { table: '', type: '', search: '', start_date: '', end_date: '' };
                setParams(defaults);
                setPage(1);
                loadLogs({ ...defaults, page: 1 });
            };

            const getTypeStyle = (type) => {
                const t = String(type).toLowerCase();
                if (t.includes('delete')) return 'bg-rose-100 text-rose-700';
                if (t.includes('update') || t.includes('edit')) return 'bg-amber-100 text-amber-700';
                return 'bg-emerald-100 text-emerald-700';
            };

            const parseData = (data) => {
                if (!data) return {};
                try {
                    return typeof data === 'object' ? data : JSON.parse(data || '{}');
                } catch (e) {
                    return {};
                }
            };

            const diffData = useMemo(() => {
                if (!selectedLog) return [];
                const oldObj = parseData(selectedLog.details || selectedLog.data);
                const newObj = parseData(selectedLog.new_log_details || selectedLog.new_data);
                const keys = [...new Set([...Object.keys(oldObj), ...Object.keys(newObj)])];
                
                return keys.map(key => ({
                    key,
                    old: oldObj[key],
                    new: newObj[key],
                    changed: JSON.stringify(oldObj[key]) !== JSON.stringify(newObj[key])
                }));
            }, [selectedLog]);

            return (
                <div className="max-w-7xl mx-auto px-4 py-8 md:px-8">
                    {/* Header Section */}
                    <motion.div 
                        initial=@{{ opacity: 0, y: -20 }}
                        animate=@{{ opacity: 1, y: 0 }}
                        className="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <div>
                            <h1 className="text-3xl font-extrabold text-slate-900 tracking-tight">{i18n.panel_title}</h1>
                            <p className="text-slate-500 mt-1 font-medium">{i18n.subtitle}</p>
                        </div>

                        {/* Modern Language Switcher */}
                        <div className="flex bg-slate-200/50 backdrop-blur-md p-1 rounded-xl w-fit self-end md:self-auto">
                            <button 
                                onClick={() => changeLanguage('en')}
                                className={`px-4 py-2 rounded-lg text-xs font-black transition-all duration-300 flex items-center gap-2 ${currentLocale === 'en' ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
                            >
                                <span className="w-2 h-2 rounded-full bg-blue-500"></span>
                                ENGLISH
                            </button>
                            <button 
                                onClick={() => changeLanguage('bn')}
                                className={`px-4 py-2 rounded-lg text-xs font-black transition-all duration-300 flex items-center gap-2 ${currentLocale === 'bn' ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
                            >
                                <span className="w-2 h-2 rounded-full bg-green-500"></span>
                                বাংলা
                            </button>
                        </div>
                    </motion.div>

                    {/* Insights Section */}
                    <div className="mb-6">
                        <button 
                            onClick={() => setShowInsights(!showInsights)}
                            className="flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-[0.2em] hover:text-primary-600 transition-colors mb-4"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><path d="m19 9-7 7-7-7"/></svg>
                            {showInsights ? 'Hide Insights' : 'Show Visual Insights'}
                        </button>
                        
                        <AnimatePresence>
                            {showInsights && (
                                <motion.div 
                                    initial=@{{ opacity: 0, height: 0 }}
                                    animate=@{{ opacity: 1, height: 'auto' }}
                                    exit=@{{ opacity: 0, height: 0 }}
                                    className="grid grid-cols-1 md:grid-cols-3 gap-6 overflow-hidden pb-4"
                                >
                                    <div className="glass-card rounded-2xl p-6">
                                        <h3 className="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 text-center">Table Distribution</h3>
                                        <div id="chart-tables-pie" className="h-[220px]"></div>
                                    </div>

                                    <div className="glass-card rounded-2xl p-6">
                                        <h3 className="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 text-center">Active Contributors</h3>
                                        <div className="space-y-4">
                                            {insights.top_users.slice(0, 5).map((u, i) => (
                                                <div key={i} className="flex items-center justify-between group p-2 hover:bg-slate-50 rounded-xl transition-all">
                                                    <div className="flex items-center gap-3">
                                                        <div className="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center text-[10px] font-black text-primary-700">{u.count}</div>
                                                        <div className="text-xs font-bold text-slate-700">{u.name}</div>
                                                    </div>
                                                    <div className="w-1.5 h-1.5 rounded-full bg-slate-200 group-hover:bg-primary-500"></div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    <div className="glass-card rounded-2xl p-6">
                                        <h3 className="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 text-center">Activity Velocity</h3>
                                        <div id="chart-activity-area" className="h-[200px]"></div>
                                    </div>
                                </motion.div>
                            )}
                        </AnimatePresence>
                    </div>

                    {/* Filter Card */}
                    <motion.div 
                        initial=@{{ opacity: 0, y: 20 }}
                        animate=@{{ opacity: 1, y: 0 }}
                        transition=@{{ delay: 0.1 }}
                        className="glass-card rounded-2xl p-6 mb-8"
                    >
                        <form onSubmit={executeSearch} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div className="space-y-2">
                                    <label className="text-xs font-black text-slate-400 uppercase tracking-widest">{i18n.filters.search}</label>
                                    <div className="relative">
                                        <input 
                                            type="text" 
                                            name="search" 
                                            value={params.search} 
                                            onChange={handleFilterChange} 
                                            placeholder={i18n.filters.searchPlaceholder}
                                            className="w-full bg-slate-50 border-none rounded-xl pl-10 pr-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-primary-400 outline-none transition-all"
                                        />
                                        <svg className="absolute left-3 top-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <label className="text-xs font-black text-slate-400 uppercase tracking-widest">{i18n.filters.table}</label>
                                    <select name="table" value={params.table} onChange={handleFilterChange} className="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-primary-400 outline-none transition-all">
                                        <option value="">{i18n.filters.allTables}</option>
                                        {filters.tables.map(t => <option key={t} value={t}>{t}</option>)}
                                    </select>
                                </div>
                                <div className="space-y-2">
                                    <label className="text-xs font-black text-slate-400 uppercase tracking-widest">{i18n.filters.type}</label>
                                    <select name="type" value={params.type} onChange={handleFilterChange} className="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-primary-400 outline-none transition-all">
                                        <option value="">{i18n.filters.allTypes}</option>
                                        {filters.types.map(t => <option key={t} value={t}>{t}</option>)}
                                    </select>
                                </div>
                                <div className="space-y-2">
                                    <label className="text-xs font-black text-slate-400 uppercase tracking-widest">Global Search</label>
                                    <div className="relative">
                                        <input 
                                            name="search" 
                                            type="text" 
                                            value={params.search} 
                                            onChange={handleFilterChange}
                                            placeholder="Search by ID or content..." 
                                            className="w-full bg-slate-50 border-none rounded-xl pl-10 pr-4 py-3 text-sm font-semibold focus:ring-2 focus:ring-primary-400 outline-none transition-all"
                                        />
                                        <span className="absolute left-3 top-3.5 text-slate-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
                                <div className="lg:col-span-2 space-y-2">
                                    <label className="text-xs font-black text-slate-400 uppercase tracking-widest">Selected Period</label>
                                    <div className="relative group">
                                        <input 
                                            type="text" 
                                            placeholder="Pick range..."
                                            value={params.start_date ? `${params.start_date} → ${params.end_date}` : ''}
                                            readOnly
                                            className="datepicker-input w-full bg-slate-50 border-none rounded-xl pl-12 pr-4 py-3 text-sm font-bold text-slate-700 focus:ring-2 focus:ring-primary-400 outline-none transition-all cursor-pointer group-hover:bg-slate-100"
                                        />
                                        <span className="absolute left-4 top-3 text-primary-500 pointer-events-none group-hover:scale-110 transition-transform">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                        </span>
                                    </div>
                                </div>
                                <div className="lg:col-span-2 flex gap-2">
                                    <button type="submit" className="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg shadow-primary-200">
                                        Apply Filters
                                    </button>
                                    <button type="button" onClick={exportToCSV} title="Export CSV" className="bg-emerald-600 hover:bg-emerald-700 text-white p-3 rounded-xl transition-all shadow-lg shadow-emerald-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                    </button>
                                    <button type="button" onClick={resetFilters} className="bg-slate-100 hover:bg-slate-200 text-slate-600 p-3 rounded-xl transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </motion.div>

                    {/* Table Section */}
                    <motion.div 
                        initial=@{{ opacity: 0 }}
                        animate=@{{ opacity: 1 }}
                        transition=@{{ delay: 0.2 }}
                        className="glass-card rounded-2xl overflow-hidden"
                    >
                        <div className="overflow-x-auto">
                            <table className="w-full border-collapse">
                                <thead>
                                    <tr className="bg-slate-50/50 border-b border-slate-100">
                                        <th className="p-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">{i18n.columns.date}</th>
                                        <th className="p-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">{i18n.columns.user}</th>
                                        <th className="p-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">{i18n.columns.table}</th>
                                        <th className="p-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">{i18n.columns.type}</th>
                                        <th className="p-5 text-right text-xs font-bold text-slate-400 uppercase tracking-widest">{i18n.columns.action}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <AnimatePresence mode="popLayout">
                                        {loading ? (
                                            Array(pageSize).fill(0).map((_, i) => <SkeletonRow key={i} />)
                                        ) : (
                                            logs.map((log, i) => (
                                                <motion.tr 
                                                    key={log.id}
                                                    initial=@{{ opacity: 0, scale: 0.98 }}
                                                    animate=@{{ opacity: 1, scale: 1 }}
                                                    transition=@{{ delay: i * 0.03 }}
                                                    onClick={() => { setSelectedLog(log); setIsModalOpen(true); }}
                                                    className="data-row group"
                                                >
                                                    <td className="p-5">
                                                        <div className="font-bold text-slate-700">{log.log_date}</div>
                                                        <div className="text-[10px] text-slate-400 uppercase font-black">{log.log_time}</div>
                                                    </td>
                                                    <td className="p-5">
                                                        <div className="flex items-center gap-3">
                                                            <div className="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-bold text-sm">
                                                                {String(JSON.parse(log.users || '{}').name || 'U').charAt(0)}
                                                            </div>
                                                            <div>
                                                                <div className="font-bold text-slate-700">{JSON.parse(log.users || '{}').name || 'System User'}</div>
                                                                <div className="text-xs text-slate-400 font-medium">ID: {log.user_id}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="p-5 font-bold text-primary-600 text-sm">
                                                        <span className="bg-primary-50 px-3 py-1 rounded-lg">
                                                            {log.table_name}
                                                        </span>
                                                    </td>
                                                    <td className="p-5">
                                                        <span className={`badge-pill ${getTypeStyle(log.log_type)}`}>
                                                            {i18n.types[log.log_type.toLowerCase()] || log.log_type}
                                                        </span>
                                                    </td>
                                                    <td className="p-5 text-right">
                                                        <button className="bg-slate-100 group-hover:bg-primary-600 group-hover:text-white transition-all p-2 rounded-lg">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-arrow-right"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                                        </button>
                                                    </td>
                                                </motion.tr>
                                            ))
                                        )}
                                    </AnimatePresence>
                                </tbody>
                            </table>
                        </div>
                        
                        {/* Pagination */}
                        <div className="p-6 bg-slate-50/30 border-t border-slate-100 flex items-center justify-between">
                            <span className="text-xs font-bold text-slate-500">
                                Page {meta.current_page} of {meta.last_page} | Total {meta.total} records
                            </span>
                            <div className="flex gap-2">
                                <button 
                                    onClick={() => setPage(p => Math.max(1, p - 1))}
                                    disabled={page === 1}
                                    className="p-2 rounded-lg bg-white border border-slate-200 disabled:opacity-50 hover:bg-slate-50 transition-all font-bold text-slate-600"
                                >
                                    Prev
                                </button>
                                <button 
                                    onClick={() => setPage(p => Math.min(meta.last_page, p + 1))}
                                    disabled={page === meta.last_page}
                                    className="p-2 rounded-lg bg-white border border-slate-200 disabled:opacity-50 hover:bg-slate-50 transition-all font-bold text-slate-600"
                                >
                                    Next
                                </button>
                            </div>
                        </div>
                    </motion.div>

                    {/* Activity Heatmap Grid */}
                    <motion.div 
                        initial=@{{ opacity: 0 }}
                        animate=@{{ opacity: 1 }}
                        transition=@{{ delay: 0.3 }}
                        className="glass-card rounded-2xl p-6 mt-8"
                    >
                        <div className="flex items-center justify-between mb-6">
                            <h3 className="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">30-Day Activity Heatmap</h3>
                            <div className="flex items-center gap-1.5 text-[8px] font-black text-slate-400">
                                <span>LESS</span>
                                <div className="flex gap-1">
                                    <div className="w-2.5 h-2.5 rounded-sm bg-slate-100"></div>
                                    <div className="w-2.5 h-2.5 rounded-sm bg-emerald-200"></div>
                                    <div className="w-2.5 h-2.5 rounded-sm bg-emerald-400"></div>
                                    <div className="w-2.5 h-2.5 rounded-sm bg-emerald-600"></div>
                                </div>
                                <span>MORE</span>
                            </div>
                        </div>
                        
                        <div className="flex flex-wrap gap-2 pb-2">
                            {Array(30).fill(0).map((_, i) => {
                                const date = new Date();
                                date.setDate(date.getDate() - (29 - i));
                                const dateStr = date.toISOString().split('T')[0];
                                const dayData = (insights.activity || []).find(a => a.date === dateStr);
                                const count = dayData ? dayData.count : 0;
                                
                                let color = 'bg-slate-100';
                                if (count > 0) color = 'bg-emerald-200 ring-1 ring-emerald-300';
                                if (count > 5) color = 'bg-emerald-400 ring-1 ring-emerald-500';
                                if (count > 20) color = 'bg-emerald-600 shadow-md ring-1 ring-emerald-700';
                                
                                return (
                                    <div 
                                        key={i} 
                                        title={`${dateStr}: ${count} logs`}
                                        className={`w-4 h-4 rounded-[3px] ${color} transition-all duration-300 hover:scale-150 cursor-help hover:z-20`}
                                    ></div>
                                );
                            })}
                        </div>
                        <div className="mt-4 flex justify-between text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">
                            <span>30 Days Ago</span>
                            <span>Today</span>
                        </div>
                    </motion.div>

                    {/* Detail Modal Overlay */}
                    <AnimatePresence>
                        {isModalOpen && (
                            <motion.div 
                                initial=@{{ opacity: 0 }}
                                animate=@{{ opacity: 1 }}
                                exit=@{{ opacity: 0 }}
                                className="fixed inset-0 z-50 flex justify-end bg-slate-900/40 backdrop-blur-sm"
                                onClick={() => setIsModalOpen(false)}
                            >
                                <motion.div 
                                    key="modal-content"
                                    initial=@{{ x: '100%' }}
                                    animate=@{{ x: 0 }}
                                    exit=@{{ x: '100%' }}
                                    transition=@{{ 
                                        type: 'spring', 
                                        damping: 35, 
                                        stiffness: 450,
                                        mass: 0.5
                                    }}
                                    className={`bg-white h-screen shadow-[-20px_0_50px_-10px_rgba(30,41,59,0.1)] relative flex flex-col pt-24 border-l border-slate-100 transition-all duration-200 ease-out ${isMaximized ? 'w-full max-w-full' : 'w-full md:w-[45rem] max-w-[95vw]'}`}
                                    onClick={e => e.stopPropagation()}
                                >
                                    {/* Action Icons Top Right */}
                                    <div className="absolute top-6 right-8 flex items-center gap-3 z-30">
                                        <button 
                                            onClick={() => setIsMaximized(!isMaximized)}
                                            className="p-3 bg-white/90 backdrop-blur-md shadow-sm border border-indigo-100 rounded-2xl text-slate-400 hover:text-indigo-600 hover:border-indigo-300 transition-all duration-300"
                                            title={isMaximized ? "Restore" : "Full Screen"}
                                        >
                                            {isMaximized ? (
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"/></svg>
                                            ) : (
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M15 3h6v6M9 21H3v-6M21 15v6h-6M3 9V3h6"/></svg>
                                            )}
                                        </button>
                                        <button 
                                            onClick={() => setIsModalOpen(false)}
                                            className="p-3 bg-white/90 backdrop-blur-md shadow-sm border border-rose-100 rounded-2xl text-slate-400 hover:text-rose-600 hover:border-rose-300 transition-all duration-300"
                                            title="Close"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                        </button>
                                    </div>

                                    <div className="px-8 pb-8 flex-1 overflow-y-auto">
                                        <div className="mb-8 p-6 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                                            <div>
                                                <h2 className="text-2xl font-black text-slate-900">{i18n.details.title}</h2>
                                                <div className="flex items-center gap-2 mt-2">
                                                    <span className={`badge-pill ${getTypeStyle(selectedLog.log_type)}`}>{selectedLog.log_type}</span>
                                                    <span className="text-xs font-bold text-slate-400 uppercase tracking-widest">{selectedLog.table_name}</span>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-lg font-bold text-slate-900">{selectedLog.log_date}</div>
                                                <div className="text-xs font-bold text-slate-400 uppercase">{selectedLog.log_time}</div>
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-2 gap-4 mb-8">
                                            <div className="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100">
                                                <div className="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">IP Address</div>
                                                <div className="text-sm font-black text-indigo-700">{selectedLog.ip_address || '0.0.0.0'}</div>
                                            </div>
                                            <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                                <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">User Agent</div>
                                                <div className="text-[10px] font-bold text-slate-600 truncate" title={selectedLog.user_agent}>
                                                    {selectedLog.user_agent || 'Unknown Browser'}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-2 gap-4 mb-8">
                                            <div className="p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100 overflow-hidden">
                                                <div className="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-1">{i18n.details.url}</div>
                                                <div className="text-[10px] font-bold text-emerald-700 truncate" title={selectedLog.url}>
                                                    {selectedLog.url || 'N/A'}
                                                </div>
                                            </div>
                                            <div className="p-4 bg-amber-50/50 rounded-2xl border border-amber-100 overflow-hidden">
                                                <div className="text-[10px] font-black text-amber-400 uppercase tracking-widest mb-1">{i18n.details.route}</div>
                                                <div className="text-[10px] font-bold text-amber-700 truncate" title={selectedLog.route_name}>
                                                    {selectedLog.route_name || 'N/A'}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="space-y-6">
                                            <table className="w-full border-collapse">
                                                <thead>
                                                    <tr className="text-left">
                                                        <th className="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] w-1/4">{i18n.details.field}</th>
                                                        <th className="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] w-3/8 text-rose-600">{i18n.details.old}</th>
                                                        <th className="pb-4 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] w-3/8 text-emerald-600">{i18n.details.new}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {diffData.map(row => (
                                                        <tr key={row.key} className={`border-b border-slate-50 ${row.changed ? 'bg-amber-50/30' : ''}`}>
                                                            <td className="py-4 text-xs font-bold text-slate-600 align-top">{row.key.replace(/_/g, ' ')}</td>
                                                            <td className="py-4 px-2 text-xs font-medium text-slate-500 break-words align-top">
                                                                <span className={row.changed ? 'text-rose-400 line-through decoration-rose-300' : ''}>
                                                                    {String(row.old ?? '—')}
                                                                </span>
                                                            </td>
                                                            <td className="py-4 px-2 text-xs font-bold text-slate-800 break-words align-top">
                                                                <span className={row.changed ? 'text-emerald-700 bg-emerald-50 px-1 rounded' : ''}>
                                                                    {String(row.new ?? '—')}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div className="p-8 bg-slate-50 border-t border-slate-100">
                                        <button 
                                            onClick={() => setIsModalOpen(false)}
                                            className="w-full bg-slate-900 text-white font-bold py-4 rounded-2xl shadow-xl shadow-slate-200 transition-all hover:bg-slate-800"
                                        >
                                            {i18n.details.close}
                                        </button>
                                    </div>
                                </motion.div>
                            </motion.div>
                        )}
                    </AnimatePresence>
                </div>
            );
        }

            const rootElement = document.getElementById('audit-panel-root');
            if (rootElement) {
                const rootLayout = ReactDOM.createRoot(rootElement);
                rootLayout.render(<AuditPanel />);
            }
        } catch (err) {
            console.error("Dashboard Render Error:", err);
            const rootElement = document.getElementById('audit-panel-root');
            if (rootElement) {
                rootElement.innerHTML = `<div style="padding: 40px; color: #e11d48; font-family: sans-serif; text-align: center;">
                    <h2 style="font-weight: 800; font-size: 24px; margin-bottom: 10px;">Dashboard Load Error</h2>
                    <p style="color: #64748b;">${err.message}</p>
                </div>`;
            }
        }
    </script>
</body>
</html>
