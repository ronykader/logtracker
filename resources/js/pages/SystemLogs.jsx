import React, { useState, useEffect, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    Terminal, RefreshCw, ChevronDown, ChevronUp,
    AlertOctagon, AlertTriangle, Info, Bug, Zap, SearchX,
    Trash2, CheckSquare, Square, XCircle, AlertCircle, Check,
} from 'lucide-react';
import { routes, get } from '../utils/api';

// ── Level config ─────────────────────────────────────────────────────────────
const LEVEL_CONFIG = {
    ERROR:     { bg: '#fff1f2', text: '#9f1239', border: '#fda4af', bar: '#f43f5e', Icon: AlertOctagon  },
    CRITICAL:  { bg: '#fee2e2', text: '#7f1d1d', border: '#fca5a5', bar: '#ef4444', Icon: AlertOctagon  },
    EMERGENCY: { bg: '#f5f3ff', text: '#4c1d95', border: '#ddd6fe', bar: '#7c3aed', Icon: Zap           },
    WARNING:   { bg: '#fffbeb', text: '#92400e', border: '#fcd34d', bar: '#f59e0b', Icon: AlertTriangle },
    NOTICE:    { bg: '#eff6ff', text: '#1e40af', border: '#bfdbfe', bar: '#3b82f6', Icon: Info          },
    INFO:      { bg: '#f0f9ff', text: '#0c4a6e', border: '#bae6fd', bar: '#0ea5e9', Icon: Info          },
    DEBUG:     { bg: '#f8fafc', text: '#475569', border: '#e2e8f0', bar: '#94a3b8', Icon: Bug           },
};
const getLevel = (l) => LEVEL_CONFIG[l?.toUpperCase()] || LEVEL_CONFIG.DEBUG;

// ── Toast ────────────────────────────────────────────────────────────────────
function Toast({ message, type, onDone }) {
    useEffect(() => { const t = setTimeout(onDone, 3000); return () => clearTimeout(t); }, []);
    const isError = type === 'error';
    return (
        <motion.div
            initial={{ opacity: 0, y: 20, scale: 0.95 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 20, scale: 0.95 }}
            style={{
                position: 'fixed', bottom: 28, right: 28, zIndex: 9999,
                display: 'flex', alignItems: 'center', gap: 10,
                padding: '12px 18px', borderRadius: 14,
                background: isError ? '#fff1f2' : '#f0fdf4',
                border: `1px solid ${isError ? '#fda4af' : '#86efac'}`,
                boxShadow: '0 8px 30px rgba(0,0,0,0.1)',
                fontSize: 13, fontWeight: 700,
                color: isError ? '#9f1239' : '#14532d',
            }}>
            {isError ? <AlertCircle size={16} /> : <Check size={16} />}
            {message}
        </motion.div>
    );
}

// ── Confirm Dialog ────────────────────────────────────────────────────────────
function ConfirmDialog({ message, onConfirm, onCancel }) {
    return (
        <div style={{ position: 'fixed', inset: 0, zIndex: 2000, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: 20 }}>
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} onClick={onCancel}
                style={{ position: 'absolute', inset: 0, background: 'rgba(15,23,42,0.5)', backdropFilter: 'blur(4px)' }} />
            <motion.div
                initial={{ scale: 0.9, opacity: 0 }} animate={{ scale: 1, opacity: 1 }}
                style={{ position: 'relative', zIndex: 1, background: '#fff', borderRadius: 20, padding: '28px 28px 22px', maxWidth: 420, width: '100%', boxShadow: '0 20px 60px rgba(0,0,0,0.15)' }}>
                <div style={{ width: 46, height: 46, borderRadius: 14, background: '#fff1f2', border: '1px solid #fda4af', display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: 14 }}>
                    <Trash2 size={20} color="#f43f5e" />
                </div>
                <p style={{ fontSize: 16, fontWeight: 800, color: '#0f172a', marginBottom: 8 }}>Are you sure?</p>
                <p style={{ fontSize: 13.5, color: '#64748b', lineHeight: 1.6, marginBottom: 22 }}>{message}</p>
                <div style={{ display: 'flex', gap: 8 }}>
                    <button onClick={onCancel}
                        style={{ flex: 1, padding: '10px', borderRadius: 10, border: '1px solid #e2e8f0', background: '#f8fafc', color: '#64748b', fontSize: 13, fontWeight: 700, cursor: 'pointer', fontFamily: 'Outfit, sans-serif' }}>
                        Cancel
                    </button>
                    <button onClick={onConfirm}
                        style={{ flex: 1, padding: '10px', borderRadius: 10, border: 'none', background: '#f43f5e', color: '#fff', fontSize: 13, fontWeight: 700, cursor: 'pointer', fontFamily: 'Outfit, sans-serif', boxShadow: '0 2px 8px rgba(244,63,94,0.3)' }}>
                        Delete
                    </button>
                </div>
            </motion.div>
        </div>
    );
}

// ── Log Entry ─────────────────────────────────────────────────────────────────
function LogEntry({ log, index, selected, onSelect, onDelete }) {
    const [open, setOpen] = useState(false);
    const lc = getLevel(log.level);
    const { Icon } = lc;
    const hasStack = log.stack && log.stack.trim().length > 0;
    const isSelected = selected;

    return (
        <div style={{
            borderRadius: 14,
            border: `1px solid ${isSelected ? '#6366f1' : open ? lc.border : '#e9eef5'}`,
            borderLeft: `4px solid ${isSelected ? '#6366f1' : lc.bar}`,
            background: isSelected ? '#eef2ff' : '#fff',
            overflow: 'hidden',
            transition: 'border-color 0.15s, background 0.15s, box-shadow 0.15s',
            boxShadow: open ? '0 4px 20px rgba(0,0,0,0.06)' : 'none',
        }}>
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: 0 }}>
                {/* Checkbox */}
                <button onClick={() => onSelect(log.timestamp)}
                    style={{ padding: '14px 12px', background: 'transparent', border: 'none', cursor: 'pointer', color: isSelected ? '#6366f1' : '#cbd5e1', display: 'flex', alignItems: 'center', flexShrink: 0, transition: 'color 0.15s' }}>
                    {isSelected ? <CheckSquare size={16} /> : <Square size={16} />}
                </button>

                {/* Main row */}
                <button onClick={() => setOpen(o => !o)}
                    style={{ flex: 1, display: 'flex', alignItems: 'flex-start', gap: 12, padding: '12px 12px 12px 0', background: 'transparent', border: 'none', cursor: 'pointer', textAlign: 'left', fontFamily: 'inherit', transition: 'background 0.12s', minWidth: 0 }}
                    onMouseEnter={e => { if (!isSelected) e.currentTarget.style.background = '#f8fafc'; }}
                    onMouseLeave={e => { e.currentTarget.style.background = 'transparent'; }}
                >
                    {/* Level badge */}
                    <div style={{ flexShrink: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '7px 9px', borderRadius: 10, background: lc.bg, border: `1px solid ${lc.border}`, minWidth: 64, gap: 3 }}>
                        <Icon size={14} color={lc.text} />
                        <span style={{ fontSize: 9, fontWeight: 900, color: lc.text, textTransform: 'uppercase', letterSpacing: '0.05em' }}>{log.level}</span>
                    </div>

                    {/* Content */}
                    <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 7, marginBottom: 3, flexWrap: 'wrap' }}>
                            <span style={{ fontSize: 11, fontWeight: 700, color: '#64748b', fontFamily: 'JetBrains Mono, monospace' }}>{log.timestamp}</span>
                            {log.env && <span style={{ padding: '1px 7px', borderRadius: 5, background: '#f1f5f9', fontSize: 9, fontWeight: 800, color: '#64748b', textTransform: 'uppercase', letterSpacing: '0.06em' }}>{log.env}</span>}
                        </div>
                        <p style={{ fontSize: 13, fontWeight: 600, color: '#1e293b', fontFamily: 'JetBrains Mono, monospace', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                            {log.message}
                        </p>
                    </div>

                    {/* Expand toggle */}
                    {hasStack && (
                        <div style={{ flexShrink: 0, width: 26, height: 26, borderRadius: 7, border: '1px solid #e2e8f0', background: open ? lc.bg : '#f8fafc', display: 'flex', alignItems: 'center', justifyContent: 'center', color: open ? lc.text : '#94a3b8', marginTop: 2 }}>
                            {open ? <ChevronUp size={13} /> : <ChevronDown size={13} />}
                        </div>
                    )}
                </button>

                {/* Delete button */}
                <button onClick={() => onDelete(log.timestamp)}
                    style={{ padding: '14px 14px', background: 'transparent', border: 'none', cursor: 'pointer', color: '#fca5a5', display: 'flex', alignItems: 'center', flexShrink: 0, transition: 'color 0.15s' }}
                    title="Delete this entry"
                    onMouseEnter={e => e.currentTarget.style.color = '#f43f5e'}
                    onMouseLeave={e => e.currentTarget.style.color = '#fca5a5'}
                >
                    <Trash2 size={15} />
                </button>
            </div>

            {/* Stack trace */}
            <AnimatePresence>
                {open && (
                    <motion.div
                        initial={{ height: 0, opacity: 0 }} animate={{ height: 'auto', opacity: 1 }} exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.2 }} style={{ overflow: 'hidden' }}>
                        <div style={{ padding: '12px 16px 16px 52px', borderTop: '1px solid #f1f5f9', background: '#fafafa', display: 'flex', flexDirection: 'column', gap: 12 }}>
                            <div>
                                <p style={{ fontSize: 9, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 6 }}>Full Message</p>
                                <div style={{ background: '#fff', border: '1px solid #e2e8f0', borderRadius: 10, padding: '10px 14px', fontSize: 13, color: '#334155', fontFamily: 'JetBrains Mono, monospace', wordBreak: 'break-word', lineHeight: 1.6 }}>
                                    {log.message}
                                </div>
                            </div>
                            {hasStack && (
                                <div>
                                    <p style={{ fontSize: 9, fontWeight: 800, color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: 6 }}>Stack Trace</p>
                                    <div style={{ background: '#0f172a', borderRadius: 12, padding: '14px 16px', overflowX: 'auto' }}>
                                        <pre style={{ fontSize: 11, color: '#94a3b8', fontFamily: 'JetBrains Mono, monospace', whiteSpace: 'pre-wrap', wordBreak: 'break-word', lineHeight: 1.7, margin: 0 }}>
                                            {log.stack.trim()}
                                        </pre>
                                    </div>
                                </div>
                            )}
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}

// ── Main ──────────────────────────────────────────────────────────────────────
export default function SystemLogs() {
    const r = routes();
    const [logs, setLogs]           = useState([]);
    const [loading, setLoading]     = useState(true);
    const [spinning, setSpinning]   = useState(false);
    const [error, setError]         = useState(null);
    const [levelFilter, setLevel]   = useState('ALL');
    const [search, setSearch]       = useState('');
    const [selected, setSelected]   = useState(new Set());
    const [confirm, setConfirm]     = useState(null); // { type: 'clear'|'selected'|'single', payload }
    const [toast, setToast]         = useState(null);
    
    // Multi-file state
    const [availableFiles, setFiles] = useState(['laravel.log']);
    const [selectedFile, setFile]   = useState('laravel.log');

    const fetchLogs = useCallback((fileName = selectedFile) => {
        setLoading(true); setError(null); setSpinning(true); setSelected(new Set());
        const url = new URL(r['system-log-data'], window.location.origin);
        url.searchParams.set('file', fileName);

        fetch(url, {
            headers: { 'Accept': 'application/json', 'Cache-Control': 'no-store', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(res => res.json())
            .then(json => { 
                setLogs(json.data || []); 
                if (json.files) setFiles(json.files);
                setLoading(false); 
                setSpinning(false); 
            })
            .catch(() => { setError(`Could not read ${fileName}. Check file permissions.`); setLoading(false); setSpinning(false); });
    }, [selectedFile]);

    useEffect(() => { fetchLogs(); }, [selectedFile]);

    // ── Filtering ────────────────────────────────────────────────────────────
    const filtered = logs.filter(log => {
        const matchLvl = levelFilter === 'ALL' || log.level?.toUpperCase() === levelFilter;
        const q = search.toLowerCase();
        const matchSrch = !q || log.message?.toLowerCase().includes(q) || (log.stack || '').toLowerCase().includes(q);
        return matchLvl && matchSrch;
    });

    const levelCounts = logs.reduce((a, l) => { a[l.level] = (a[l.level] || 0) + 1; return a; }, {});

    // ── Selection ────────────────────────────────────────────────────────────
    const toggleSelect = (ts) => setSelected(s => { const n = new Set(s); n.has(ts) ? n.delete(ts) : n.add(ts); return n; });
    const toggleSelectAll = () => {
        if (selected.size === filtered.length) setSelected(new Set());
        else setSelected(new Set(filtered.map(l => l.timestamp)));
    };

    // ── API Calls ─────────────────────────────────────────────────────────────
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const postJson = (url, body) => fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    })
        .then(res => res.json())
        .then(json => {
            // Surface backend errors (success:false) to the catch handler
            if (json.success === false) throw new Error(json.message || 'Operation failed.');
            return json;
        });

    const executeClear = () => {
        setConfirm(null);
        postJson(r['system-log-clear'], { file: selectedFile })
            .then(res => { setToast({ message: res.message || 'Log file cleared.', type: 'success' }); fetchLogs(); })
            .catch(err => setToast({ message: err.message || 'Failed to clear log file.', type: 'error' }));
    };

    const executeDelete = (timestamps) => {
        setConfirm(null);
        postJson(r['system-log-delete'], { timestamps: [...timestamps], file: selectedFile })
            .then(res => { setToast({ message: res.message || 'Deleted.', type: 'success' }); fetchLogs(); })
            .catch(err => setToast({ message: err.message || 'Failed to delete entries.', type: 'error' }));
    };

    const allFilteredSelected = filtered.length > 0 && filtered.every(l => selected.has(l.timestamp));

    return (
        <>
            {/* ── Toolbar ── */}
            <div className="lt-card" style={{ padding: '13px 18px', marginBottom: 16, display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 10 }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    <div style={{ width: 38, height: 38, borderRadius: 11, background: '#0f172a', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <Terminal size={17} color="#34d399" />
                    </div>
                    <div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                            <p style={{ fontSize: 13, fontWeight: 800, color: '#1e293b', fontFamily: 'JetBrains Mono, monospace' }}>storage/logs/</p>
                            <select value={selectedFile} onChange={e => setFile(e.target.value)}
                                style={{ padding: '2px 8px', border: '1px solid #e2e8f0', borderRadius: 6, fontSize: 12, fontWeight: 700, color: '#6366f1', background: '#f5f3ff', outline: 'none', cursor: 'pointer' }}>
                                {availableFiles.map(f => <option key={f} value={f}>{f}</option>)}
                            </select>
                        </div>
                        <p style={{ fontSize: 10, color: '#94a3b8', fontWeight: 600, marginTop: 1 }}>Last {logs.length} entries &bull; Newest first</p>
                    </div>
                </div>

                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    {/* Search */}
                    <div style={{ position: 'relative' }}>
                        <input type="text" value={search} onChange={e => setSearch(e.target.value)} placeholder="Search logs..."
                            style={{ width: 190, padding: '8px 12px 8px 34px', background: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: 9, fontSize: 13, fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#334155', outline: 'none' }} />
                        <svg style={{ position: 'absolute', left: 11, top: '50%', transform: 'translateY(-50%)', color: '#94a3b8' }} width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    {/* Level filter */}
                    <select value={levelFilter} onChange={e => setLevel(e.target.value)}
                        style={{ padding: '8px 12px', background: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: 9, fontSize: 13, fontFamily: 'Outfit, sans-serif', fontWeight: 700, color: '#334155', outline: 'none', cursor: 'pointer' }}>
                        <option value="ALL">All Levels ({logs.length})</option>
                        {Object.entries(levelCounts).sort().map(([l, c]) => <option key={l} value={l}>{l} ({c})</option>)}
                    </select>
                    {/* Refresh */}
                    <button onClick={fetchLogs} title="Refresh"
                        style={{ width: 36, height: 36, borderRadius: 9, border: '1px solid #e2e8f0', background: '#f8fafc', color: '#64748b', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <RefreshCw size={15} style={{ animation: spinning ? 'spin 1s linear infinite' : 'none' }} />
                    </button>
                    {/* Clear all */}
                    <button onClick={() => setConfirm({ type: 'clear' })} title="Clear entire log file"
                        style={{ display: 'flex', alignItems: 'center', gap: 6, padding: '8px 14px', borderRadius: 9, border: '1px solid #fda4af', background: '#fff1f2', color: '#f43f5e', fontSize: 12, fontWeight: 800, cursor: 'pointer', fontFamily: 'Outfit, sans-serif' }}>
                        <Trash2 size={13} /> Clear All
                    </button>
                </div>
            </div>

            {/* ── Error ── */}
            {error && (
                <div style={{ background: '#fff1f2', border: '1px solid #fda4af', borderRadius: 12, padding: '13px 18px', marginBottom: 14, display: 'flex', alignItems: 'center', gap: 10 }}>
                    <AlertTriangle size={17} color="#f43f5e" style={{ flexShrink: 0 }} />
                    <p style={{ fontSize: 13, fontWeight: 600, color: '#9f1239' }}>{error}</p>
                </div>
            )}

            {/* ── Select All bar ── */}
            {!loading && filtered.length > 0 && (
                <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '8px 14px', marginBottom: 10, background: '#f8fafc', border: '1px solid #e2e8f0', borderRadius: 10 }}>
                    <button onClick={toggleSelectAll}
                        style={{ display: 'flex', alignItems: 'center', gap: 7, padding: '5px 10px', borderRadius: 7, border: '1px solid #e2e8f0', background: '#fff', fontSize: 12, fontWeight: 700, color: '#475569', cursor: 'pointer', fontFamily: 'Outfit, sans-serif' }}>
                        {allFilteredSelected ? <CheckSquare size={14} color="#6366f1" /> : <Square size={14} />}
                        {allFilteredSelected ? 'Deselect All' : `Select All (${filtered.length})`}
                    </button>
                    {selected.size > 0 && (
                        <span style={{ fontSize: 12, fontWeight: 700, color: '#6366f1' }}>{selected.size} selected</span>
                    )}
                </div>
            )}

            {/* ── Log list ── */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 7 }}>
                {loading
                    ? Array.from({ length: 6 }).map((_, i) => <div key={i} className="shimmer" style={{ height: 72, borderRadius: 14 }}></div>)
                    : filtered.length === 0
                        ? <div className="lt-card" style={{ padding: '60px 20px', textAlign: 'center' }}>
                            <div style={{ width: 50, height: 50, borderRadius: 14, background: '#f8fafc', border: '1px solid #e2e8f0', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 12px' }}>
                                <SearchX size={22} color="#cbd5e1" />
                            </div>
                            <p style={{ fontSize: 14, fontWeight: 700, color: '#94a3b8' }}>No log entries match your filter</p>
                          </div>
                        : filtered.map((log, i) => (
                            <LogEntry key={i} log={log} index={i}
                                selected={selected.has(log.timestamp)}
                                onSelect={toggleSelect}
                                onDelete={(ts) => setConfirm({ type: 'single', payload: [ts] })}
                            />
                        ))
                }
            </div>

            {/* ── Bulk action footer ── */}
            <AnimatePresence>
                {selected.size > 0 && (
                    <motion.div
                        initial={{ y: 80, opacity: 0 }} animate={{ y: 0, opacity: 1 }} exit={{ y: 80, opacity: 0 }}
                        style={{ position: 'fixed', bottom: 28, left: '50%', transform: 'translateX(-50%)', zIndex: 500, display: 'flex', alignItems: 'center', gap: 12, padding: '14px 22px', background: '#1e1b4b', borderRadius: 20, boxShadow: '0 8px 40px rgba(30,27,75,0.35)', border: '1px solid rgba(165,180,252,0.2)' }}>
                        <span style={{ fontSize: 13, fontWeight: 800, color: '#a5b4fc' }}>
                            {selected.size} entr{selected.size !== 1 ? 'ies' : 'y'} selected
                        </span>
                        <div style={{ width: 1, height: 20, background: 'rgba(255,255,255,0.15)' }}></div>
                        <button onClick={() => setConfirm({ type: 'selected', payload: selected })}
                            style={{ display: 'flex', alignItems: 'center', gap: 7, padding: '8px 16px', borderRadius: 10, border: 'none', background: '#f43f5e', color: '#fff', fontSize: 13, fontWeight: 800, cursor: 'pointer', fontFamily: 'Outfit, sans-serif' }}>
                            <Trash2 size={14} /> Delete Selected
                        </button>
                        <button onClick={() => setSelected(new Set())}
                            style={{ display: 'flex', alignItems: 'center', gap: 6, background: 'transparent', border: 'none', color: 'rgba(165,180,252,0.7)', cursor: 'pointer', fontSize: 13, fontWeight: 700, fontFamily: 'Outfit, sans-serif' }}>
                            <XCircle size={14} /> Clear selection
                        </button>
                    </motion.div>
                )}
            </AnimatePresence>

            {/* ── Confirm dialog ── */}
            {confirm && (
                <ConfirmDialog
                    message={
                        confirm.type === 'clear'
                            ? `This will permanently delete the entire "${selectedFile}" file contents. This action cannot be undone.`
                            : `This will permanently delete ${confirm.type === 'single' ? 'this log entry' : `${confirm.payload.size} selected entries`} from "${selectedFile}". This cannot be undone.`
                    }
                    onConfirm={() => confirm.type === 'clear' ? executeClear() : executeDelete(confirm.payload)}
                    onCancel={() => setConfirm(null)}
                />
            )}

            {/* ── Toast ── */}
            <AnimatePresence>
                {toast && <Toast key="toast" message={toast.message} type={toast.type} onDone={() => setToast(null)} />}
            </AnimatePresence>
        </>
    );
}
