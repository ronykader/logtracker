import React, { useState, useEffect, useRef, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Eye, X, ShieldCheck, RefreshCw, RotateCcw, Calendar, Globe, Cpu, Link, Navigation2, Maximize2, Minimize2 } from 'lucide-react';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { routes, i18n, get, buildUrl, parseUsers, parseJson } from '../utils/api';

// ── Type config ──────────────────────────────────────────────────────────────
const TYPE = {
    create: { bg: '#ecfdf5', color: '#065f46', border: '#6ee7b7', dot: '#10b981', label: 'Created', gradient: 'from-emerald-500 to-teal-600' },
    edit:   { bg: '#fffbeb', color: '#92400e', border: '#fcd34d', dot: '#f59e0b', label: 'Edited',  gradient: 'from-amber-400 to-orange-500' },
    delete: { bg: '#fff1f2', color: '#9f1239', border: '#fda4af', dot: '#f43f5e', label: 'Deleted', gradient: 'from-rose-500 to-pink-600'  },
};
const getType = (t) => TYPE[t?.toLowerCase()] || { bg: '#f8fafc', color: '#475569', border: '#e2e8f0', dot: '#94a3b8', label: t || 'Log', gradient: 'from-slate-400 to-slate-600' };

// ── Skeleton ─────────────────────────────────────────────────────────────────
const SkeletonRow = () => (
    <tr>
        <td colSpan={5} style={{ padding: '8px 20px' }}>
            <div className="shimmer" style={{ height: 52, borderRadius: 10 }}></div>
        </td>
    </tr>
);

// ── Heatmap ──────────────────────────────────────────────────────────────────
function HeatmapSection({ apiBase }) {
    const [data, setData] = useState({});
    useEffect(() => {
        get(buildUrl(`${apiBase}/insights`))
            .then(json => {
                const counts = {};
                (json.activity || []).forEach(a => { counts[a.date] = a.count; });
                setData(counts);
            }).catch(() => {});
    }, []);

    const days = Array.from({ length: 30 }, (_, i) => {
        const d = new Date();
        d.setDate(d.getDate() - (29 - i));
        return d.toISOString().split('T')[0];
    });
    const max = Math.max(1, ...Object.values(data));

    return (
        <div className="lt-card" style={{ padding: '16px 20px', marginBottom: 16 }}>
            <p style={{ fontSize: 10, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.12em', marginBottom: 10 }}>30-Day Activity</p>
            <div style={{ display: 'flex', gap: 4 }}>
                {days.map(date => {
                    const count = data[date] || 0;
                    const intensity = count === 0 ? 0 : 0.12 + (count / max) * 0.88;
                    return (
                        <motion.div key={date} whileHover={{ scale: 1.6, zIndex: 10 }}
                            title={`${date}: ${count} log${count !== 1 ? 's' : ''}`}
                            style={{ flex: 1, height: 32, borderRadius: 5, cursor: 'pointer', background: count === 0 ? '#f1f5f9' : `rgba(99,102,241,${intensity})` }}
                        />
                    );
                })}
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 5 }}>
                <span style={{ fontSize: 9, color: '#cbd5e1', fontWeight: 700 }}>30 days ago</span>
                <span style={{ fontSize: 9, color: '#cbd5e1', fontWeight: 700 }}>Today</span>
            </div>
        </div>
    );
}

// ── Filter Bar ────────────────────────────────────────────────────────────────
function FilterBar({ filters, params, setParams, onSearch, onReset }) {
    const t = i18n();
    const dateRef = useRef(null);
    const fpRef   = useRef(null);

    useEffect(() => {
        if (!dateRef.current) return;
        fpRef.current = flatpickr(dateRef.current, {
            mode: 'range',
            dateFormat: 'Y-m-d',
            allowInput: false,
            disableMobile: true,
            onChange: (dates) => {
                if (dates.length === 2) {
                    setParams(p => ({
                        ...p,
                        start_date: dates[0].toISOString().split('T')[0],
                        end_date:   dates[1].toISOString().split('T')[0],
                    }));
                }
            },
            onReady: (_, __, fp) => {
                // Style the calendar input icon
                fp.calendarContainer?.classList.add('lt-flatpickr');
            },
        });
        return () => { fpRef.current?.destroy(); fpRef.current = null; };
    }, []);

    const field = (style = {}) => ({
        width: '100%', padding: '9px 12px',
        background: '#f8fafc', border: '1px solid #e2e8f0',
        borderRadius: 10, fontSize: 13.5, fontWeight: 500,
        color: '#334155', fontFamily: 'Outfit, sans-serif', outline: 'none',
        transition: 'border-color 0.15s, box-shadow 0.15s',
        ...style,
    });
    const lbl = { display: 'block', fontSize: 10, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.12em', marginBottom: 5 };
    const onFocus = e => { e.target.style.borderColor = '#6366f1'; e.target.style.boxShadow = '0 0 0 3px rgba(99,102,241,0.1)'; };
    const onBlur  = e => { e.target.style.borderColor = '#e2e8f0'; e.target.style.boxShadow = 'none'; };

    return (
        <div className="lt-card" style={{ padding: '16px 20px', marginBottom: 16 }}>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 12 }}>
                {/* Search */}
                <div>
                    <label style={lbl}>{t.filter_search || 'Search'}</label>
                    <div style={{ position: 'relative' }}>
                        <input type="text" value={params.search}
                            onChange={e => setParams(p => ({ ...p, search: e.target.value }))}
                            onKeyDown={e => e.key === 'Enter' && onSearch()}
                            placeholder={t.filter_search_placeholder || 'Search...'}
                            style={{ ...field(), paddingLeft: 36 }}
                            onFocus={onFocus} onBlur={onBlur}
                        />
                        <svg style={{ position: 'absolute', left: 11, top: '50%', transform: 'translateY(-50%)', color: '#94a3b8', pointerEvents: 'none' }} width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                </div>

                {/* Table */}
                <div>
                    <label style={lbl}>{t.filter_table || 'Table'}</label>
                    <select value={params.table}
                        onChange={e => setParams(p => ({ ...p, table: e.target.value }))}
                        style={field({ cursor: 'pointer' })} onFocus={onFocus} onBlur={onBlur}>
                        <option value="">{t.all_tables || 'All Tables'}</option>
                        {filters.tables.map(t2 => <option key={t2} value={t2}>{t2}</option>)}
                    </select>
                </div>

                {/* Type */}
                <div>
                    <label style={lbl}>{t.filter_type || 'Type'}</label>
                    <select value={params.type}
                        onChange={e => setParams(p => ({ ...p, type: e.target.value }))}
                        style={field({ cursor: 'pointer' })} onFocus={onFocus} onBlur={onBlur}>
                        <option value="">{t.all_types || 'All Types'}</option>
                        {filters.types.map(v => <option key={v} value={v}>{t[`type_${v?.toLowerCase()}`] || v}</option>)}
                    </select>
                </div>

                {/* Date range — flatpickr */}
                <div>
                    <label style={lbl}>Date Range</label>
                    <div style={{ position: 'relative' }}>
                        <input
                            ref={dateRef}
                            type="text"
                            readOnly
                            placeholder="Pick a date range..."
                            style={{ ...field({ cursor: 'pointer', paddingLeft: 36 }) }}
                            onFocus={onFocus} onBlur={onBlur}
                        />
                        <Calendar size={14} style={{ position: 'absolute', left: 11, top: '50%', transform: 'translateY(-50%)', color: '#94a3b8', pointerEvents: 'none' }} />
                    </div>
                </div>
            </div>

            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 12, paddingTop: 12, borderTop: '1px solid #f1f5f9' }}>
                <button onClick={() => { onReset(); fpRef.current?.clear(); }}
                    style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '8px 16px', borderRadius: 9, border: '1px solid #e2e8f0', background: '#f8fafc', color: '#64748b', fontSize: 13, fontWeight: 700, cursor: 'pointer', fontFamily: 'Outfit, sans-serif' }}>
                    <RotateCcw size={13} /> {t.reset || 'Reset'}
                </button>
                <button onClick={onSearch}
                    style={{ display: 'inline-flex', alignItems: 'center', gap: 6, padding: '8px 20px', borderRadius: 9, border: 'none', background: '#6366f1', color: '#fff', fontSize: 13, fontWeight: 700, cursor: 'pointer', fontFamily: 'Outfit, sans-serif', boxShadow: '0 2px 10px rgba(99,102,241,0.3)' }}>
                    <RefreshCw size={13} /> {t.refresh || 'Search'}
                </button>
            </div>
        </div>
    );
}

// ── Detail Drawer ─────────────────────────────────────────────────────────────
function DetailDrawer({ log, onClose }) {
    const t   = i18n();
    const tc  = getType(log.log_type);
    const newData = parseJson(log.new_data);
    const oldData = parseJson(log.data);
    const keys = Object.keys(newData);
    const userName = parseUsers(log.users);
    const [isExpanded, setIsExpanded] = useState(false);

    // Close on Escape
    useEffect(() => {
        const handler = (e) => { if (e.key === 'Escape') onClose(); };
        window.addEventListener('keydown', handler);
        return () => window.removeEventListener('keydown', handler);
    }, []);

    const MetaItem = ({ Icon, label, value, mono }) => (
        <div style={{ display: 'flex', gap: 12, padding: '12px 0', borderBottom: '1px solid #f8fafc', alignItems: 'flex-start' }}>
            <div style={{ width: 32, height: 32, borderRadius: 9, background: '#f8fafc', border: '1px solid #f1f5f9', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, marginTop: 1 }}>
                <Icon size={14} color="#94a3b8" />
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
                <p style={{ fontSize: 10, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 3 }}>{label}</p>
                <p style={{ fontSize: 13, fontWeight: 600, color: '#334155', wordBreak: 'break-all', fontFamily: mono ? 'JetBrains Mono, monospace' : 'Outfit, sans-serif', lineHeight: 1.5 }}>
                    {value || <span style={{ color: '#cbd5e1' }}>N/A</span>}
                </p>
            </div>
        </div>
    );

    return (
        <AnimatePresence>
            <div style={{ position: 'fixed', inset: 0, zIndex: 1000, display: 'flex', justifyContent: 'flex-end' }}>
                {/* Backdrop */}
                <motion.div
                    initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                    onClick={onClose}
                    style={{ position: 'absolute', inset: 0, background: 'rgba(15,23,42,0.45)', backdropFilter: 'blur(3px)' }}
                />

                {/* Drawer */}
                <motion.div
                    initial={{ x: '100%' }}
                    animate={{ x: 0, width: isExpanded ? '100vw' : 520 }}
                    exit={{ x: '100%' }}
                    transition={{ type: 'spring', stiffness: 380, damping: 34 }}
                    style={{ position: 'relative', zIndex: 1, width: isExpanded ? '100vw' : 520, maxWidth: isExpanded ? '100vw' : '95vw', height: '100vh', background: '#fff', display: 'flex', flexDirection: 'column', boxShadow: '-20px 0 80px rgba(0,0,0,0.15)' }}
                >
                    {/* ── Drawer Header ── */}
                    <div style={{
                        background: `linear-gradient(135deg, #1e1b4b, #312e81)`,
                        padding: '28px 28px 24px',
                        position: 'relative',
                        overflow: 'hidden',
                    }}>
                        {/* Decor circle */}
                        <div style={{ position: 'absolute', top: -40, right: -40, width: 160, height: 160, borderRadius: '50%', background: 'rgba(255,255,255,0.04)', pointerEvents: 'none' }}></div>
                        <div style={{ position: 'absolute', bottom: -20, left: -20, width: 100, height: 100, borderRadius: '50%', background: 'rgba(255,255,255,0.04)', pointerEvents: 'none' }}></div>

                        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 20 }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                                <div style={{ width: 42, height: 42, borderRadius: 14, background: tc.bg, border: `2px solid ${tc.border}`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                    <ShieldCheck size={20} color={tc.color} />
                                </div>
                                <div>
                                    <p style={{ fontSize: 11, fontWeight: 700, color: 'rgba(165,180,252,0.8)', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 3 }}>Audit Detail</p>
                                    <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '3px 10px', borderRadius: 99, background: tc.bg, color: tc.color, border: `1px solid ${tc.border}`, fontSize: 11, fontWeight: 800 }}>
                                        <span style={{ width: 6, height: 6, borderRadius: '50%', background: tc.dot }}></span>
                                        {tc.label}
                                    </span>
                                </div>
                            </div>
                            {/* Action buttons */}
                            <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                <button
                                    onClick={() => setIsExpanded(e => !e)}
                                    title={isExpanded ? 'Collapse drawer' : 'Expand to full screen'}
                                    style={{ width: 32, height: 32, borderRadius: 9, border: '1px solid rgba(255,255,255,0.15)', background: isExpanded ? 'rgba(165,180,252,0.2)' : 'rgba(255,255,255,0.08)', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', color: isExpanded ? '#c7d2fe' : 'rgba(255,255,255,0.7)', transition: 'all 0.15s' }}
                                    onMouseEnter={e => { e.currentTarget.style.background = 'rgba(165,180,252,0.2)'; e.currentTarget.style.color = '#c7d2fe'; }}
                                    onMouseLeave={e => { e.currentTarget.style.background = isExpanded ? 'rgba(165,180,252,0.2)' : 'rgba(255,255,255,0.08)'; e.currentTarget.style.color = isExpanded ? '#c7d2fe' : 'rgba(255,255,255,0.7)'; }}
                                >
                                    {isExpanded ? <Minimize2 size={14} /> : <Maximize2 size={14} />}
                                </button>
                                <button onClick={onClose} style={{ width: 32, height: 32, borderRadius: 9, border: '1px solid rgba(255,255,255,0.15)', background: 'rgba(255,255,255,0.08)', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'rgba(255,255,255,0.7)', transition: 'all 0.15s' }}>
                                    <X size={15} />
                                </button>
                            </div>
                        </div>

                        {/* Quick stats */}
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: 10 }}>
                            {[
                                { label: 'Log ID', value: `#${log.id}` },
                                { label: 'Table', value: log.table_name },
                                { label: 'User', value: userName },
                            ].map(item => (
                                <div key={item.label} style={{ background: 'rgba(255,255,255,0.07)', borderRadius: 10, padding: '10px 12px', border: '1px solid rgba(255,255,255,0.1)' }}>
                                    <p style={{ fontSize: 9, color: 'rgba(165,180,252,0.7)', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 4 }}>{item.label}</p>
                                    <p style={{ fontSize: 13, fontWeight: 800, color: '#fff', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{item.value}</p>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* ── Drawer Body ── */}
                    <div style={{ flex: 1, overflowY: 'auto', padding: '0 28px 28px' }}>

                        {/* Timestamp + date */}
                        <div style={{ display: 'flex', gap: 10, padding: '16px 0', borderBottom: '1px solid #f8fafc' }}>
                            <div style={{ flex: 1, background: '#f8fafc', borderRadius: 10, padding: '10px 14px', border: '1px solid #f1f5f9' }}>
                                <p style={{ fontSize: 9, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 3 }}>Date</p>
                                <p style={{ fontSize: 14, fontWeight: 700, color: '#1e293b' }}>{log.log_date}</p>
                            </div>
                            <div style={{ flex: 1, background: '#f8fafc', borderRadius: 10, padding: '10px 14px', border: '1px solid #f1f5f9' }}>
                                <p style={{ fontSize: 9, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 3 }}>Time</p>
                                <p style={{ fontSize: 14, fontWeight: 700, color: '#1e293b', fontFamily: 'JetBrains Mono, monospace' }}>{log.log_time}</p>
                            </div>
                        </div>

                        {/* Meta items */}
                        <MetaItem Icon={Link}        label={t.details_url || 'Source URL'}   value={log.url}        mono />
                        <MetaItem Icon={Navigation2} label={t.details_route || 'Route Name'}  value={log.route_name} mono />
                        <MetaItem Icon={Globe} label="IP Address"                       value={log.ip_address} mono />
                        <MetaItem Icon={Cpu}   label="User Agent"                       value={log.user_agent} />

                        {/* Data diff */}
                        {keys.length > 0 && (
                            <div style={{ marginTop: 20 }}>
                                <p style={{ fontSize: 10, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.12em', marginBottom: 10 }}>
                                    Data Changes <span style={{ color: '#c7d2fe', fontWeight: 600, fontSize: 9 }}>— {keys.length} field{keys.length !== 1 ? 's' : ''}</span>
                                </p>
                                <div style={{ borderRadius: 14, border: '1px solid #e9eef5', overflow: 'hidden' }}>
                                    <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 12.5 }}>
                                        <thead>
                                            <tr style={{ background: '#fafafa', borderBottom: '1px solid #f1f5f9' }}>
                                                <th style={{ padding: '9px 14px', textAlign: 'left', fontSize: 10, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.1em', width: '30%' }}>Field</th>
                                                <th style={{ padding: '9px 14px', textAlign: 'left', fontSize: 10, fontWeight: 800, color: '#f43f5e', textTransform: 'uppercase', letterSpacing: '0.1em' }}>Before</th>
                                                <th style={{ padding: '9px 14px', textAlign: 'left', fontSize: 10, fontWeight: 800, color: '#059669', textTransform: 'uppercase', letterSpacing: '0.1em' }}>After</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {keys.map((key, idx) => {
                                                const valOld = oldData[key];
                                                const valNew = newData[key];
                                                const changed = String(valOld) !== String(valNew);
                                                
                                                const formatVal = (v) => {
                                                    if (v === null || v === undefined) return <span style={{ color: '#cbd5e1' }}>—</span>;
                                                    if (typeof v === 'object') return JSON.stringify(v, null, 2);
                                                    return String(v);
                                                };

                                                return (
                                                    <tr key={key} style={{ background: changed ? '#fffbf5' : (idx % 2 === 0 ? '#fff' : '#fafafa'), borderBottom: '1px solid #f8fafc' }}>
                                                        <td style={{ padding: '9px 14px', fontWeight: 700, color: '#475569', fontFamily: 'JetBrains Mono, monospace', fontSize: 11 }}>{key}</td>
                                                        <td style={{ padding: '9px 14px', color: '#f43f5e', fontFamily: 'JetBrains Mono, monospace', fontSize: 11, maxWidth: 120, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }} title={typeof valOld === 'object' ? JSON.stringify(valOld) : String(valOld)}>
                                                            {formatVal(valOld)}
                                                        </td>
                                                        <td style={{ padding: '9px 14px', color: '#059669', fontFamily: 'JetBrains Mono, monospace', fontSize: 11, fontWeight: 700, maxWidth: 120, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }} title={typeof valNew === 'object' ? JSON.stringify(valNew) : String(valNew)}>
                                                            {formatVal(valNew)}
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>
                </motion.div>
            </div>
        </AnimatePresence>
    );
}

// ── Main AuditTrail ──────────────────────────────────────────────────────────
export default function AuditTrail() {
    const t = i18n();
    const r = routes();
    const [logs, setLogs] = useState([]);
    const [filterData, setFilterData] = useState({ tables: [], users: [], types: [] });
    const [params, setParams] = useState({ table: '', type: '', search: '', start_date: '', end_date: '' });
    const [page, setPage] = useState(1);
    const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [selectedLog, setSelectedLog] = useState(null);
    const [loading, setLoading] = useState(true);

    const load = useCallback((overrides = {}) => {
        setLoading(true);
        const p = { ...params, ...overrides, page: overrides.page ?? page, per_page: 10 };
        get(buildUrl(r.api, p))
            .then(json => {
                setLogs(json.data || []);
                setFilterData(json.filters || { tables: [], users: [], types: [] });
                setMeta(json.meta || meta);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, [params, page]);

    useEffect(() => { load(); }, [page]);

    const th = { padding: '11px 20px', textAlign: 'left', fontSize: 10, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.1em', borderBottom: '1px solid #f1f5f9', background: '#fcfcfd', whiteSpace: 'nowrap' };
    const td = { padding: '14px 20px', borderBottom: '1px solid #f9fafb', verticalAlign: 'middle' };

    return (
        <>
            <HeatmapSection apiBase={r.api} />
            <FilterBar
                filters={filterData}
                params={params}
                setParams={setParams}
                onSearch={() => { setPage(1); load({ page: 1 }); }}
                onReset={() => {
                    const d = { table: '', type: '', search: '', start_date: '', end_date: '' };
                    setParams(d); setPage(1); load({ ...d, page: 1 });
                }}
            />

            {/* Table */}
            <div className="lt-card" style={{ overflow: 'hidden' }}>
                <div style={{ overflowX: 'auto' }}>
                    <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 13.5 }}>
                        <thead>
                            <tr>
                                <th style={th}>{t.date || 'Date'}</th>
                                <th style={th}>{t.user || 'User'}</th>
                                <th style={th}>{t.table || 'Table'}</th>
                                <th style={th}>{t.type || 'Type'}</th>
                                <th style={{ ...th, textAlign: 'right' }}>{t.action || 'Action'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {loading
                                ? Array.from({ length: 6 }).map((_, i) => <SkeletonRow key={i} />)
                                : logs.length === 0
                                    ? <tr><td colSpan={5} style={{ padding: '64px 20px', textAlign: 'center', color: '#94a3b8', fontSize: 14, fontWeight: 600 }}>{t.empty || 'No logs found'}</td></tr>
                                    : logs.map(log => {
                                        const tc = getType(log.log_type);
                                        const name = parseUsers(log.users);
                                        return (
                                            <tr key={log.id} className="lt-row"
                                                onClick={() => setSelectedLog(log)}
                                                style={{ background: '#fff', cursor: 'pointer' }}>
                                                <td style={td}>
                                                    <p style={{ fontWeight: 700, color: '#1e293b', fontSize: 13 }}>{log.log_date}</p>
                                                    <p style={{ fontSize: 11, color: '#94a3b8', fontWeight: 600, marginTop: 2, fontFamily: 'JetBrains Mono, monospace' }}>{log.log_time}</p>
                                                </td>
                                                <td style={td}>
                                                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                                        <div style={{ width: 34, height: 34, borderRadius: '50%', background: 'linear-gradient(135deg,#6366f1,#4f46e5)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: '#fff', fontWeight: 800, fontSize: 13, flexShrink: 0 }}>
                                                            {name.charAt(0).toUpperCase()}
                                                        </div>
                                                        <div>
                                                            <p style={{ fontWeight: 700, color: '#1e293b', fontSize: 13, maxWidth: 140, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{name}</p>
                                                            <p style={{ fontSize: 10, color: '#94a3b8', fontWeight: 600 }}>#ID {log.user_id}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style={td}>
                                                    <code style={{ padding: '3px 9px', borderRadius: 7, background: '#f1f5f9', color: '#475569', fontSize: 12, fontFamily: 'JetBrains Mono, monospace', fontWeight: 700 }}>{log.table_name}</code>
                                                </td>
                                                <td style={td}>
                                                    <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '4px 11px', borderRadius: 99, background: tc.bg, color: tc.color, border: `1px solid ${tc.border}`, fontSize: 11, fontWeight: 800 }}>
                                                        <span style={{ width: 6, height: 6, borderRadius: '50%', background: tc.dot }}></span>
                                                        {t[`type_${log.log_type?.toLowerCase()}`] || log.log_type}
                                                    </span>
                                                </td>
                                                <td style={{ ...td, textAlign: 'right' }}>
                                                    <button onClick={e => { e.stopPropagation(); setSelectedLog(log); }}
                                                        style={{ display: 'inline-flex', alignItems: 'center', gap: 5, padding: '6px 12px', borderRadius: 8, border: '1px solid #e2e8f0', background: '#f8fafc', color: '#64748b', fontSize: 12, fontWeight: 700, cursor: 'pointer', fontFamily: 'Outfit, sans-serif' }}>
                                                        <Eye size={13} /> View
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
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '12px 20px', background: '#fafafa', borderTop: '1px solid #f1f5f9' }}>
                    <p style={{ fontSize: 11, fontWeight: 700, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.08em' }}>{meta.total} Total Records</p>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <button disabled={page <= 1} onClick={() => setPage(p => p - 1)}
                            style={{ width: 32, height: 32, borderRadius: 8, border: '1px solid #e2e8f0', background: page <= 1 ? '#f8fafc' : '#fff', color: page <= 1 ? '#cbd5e1' : '#475569', cursor: page <= 1 ? 'not-allowed' : 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <span style={{ padding: '6px 16px', background: '#6366f1', color: '#fff', borderRadius: 8, fontSize: 12, fontWeight: 800 }}>{page} / {meta.last_page}</span>
                        <button disabled={page >= meta.last_page} onClick={() => setPage(p => p + 1)}
                            style={{ width: 32, height: 32, borderRadius: 8, border: '1px solid #e2e8f0', background: page >= meta.last_page ? '#f8fafc' : '#fff', color: page >= meta.last_page ? '#cbd5e1' : '#475569', cursor: page >= meta.last_page ? 'not-allowed' : 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {/* Detail Drawer */}
            {selectedLog && <DetailDrawer log={selectedLog} onClose={() => setSelectedLog(null)} />}
        </>
    );
}
