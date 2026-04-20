import React, { useState, useEffect, useRef } from 'react';
import { motion } from 'framer-motion';
import { Activity, Database, UserCheck, ShieldCheck } from 'lucide-react';
import ReactApexChart from 'react-apexcharts';
import { routes, get, buildUrl } from '../utils/api';

const CHART_FONT = "'Outfit', sans-serif";
const CHART_COLORS = ['#6366f1', '#8b5cf6', '#a78bfa', '#c4b5fd', '#ddd6fe'];

function StatCard({ label, value, Icon, color, loading, pulse }) {
    const colors = {
        indigo:  { bg: '#eef2ff', text: '#4f46e5', icon: '#6366f1' },
        violet:  { bg: '#f5f3ff', text: '#6d28d9', icon: '#7c3aed' },
        purple:  { bg: '#faf5ff', text: '#7c3aed', icon: '#9333ea' },
        emerald: { bg: '#ecfdf5', text: '#065f46', icon: '#10b981' },
    };
    const c = colors[color] || colors.indigo;

    return (
        <motion.div
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            className="lt-card"
            style={{ padding: 20 }}>
            <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 14 }}>
                <div style={{ width: 38, height: 38, borderRadius: 11, background: c.bg, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <Icon size={18} color={c.icon} />
                </div>
                {pulse && (
                    <span style={{ display: 'flex', alignItems: 'center', gap: 5, fontSize: 10, fontWeight: 800, color: '#10b981' }}>
                        <span style={{ width: 7, height: 7, borderRadius: '50%', background: '#10b981', display: 'inline-block', animation: 'pulse 1.5s infinite' }}></span>
                        Live
                    </span>
                )}
            </div>
            {loading
                ? <div className="shimmer" style={{ height: 28, width: '60%', marginBottom: 6 }}></div>
                : <p style={{ fontSize: 22, fontWeight: 900, color: c.text, letterSpacing: '-0.03em' }}>{value}</p>
            }
            <p style={{ fontSize: 10, color: '#94a3b8', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em', marginTop: 4 }}>{label}</p>
        </motion.div>
    );
}

export default function Insights() {
    const r = routes();
    const [data, setData] = useState({ top_tables: [], top_users: [], activity: [] });
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        get(buildUrl(`${r.api}/insights`))
            .then(json => { setData(json); setLoading(false); })
            .catch(() => setLoading(false));
    }, []);

    const total = data.activity.reduce((s, a) => s + (a.count || 0), 0);

    const donutOpts = {
        chart: { type: 'donut', fontFamily: CHART_FONT, toolbar: { show: false } },
        colors: CHART_COLORS,
        labels: data.top_tables.map(t => t.table_name),
        plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'Tables', fontSize: '12px', fontWeight: 700, color: '#94a3b8' } } } } },
        stroke: { width: 0 },
        legend: { position: 'bottom', fontSize: '12px', fontWeight: 600, fontFamily: CHART_FONT },
        dataLabels: { enabled: false },
    };

    const barOpts = {
        chart: { type: 'bar', fontFamily: CHART_FONT, toolbar: { show: false } },
        colors: ['#6366f1'],
        plotOptions: { bar: { borderRadius: 7, columnWidth: '45%' } },
        xaxis: {
            categories: data.top_users.map(u => u.name),
            labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#64748b', fontFamily: CHART_FONT } },
        },
        yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#64748b', fontFamily: CHART_FONT } } },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        dataLabels: { enabled: false },
        fill: { type: 'gradient', gradient: { shade: 'light', type: 'vertical', shadeIntensity: 0.3, inverseColors: false, opacityFrom: 1, opacityTo: 0.75 } },
    };

    const areaOpts = {
        chart: { type: 'area', fontFamily: CHART_FONT, toolbar: { show: false }, sparkline: { enabled: false } },
        stroke: { curve: 'smooth', width: 2.5, colors: ['#6366f1'] },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.01, stops: [0, 100] } },
        colors: ['#6366f1'],
        xaxis: {
            categories: data.activity.map(a => a.date),
            labels: { rotate: -30, style: { fontSize: '10px', fontWeight: 600, colors: '#64748b', fontFamily: CHART_FONT } },
        },
        yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#64748b', fontFamily: CHART_FONT } } },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        dataLabels: { enabled: false },
        markers: { size: 4, colors: ['#6366f1'], strokeColors: '#fff', strokeWidth: 2 },
        tooltip: { style: { fontFamily: CHART_FONT } },
    };

    const shim = (h) => <div className="shimmer" style={{ height: h, borderRadius: 10 }}></div>;

    return (
        <>
            {/* Stat Cards */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 16, marginBottom: 24 }}>
                <StatCard loading={loading} label="Total Events" value={total.toLocaleString()} Icon={Activity} color="indigo" />
                <StatCard loading={loading} label="Most Active Table" value={data.top_tables[0]?.table_name || 'N/A'} Icon={Database} color="violet" />
                <StatCard loading={loading} label="Top Operator" value={data.top_users[0]?.name || 'N/A'} Icon={UserCheck} color="purple" />
                <StatCard loading={loading} label="System Status" value="Live" Icon={ShieldCheck} color="emerald" pulse={true} />
            </div>

            {/* Charts Row */}
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 18, marginBottom: 18 }}>
                <div className="lt-card" style={{ padding: 22 }}>
                    <p style={{ fontSize: 10, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 16 }}>Table Distribution</p>
                    {loading || !data.top_tables.length
                        ? shim(280)
                        : <ReactApexChart type="donut" series={data.top_tables.map(t => t.count)} options={donutOpts} height={280} />
                    }
                </div>
                <div className="lt-card" style={{ padding: 22 }}>
                    <p style={{ fontSize: 10, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 16 }}>Active Operators</p>
                    {loading || !data.top_users.length
                        ? shim(280)
                        : <ReactApexChart type="bar" series={[{ name: 'Actions', data: data.top_users.map(u => u.count) }]} options={barOpts} height={280} />
                    }
                </div>
            </div>

            {/* Timeline */}
            <div className="lt-card" style={{ padding: 22 }}>
                <p style={{ fontSize: 10, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 16 }}>Activity Timeline — Last 14 Days</p>
                {loading || !data.activity.length
                    ? shim(300)
                    : <ReactApexChart type="area" series={[{ name: 'Log Events', data: data.activity.map(a => a.count) }]} options={areaOpts} height={300} />
                }
            </div>
        </>
    );
}
