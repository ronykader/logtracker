@extends('logtracker::layout')

@section('title', __('logtracker::ui.audit_panel'))

@section('scripts')
    <script type="text/babel">
        const { useState, useEffect, useRef } = React;
        const { motion, AnimatePresence } = Motion;

        const i18n = {
            columns: {
                date: "{{ __('logtracker::ui.table_header_date') }}",
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
                allTables: "{{ __('logtracker::ui.all_tables') }}",
                allTypes: "{{ __('logtracker::ui.all_types') }}",
                refresh: "{{ __('logtracker::ui.filter_button_refresh') }}",
                reset: "{{ __('logtracker::ui.filter_button_reset') }}"
            },
            details: {
                title: "{{ __('logtracker::ui.details_title') }}",
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

        const SkeletonRow = () => (
            <tr>
                <td colSpan={5} className="px-6 py-4">
                    <div className="h-10 rounded-xl animate-shimmer"></div>
                </td>
            </tr>
        );

        const typeConfig = {
            create: { bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200', dot: 'bg-emerald-500' },
            edit:   { bg: 'bg-amber-50',   text: 'text-amber-700',   border: 'border-amber-200',   dot: 'bg-amber-500' },
            delete: { bg: 'bg-rose-50',    text: 'text-rose-700',    border: 'border-rose-200',    dot: 'bg-rose-500' },
        };

        const getTypeConfig = (type) => typeConfig[type?.toLowerCase()] || { bg: 'bg-slate-50', text: 'text-slate-600', border: 'border-slate-200', dot: 'bg-slate-400' };

        function Carbon() {
            this.subDays = (days) => { const d = new Date(); d.setDate(d.getDate() - days); this.date = d; return this; };
            this.format = () => this.date.toISOString().split('T')[0];
        }

        function AuditTrail() {
            const [logs, setLogs] = useState([]);
            const [heatmapData, setHeatmapData] = useState({});
            const [filters, setFilters] = useState({ tables: [], users: [], types: [] });
            const [params, setParams] = useState({ table: '', type: '', search: '', start_date: '', end_date: '' });
            const [page, setPage] = useState(1);
            const [pageSize] = useState(10);
            const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
            const [selectedLog, setSelectedLog] = useState(null);
            const [isModalOpen, setIsModalOpen] = useState(false);
            const [loading, setLoading] = useState(true);
            const datePickerRef = useRef(null);

            useEffect(() => { loadLogs(); }, [page]);

            useEffect(() => {
                if (datePickerRef.current && window.flatpickr) {
                    flatpickr(datePickerRef.current, {
                        mode: 'range',
                        dateFormat: 'Y-m-d',
                        onChange: (dates) => {
                            if (dates.length === 2) {
                                setParams(prev => ({
                                    ...prev,
                                    start_date: dates[0].toISOString().split('T')[0],
                                    end_date: dates[1].toISOString().split('T')[0]
                                }));
                            }
                        }
                    });
                }
                if (window.lucide) window.lucide.createIcons();
            }, [loading]);

            const loadLogs = (overrideParams = {}) => {
                setLoading(true);
                const p = { ...params, ...overrideParams, page, per_page: pageSize };
                fetch("{{ url(config('logtracker.api_prefix', 'api/audit-panel-data')) }}?" + new URLSearchParams(p))
                    .then(r => r.json())
                    .then(json => {
                        setLogs(json.data || []);
                        setFilters(json.filters || { tables: [], users: [], types: [] });
                        setMeta(json.meta || meta);
                        // Build heatmap counts
                        const counts = {};
                        (json.data || []).forEach(l => { counts[l.log_date] = (counts[l.log_date] || 0) + 1; });
                        setHeatmapData(counts);
                        setLoading(false);
                    })
                    .catch(() => setLoading(false));
            };

            const executeSearch = (e) => { e.preventDefault(); setPage(1); loadLogs({ page: 1 }); };
            const resetFilters = () => {
                const d = { table: '', type: '', search: '', start_date: '', end_date: '' };
                setParams(d); setPage(1); loadLogs({ ...d, page: 1 });
                if (datePickerRef.current?._flatpickr) datePickerRef.current._flatpickr.clear();
            };

            const getUserName = (users) => {
                try { return (typeof users === 'string' ? JSON.parse(users) : users)?.name || 'Unknown'; }
                catch { return 'Unknown'; }
            };

            return (
                <SidebarLayout activePage="audit">
                    {/* Heatmap */}
                    <div className="mb-6">
                        <p className="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3">30-Day Activity</p>
                        <div className="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm">
                            <div className="flex gap-1.5">
                                {Array.from({length: 30}).map((_, i) => {
                                    const dateStr = new Carbon().subDays(29 - i).format();
                                    const count = heatmapData[dateStr] || 0;
                                    const intensity = count === 0 ? 0 : Math.min(0.2 + count * 0.15, 1);
                                    return (
                                        <motion.div
                                            key={i}
                                            whileHover=@{{ scale: 1.4 }}
                                            title={`${dateStr}: ${count} logs`}
                                            className="flex-1 h-10 rounded-lg cursor-help"
                                            style=@{{ backgroundColor: count > 0 ? `rgba(59, 130, 246, ${intensity})` : '#f8fafc', border: '1px solid #f1f5f9' }}
                                        />
                                    );
                                })}
                            </div>
                            <div className="flex justify-between mt-2 text-[10px] text-slate-300 font-bold">
                                <span>30 days ago</span><span>Today</span>
                            </div>
                        </div>
                    </div>

                    {/* Filters */}
                    <form onSubmit={executeSearch} className="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm mb-6">
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">{i18n.filters.search}</label>
                                <div className="relative">
                                    <input type="text" name="search" value={params.search}
                                        onChange={e => setParams(p => ({...p, search: e.target.value}))}
                                        placeholder={i18n.filters.searchPlaceholder}
                                        className="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-4 py-2.5 text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all"
                                    />
                                    <svg className="absolute left-3 top-3 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                </div>
                            </div>
                            <div>
                                <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">{i18n.filters.table}</label>
                                <select name="table" value={params.table}
                                    onChange={e => setParams(p => ({...p, table: e.target.value}))}
                                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all">
                                    <option value="">{i18n.filters.allTables}</option>
                                    {filters.tables.map(t => <option key={t} value={t}>{t}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">{i18n.filters.type}</label>
                                <select name="type" value={params.type}
                                    onChange={e => setParams(p => ({...p, type: e.target.value}))}
                                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all">
                                    <option value="">{i18n.filters.allTypes}</option>
                                    {filters.types.map(t => <option key={t} value={t}>{i18n.types[t?.toLowerCase()] || t}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Date Range</label>
                                <input ref={datePickerRef} type="text" readOnly placeholder="Select date range"
                                    className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-700 font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all cursor-pointer"
                                />
                            </div>
                        </div>
                        <div className="flex justify-end gap-2 mt-4 pt-4 border-t border-slate-100">
                            <button type="button" onClick={resetFilters}
                                className="px-5 py-2 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-all">
                                {i18n.filters.reset}
                            </button>
                            <button type="submit"
                                className="px-6 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-all shadow-sm">
                                {i18n.filters.refresh}
                            </button>
                        </div>
                    </form>

                    {/* Table */}
                    <div className="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left">
                                <thead>
                                    <tr className="border-b border-slate-100 bg-slate-50">
                                        <th className="px-6 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">{i18n.columns.date}</th>
                                        <th className="px-6 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">{i18n.columns.user}</th>
                                        <th className="px-6 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">{i18n.columns.table}</th>
                                        <th className="px-6 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">{i18n.columns.type}</th>
                                        <th className="px-6 py-3.5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{i18n.columns.action}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-50">
                                    {loading
                                        ? Array.from({length: 5}).map((_, i) => <SkeletonRow key={i} />)
                                        : logs.length === 0
                                            ? <tr><td colSpan={5} className="py-16 text-center text-slate-400 text-sm font-semibold">{i18n.empty}</td></tr>
                                            : logs.map(log => {
                                                const tc = getTypeConfig(log.log_type);
                                                return (
                                                    <tr key={log.id}
                                                        onClick={() => { setSelectedLog(log); setIsModalOpen(true); }}
                                                        className="hover:bg-blue-50/30 cursor-pointer transition-colors group">
                                                        <td className="px-6 py-4">
                                                            <p className="text-sm font-bold text-slate-700">{log.log_date}</p>
                                                            <p className="text-[10px] text-slate-400 font-semibold mt-0.5">{log.log_time}</p>
                                                        </td>
                                                        <td className="px-6 py-4">
                                                            <div className="flex items-center gap-3">
                                                                <div className="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                                                    {getUserName(log.users).charAt(0).toUpperCase()}
                                                                </div>
                                                                <div>
                                                                    <p className="text-sm font-bold text-slate-700 truncate max-w-[140px]">{getUserName(log.users)}</p>
                                                                    <p className="text-[10px] text-slate-400 font-semibold">#ID {log.user_id}</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4">
                                                            <code className="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-mono font-bold">{log.table_name}</code>
                                                        </td>
                                                        <td className="px-6 py-4">
                                                            <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ${tc.bg} ${tc.text} ${tc.border}`}>
                                                                <span className={`w-1.5 h-1.5 rounded-full ${tc.dot}`}></span>
                                                                {i18n.types[log.log_type?.toLowerCase()] || log.log_type}
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 text-right">
                                                            <button className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 bg-slate-50 hover:bg-blue-50 hover:text-blue-600 border border-slate-200 hover:border-blue-200 transition-all group-hover:shadow-sm">
                                                                <i data-lucide="eye" className="w-3.5 h-3.5"></i>
                                                                View
                                                            </button>
                                                        </td>
                                                    </tr>
                                                );
                                            })
                                    }
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        <div className="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                            <p className="text-xs font-bold text-slate-400 uppercase tracking-widest">{meta.total} Total Records</p>
                            <div className="flex items-center gap-1.5">
                                <button disabled={page === 1} onClick={() => setPage(p => p - 1)}
                                    className="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:bg-white hover:shadow-sm disabled:opacity-30 transition-all border border-transparent hover:border-slate-200">
                                    <i data-lucide="chevron-left" className="w-4 h-4"></i>
                                </button>
                                <span className="px-4 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-black">
                                    {page} / {meta.last_page}
                                </span>
                                <button disabled={page >= meta.last_page} onClick={() => setPage(p => p + 1)}
                                    className="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:bg-white hover:shadow-sm disabled:opacity-30 transition-all border border-transparent hover:border-slate-200">
                                    <i data-lucide="chevron-right" className="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Detail Modal */}
                    <AnimatePresence>
                        {isModalOpen && selectedLog && (
                            <div className="fixed inset-0 z-[200] flex items-center justify-center p-4">
                                <motion.div
                                    initial=@{{ opacity: 0 }} animate=@{{ opacity: 1 }} exit=@{{ opacity: 0 }}
                                    onClick={() => setIsModalOpen(false)}
                                    className="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                                />
                                <motion.div
                                    initial=@{{ scale: 0.95, opacity: 0, y: 16 }}
                                    animate=@{{ scale: 1, opacity: 1, y: 0 }}
                                    exit=@{{ scale: 0.95, opacity: 0, y: 16 }}
                                    className="relative z-10 bg-white rounded-3xl w-full max-w-3xl max-h-[88vh] overflow-hidden shadow-2xl flex flex-col"
                                >
                                    {/* Modal Header */}
                                    <div className="flex items-center justify-between px-7 py-5 border-b border-slate-100">
                                        <div className="flex items-center gap-4">
                                            <div className={`w-10 h-10 rounded-2xl flex items-center justify-center ${getTypeConfig(selectedLog.log_type).bg} ${getTypeConfig(selectedLog.log_type).text}`}>
                                                <i data-lucide="shield-check" className="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <h3 className="text-base font-black text-slate-800">{i18n.details.title}</h3>
                                                <p className="text-[11px] text-slate-400 font-bold">#{selectedLog.id} &bull; {selectedLog.log_date} {selectedLog.log_time}</p>
                                            </div>
                                        </div>
                                        <button onClick={() => setIsModalOpen(false)}
                                            className="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-all">
                                            <i data-lucide="x" className="w-5 h-5"></i>
                                        </button>
                                    </div>

                                    {/* Modal Body */}
                                    <div className="overflow-y-auto flex-1 p-7 space-y-6">
                                        <div className="grid grid-cols-2 gap-3">
                                            {[
                                                { label: i18n.details.url, value: selectedLog.url, color: 'blue' },
                                                { label: i18n.details.route, value: selectedLog.route_name, color: 'violet' },
                                                { label: 'IP Address', value: selectedLog.ip_address, color: 'slate' },
                                                { label: 'User Agent', value: selectedLog.user_agent, color: 'slate' },
                                            ].map(item => (
                                                <div key={item.label} className={`p-4 rounded-xl border ${
                                                    item.color === 'blue' ? 'bg-blue-50 border-blue-100' :
                                                    item.color === 'violet' ? 'bg-violet-50 border-violet-100' :
                                                    'bg-slate-50 border-slate-100'
                                                }`}>
                                                    <p className={`text-[10px] font-black uppercase tracking-widest mb-1 ${
                                                        item.color === 'blue' ? 'text-blue-500' :
                                                        item.color === 'violet' ? 'text-violet-500' : 'text-slate-400'
                                                    }`}>{item.label}</p>
                                                    <p className={`text-xs font-bold truncate ${
                                                        item.color === 'blue' ? 'text-blue-800' :
                                                        item.color === 'violet' ? 'text-violet-800' : 'text-slate-600'
                                                    }`} title={item.value || 'N/A'}>{item.value || 'N/A'}</p>
                                                </div>
                                            ))}
                                        </div>

                                        <div className="rounded-2xl border border-slate-100 overflow-hidden">
                                            <table className="w-full text-sm">
                                                <thead>
                                                    <tr className="bg-slate-50 border-b border-slate-100">
                                                        <th className="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{i18n.details.field}</th>
                                                        <th className="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{i18n.details.old}</th>
                                                        <th className="px-5 py-3 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">{i18n.details.new}</th>
                                                    </tr>
                                                </thead>
                                                <tbody className="divide-y divide-slate-50">
                                                    {Object.keys(typeof selectedLog.new_data === 'string' ? JSON.parse(selectedLog.new_data) : (selectedLog.new_data || {})).map(key => {
                                                        const oldData = typeof selectedLog.data === 'string' ? JSON.parse(selectedLog.data) : (selectedLog.data || {});
                                                        const newData = typeof selectedLog.new_data === 'string' ? JSON.parse(selectedLog.new_data) : (selectedLog.new_data || {});
                                                        const changed = String(oldData[key]) !== String(newData[key]);
                                                        return (
                                                            <tr key={key} className={changed ? 'bg-amber-50/30' : ''}>
                                                                <td className="px-5 py-3 font-bold text-slate-500 text-xs whitespace-nowrap">{key}</td>
                                                                <td className="px-5 py-3 font-mono text-xs text-rose-600 max-w-[200px] truncate">{String(oldData[key] ?? '-')}</td>
                                                                <td className="px-5 py-3 font-mono text-xs text-emerald-600 font-bold max-w-[200px] truncate">{String(newData[key] ?? '-')}</td>
                                                            </tr>
                                                        );
                                                    })}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </motion.div>
                            </div>
                        )}
                    </AnimatePresence>
                </SidebarLayout>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('logtracker-root'));
        root.render(<AuditTrail />);
    </script>
@endsection
