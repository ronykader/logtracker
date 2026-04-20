@extends('logtracker::layout')

@section('title', 'System Logs')

@section('styles')
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .mono { font-family: 'JetBrains Mono', 'Courier New', monospace; }
        .stack-pre { white-space: pre-wrap; word-break: break-word; }
    </style>
@endsection

@section('scripts')
    <script type="text/babel">
        const { useState, useEffect } = React;
        const { motion, AnimatePresence } = Motion;

        const LEVELS = {
            ERROR:     { bg: 'bg-rose-50',   text: 'text-rose-700',   border: 'border-rose-200',   bar: '#f43f5e', icon: 'alert-octagon'   },
            CRITICAL:  { bg: 'bg-red-100',   text: 'text-red-800',    border: 'border-red-300',    bar: '#ef4444', icon: 'alert-octagon'   },
            WARNING:   { bg: 'bg-amber-50',  text: 'text-amber-700',  border: 'border-amber-200',  bar: '#f59e0b', icon: 'alert-triangle'  },
            NOTICE:    { bg: 'bg-blue-50',   text: 'text-blue-700',   border: 'border-blue-200',   bar: '#3b82f6', icon: 'info'            },
            INFO:      { bg: 'bg-sky-50',    text: 'text-sky-700',    border: 'border-sky-200',    bar: '#0ea5e9', icon: 'info'            },
            DEBUG:     { bg: 'bg-slate-50',  text: 'text-slate-600',  border: 'border-slate-200',  bar: '#94a3b8', icon: 'bug'             },
            EMERGENCY: { bg: 'bg-purple-100',text: 'text-purple-800', border: 'border-purple-300', bar: '#7c3aed', icon: 'zap'             },
        };

        const getLevel = (l) => LEVELS[l] || LEVELS['DEBUG'];

        function SystemLogPage() {
            const [logs, setLogs] = useState([]);
            const [loading, setLoading] = useState(true);
            const [expandedIndex, setExpandedIndex] = useState(null);
            const [levelFilter, setLevelFilter] = useState('ALL');
            const [search, setSearch] = useState('');
            const [error, setError] = useState(null);

            useEffect(() => { fetchLogs(); }, []);

            useEffect(() => {
                if (window.lucide) window.lucide.createIcons();
            }, [loading, expandedIndex]);

            const fetchLogs = () => {
                setLoading(true); setError(null);
                fetch("{{ route('logtracker.system-log-data') }}")
                    .then(r => r.json())
                    .then(json => { setLogs(json.data || []); setLoading(false); })
                    .catch(() => { setError('Could not load log file. Please check server permissions.'); setLoading(false); });
            };

            const filteredLogs = logs.filter(log => {
                const matchLevel = levelFilter === 'ALL' || log.level === levelFilter;
                const q = search.toLowerCase();
                const matchSearch = !q || log.message.toLowerCase().includes(q) || (log.stack || '').toLowerCase().includes(q);
                return matchLevel && matchSearch;
            });

            const levelCounts = logs.reduce((acc, l) => { acc[l.level] = (acc[l.level] || 0) + 1; return acc; }, {});

            return (
                <SidebarLayout activePage="system">
                    {/* Toolbar */}
                    <div className="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm mb-5 flex flex-wrap items-center gap-3 justify-between">
                        <div className="flex items-center gap-3">
                            <div className="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center">
                                <i data-lucide="terminal" className="w-4 h-4 text-emerald-400"></i>
                            </div>
                            <div>
                                <p className="text-sm font-black text-slate-800">storage/logs/laravel.log</p>
                                <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Last {logs.length} entries &bull; Newest first</p>
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="relative">
                                <input type="text" value={search} onChange={e => setSearch(e.target.value)}
                                    placeholder="Search logs..."
                                    className="w-52 bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all"
                                />
                                <svg className="absolute left-3 top-2.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </div>
                            <select value={levelFilter} onChange={e => setLevelFilter(e.target.value)}
                                className="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all">
                                <option value="ALL">All Levels ({logs.length})</option>
                                {Object.entries(levelCounts).sort().map(([l, c]) => (
                                    <option key={l} value={l}>{l} ({c})</option>
                                ))}
                            </select>
                            <button onClick={fetchLogs}
                                className="w-9 h-9 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all">
                                <i data-lucide="refresh-cw" className="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    {/* Error State */}
                    {error && (
                        <div className="bg-rose-50 border border-rose-200 rounded-2xl p-5 mb-5 flex items-start gap-3">
                            <i data-lucide="alert-triangle" className="w-5 h-5 text-rose-500 shrink-0 mt-0.5"></i>
                            <p className="text-sm font-bold text-rose-700">{error}</p>
                        </div>
                    )}

                    {/* Log List */}
                    <div className="space-y-2">
                        {loading ? (
                            Array.from({length: 6}).map((_, i) => (
                                <div key={i} className="bg-white border border-slate-100 rounded-2xl p-5 animate-pulse">
                                    <div className="flex gap-3">
                                        <div className="w-14 h-14 rounded-xl bg-slate-100"></div>
                                        <div className="flex-1 space-y-2 pt-1">
                                            <div className="h-3 bg-slate-100 rounded w-1/4"></div>
                                            <div className="h-4 bg-slate-100 rounded w-3/4"></div>
                                        </div>
                                    </div>
                                </div>
                            ))
                        ) : filteredLogs.length === 0 ? (
                            <div className="bg-white border border-slate-100 rounded-2xl py-16 text-center">
                                <div className="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
                                    <i data-lucide="search-x" className="w-6 h-6 text-slate-300"></i>
                                </div>
                                <p className="text-sm font-bold text-slate-400">No log entries match your filters</p>
                            </div>
                        ) : filteredLogs.map((log, index) => {
                            const lc = getLevel(log.level);
                            const isOpen = expandedIndex === index;
                            return (
                                <div key={index}
                                    className={`bg-white border rounded-2xl overflow-hidden transition-all duration-200 ${isOpen ? 'border-blue-200 shadow-md shadow-blue-50' : 'border-slate-100 hover:border-slate-200 hover:shadow-sm'}`}
                                    style=@{{ borderLeftWidth: '4px', borderLeftColor: lc.bar }}
                                >
                                    <button
                                        onClick={() => setExpandedIndex(isOpen ? null : index)}
                                        className="w-full flex items-start gap-4 p-4 text-left hover:bg-slate-50/50 transition-colors"
                                    >
                                        {/* Level Badge */}
                                        <div className={`shrink-0 px-3 py-2 rounded-xl border ${lc.bg} ${lc.border} flex flex-col items-center justify-center min-w-[64px]`}>
                                            <i data-lucide={lc.icon} className={`w-4 h-4 ${lc.text} mb-1`}></i>
                                            <span className={`text-[9px] font-black uppercase tracking-tight ${lc.text}`}>{log.level}</span>
                                        </div>

                                        {/* Content */}
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center gap-2 mb-1 flex-wrap">
                                                <span className="text-[10px] font-black text-slate-400 mono">{log.timestamp}</span>
                                                <span className="px-2 py-0.5 rounded-lg bg-slate-100 text-[10px] font-bold text-slate-500 uppercase">{log.env}</span>
                                            </div>
                                            <p className="text-sm font-semibold text-slate-700 truncate mono">{log.message}</p>
                                        </div>

                                        {/* Chevron */}
                                        <div className={`shrink-0 w-6 h-6 rounded-lg flex items-center justify-center transition-colors ${isOpen ? 'bg-blue-100 text-blue-600' : 'text-slate-300'}`}>
                                            <i data-lucide={isOpen ? 'chevron-up' : 'chevron-down'} className="w-4 h-4"></i>
                                        </div>
                                    </button>

                                    <AnimatePresence>
                                        {isOpen && (
                                            <motion.div
                                                initial=@{{ height: 0, opacity: 0 }}
                                                animate=@{{ height: 'auto', opacity: 1 }}
                                                exit=@{{ height: 0, opacity: 0 }}
                                                transition=@{{ duration: 0.2 }}
                                                style=@{{ overflow: 'hidden' }}
                                            >
                                                <div className="border-t border-slate-100 p-5 space-y-4 bg-slate-50/30">
                                                    <div>
                                                        <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Full Message</p>
                                                        <div className="bg-white border border-slate-100 rounded-xl p-4 text-sm text-slate-700 font-medium mono">
                                                            {log.message}
                                                        </div>
                                                    </div>
                                                    {log.stack && log.stack.trim() && (
                                                        <div>
                                                            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Stack Trace</p>
                                                            <div className="bg-slate-900 rounded-xl p-5 overflow-x-auto">
                                                                <pre className="text-[11px] text-slate-300 mono stack-pre leading-relaxed">{log.stack.trim()}</pre>
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>
                                            </motion.div>
                                        )}
                                    </AnimatePresence>
                                </div>
                            );
                        })}
                    </div>
                </SidebarLayout>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('logtracker-root'));
        root.render(<SystemLogPage />);
    </script>
@endsection
