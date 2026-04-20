@extends('logtracker::layout')

@section('title', 'Insights & Analytics')

@section('scripts')
    <script type="text/babel">
        const { useState, useEffect, useRef } = React;
        const { motion } = Motion;

        function InsightsPage() {
            const [insights, setInsights] = useState({ top_tables: [], top_users: [], activity: [] });
            const [loading, setLoading] = useState(true);
            const chartsRef = useRef({});

            useEffect(() => { loadInsights(); }, []);

            const loadInsights = () => {
                setLoading(true);
                fetch("{{ url(config('logtracker.api_prefix', 'api/audit-panel-data')) }}/insights")
                    .then(r => r.json())
                    .then(json => {
                        setInsights(json);
                        setLoading(false);
                        setTimeout(() => renderCharts(json), 150);
                    })
                    .catch(() => setLoading(false));
            };

            const renderCharts = (data) => {
                const baseOptions = {
                    chart: { fontFamily: 'Outfit, sans-serif', toolbar: { show: false } },
                };

                if (chartsRef.current.tables) { chartsRef.current.tables.destroy(); }
                if (data.top_tables?.length > 0) {
                    chartsRef.current.tables = new ApexCharts(document.querySelector("#tableChart"), {
                        ...baseOptions,
                        series: data.top_tables.map(t => t.count),
                        labels: data.top_tables.map(t => t.table_name),
                        chart: { ...baseOptions.chart, type: 'donut', height: 300 },
                        colors: ['#3b82f6', '#6366f1', '#8b5cf6', '#a78bfa', '#c4b5fd'],
                        plotOptions: { pie: { donut: { size: '72%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '12px', fontWeight: 700, color: '#94a3b8' } } } } },
                        stroke: { width: 0 },
                        legend: { position: 'bottom', fontSize: '12px', fontWeight: 600 },
                        dataLabels: { enabled: false }
                    });
                    chartsRef.current.tables.render();
                }

                if (chartsRef.current.users) { chartsRef.current.users.destroy(); }
                if (data.top_users?.length > 0) {
                    chartsRef.current.users = new ApexCharts(document.querySelector("#userChart"), {
                        ...baseOptions,
                        series: [{ name: 'Actions', data: data.top_users.map(u => u.count) }],
                        chart: { ...baseOptions.chart, type: 'bar', height: 300 },
                        colors: ['#3b82f6'],
                        plotOptions: { bar: { borderRadius: 8, columnWidth: '45%' } },
                        xaxis: { categories: data.top_users.map(u => u.name), labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#94a3b8' } } },
                        yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#94a3b8' } } },
                        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                        dataLabels: { enabled: false }
                    });
                    chartsRef.current.users.render();
                }

                if (chartsRef.current.timeline) { chartsRef.current.timeline.destroy(); }
                if (data.activity?.length > 0) {
                    chartsRef.current.timeline = new ApexCharts(document.querySelector("#timelineChart"), {
                        ...baseOptions,
                        series: [{ name: 'Log Events', data: data.activity.map(a => a.count) }],
                        chart: { ...baseOptions.chart, type: 'area', height: 320 },
                        stroke: { curve: 'smooth', width: 2.5, colors: ['#3b82f6'] },
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.02, stops: [0, 100], colorStops: [{ offset: 0, color: '#3b82f6', opacity: 0.3 }, { offset: 100, color: '#3b82f6', opacity: 0 }] } },
                        xaxis: { categories: data.activity.map(a => a.date), labels: { rotate: -30, style: { fontSize: '10px', fontWeight: 600, colors: '#94a3b8' } } },
                        yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#94a3b8' } } },
                        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                        colors: ['#3b82f6'],
                        dataLabels: { enabled: false },
                        markers: { size: 4, colors: ['#3b82f6'], strokeColors: '#fff', strokeWidth: 2 }
                    });
                    chartsRef.current.timeline.render();
                }

                if (window.lucide) window.lucide.createIcons();
            };

            const totalActivity = insights.activity.reduce((a, b) => a + (b.count || 0), 0);

            const statCards = [
                { label: 'Total Events', value: loading ? '—' : totalActivity, icon: 'activity', color: 'blue' },
                { label: 'Most Active Table', value: loading ? '—' : (insights.top_tables[0]?.table_name || 'N/A'), icon: 'database', color: 'indigo' },
                { label: 'Top Operator', value: loading ? '—' : (insights.top_users[0]?.name || 'N/A'), icon: 'user-check', color: 'violet' },
                { label: 'System Status', value: 'Live', icon: 'shield-check', color: 'emerald', pulse: true },
            ];

            const colorMap = {
                blue:    { bg: 'bg-blue-50',    text: 'text-blue-600',   border: 'border-blue-100',   icon: 'bg-blue-100'   },
                indigo:  { bg: 'bg-indigo-50',  text: 'text-indigo-600', border: 'border-indigo-100', icon: 'bg-indigo-100' },
                violet:  { bg: 'bg-violet-50',  text: 'text-violet-600', border: 'border-violet-100', icon: 'bg-violet-100' },
                emerald: { bg: 'bg-emerald-50', text: 'text-emerald-600',border: 'border-emerald-100',icon: 'bg-emerald-100'},
            };

            return (
                <SidebarLayout activePage="insights">
                    {/* Stat Cards */}
                    <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        {statCards.map((card, i) => {
                            const c = colorMap[card.color];
                            return (
                                <motion.div key={i}
                                    initial=@{{ opacity: 0, y: 16 }}
                                    animate=@{{ opacity: 1, y: 0 }}
                                    transition=@{{ delay: i * 0.07 }}
                                    className={`bg-white border ${c.border} rounded-2xl p-5 shadow-sm`}>
                                    <div className="flex items-start justify-between mb-3">
                                        <div className={`w-9 h-9 rounded-xl ${c.icon} ${c.text} flex items-center justify-center`}>
                                            <i data-lucide={card.icon} className="w-4 h-4"></i>
                                        </div>
                                        {card.pulse && <span className="flex items-center gap-1 text-[10px] font-black text-emerald-600 uppercase">
                                            <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>Active
                                        </span>}
                                    </div>
                                    <p className={`text-xl font-black ${c.text} truncate`}>{card.value}</p>
                                    <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{card.label}</p>
                                </motion.div>
                            );
                        })}
                    </div>

                    {/* Chart Grid */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
                        <div className="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                            <h3 className="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-5 flex items-center gap-2">
                                <i data-lucide="database" className="w-4 h-4 text-blue-500"></i> Table Distribution
                            </h3>
                            {loading ? <div className="h-64 animate-shimmer rounded-xl"></div> : <div id="tableChart"></div>}
                        </div>
                        <div className="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                            <h3 className="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-5 flex items-center gap-2">
                                <i data-lucide="users" className="w-4 h-4 text-indigo-500"></i> Active Operators
                            </h3>
                            {loading ? <div className="h-64 animate-shimmer rounded-xl"></div> : <div id="userChart"></div>}
                        </div>
                    </div>

                    <div className="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
                        <h3 className="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-5 flex items-center gap-2">
                            <i data-lucide="trending-up" className="w-4 h-4 text-violet-500"></i> Activity Timeline (Last 14 Days)
                        </h3>
                        {loading ? <div className="h-72 animate-shimmer rounded-xl"></div> : <div id="timelineChart"></div>}
                    </div>
                </SidebarLayout>
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('logtracker-root'));
        root.render(<InsightsPage />);
    </script>
@endsection
