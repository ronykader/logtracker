import React, { useState, useEffect } from 'react';
import {
    History, BarChart2, Terminal, Globe,
    ChevronLeft, ChevronRight, ShieldCheck,
    Maximize2, Minimize2,
} from 'lucide-react';
import { routes, locale } from '../utils/api';

const MENU = [
    { id: 'audit',    label: 'Audit Trail',  Icon: History,   routeKey: 'index'       },
    { id: 'insights', label: 'Insights',     Icon: BarChart2, routeKey: 'insights'    },
    { id: 'system',   label: 'System Logs',  Icon: Terminal,  routeKey: 'system-logs' },
];

const S = {
    aside: (collapsed) => ({
        width: collapsed ? 72 : 260,
        minWidth: collapsed ? 72 : 260,
        background: 'linear-gradient(180deg, #1e1b4b 0%, #1a1744 100%)',
        display: 'flex',
        flexDirection: 'column',
        height: '100vh',
        transition: 'width 0.25s ease, min-width 0.25s ease',
        overflow: 'hidden',
        flexShrink: 0,
        borderRight: '1px solid rgba(255,255,255,0.06)',
    }),
    logo: {
        padding: '22px 16px 18px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        borderBottom: '1px solid rgba(255,255,255,0.07)',
        gap: 8,
    },
    logoIcon: {
        width: 34, height: 34,
        borderRadius: 10,
        background: 'linear-gradient(135deg, #6366f1, #4f46e5)',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        flexShrink: 0,
        boxShadow: '0 4px 12px rgba(99,102,241,0.4)',
    },
    logoText: {
        fontSize: 17, fontWeight: 900, color: '#fff',
        whiteSpace: 'nowrap', overflow: 'hidden',
        flex: 1, letterSpacing: '-0.02em',
    },
    toggleBtn: {
        width: 28, height: 28, borderRadius: 7,
        background: 'rgba(255,255,255,0.07)',
        border: '1px solid rgba(255,255,255,0.1)',
        cursor: 'pointer',
        display: 'flex', alignItems: 'center', justifyContent: 'center',
        color: '#a5b4fc', flexShrink: 0,
        transition: 'background 0.15s',
    },
    nav: { padding: '14px 10px', flex: 1, overflowY: 'auto' },
    navLabel: {
        fontSize: 9, fontWeight: 800, letterSpacing: '0.14em',
        textTransform: 'uppercase', color: 'rgba(165,180,252,0.4)',
        padding: '0 8px', marginBottom: 6, display: 'block',
        whiteSpace: 'nowrap',
    },
    navItem: (isActive, collapsed) => ({
        display: 'flex', alignItems: 'center', gap: 11,
        padding: collapsed ? '11px' : '10px 11px',
        borderRadius: 10,
        color: isActive ? '#fff' : 'rgba(199,210,254,0.7)',
        textDecoration: 'none', fontSize: 13.5, fontWeight: 600,
        marginBottom: 3, whiteSpace: 'nowrap',
        background: isActive ? 'rgba(99,102,241,0.22)' : 'transparent',
        border: `1px solid ${isActive ? 'rgba(99,102,241,0.38)' : 'transparent'}`,
        transition: 'all 0.15s',
        justifyContent: collapsed ? 'center' : 'flex-start',
    }),
    footer: {
        padding: '10px',
        borderTop: '1px solid rgba(255,255,255,0.07)',
    },
    langBtn: {
        display: 'flex', alignItems: 'center', gap: 9,
        width: '100%', padding: '9px 11px', borderRadius: 9,
        background: 'rgba(255,255,255,0.05)',
        border: '1px solid rgba(255,255,255,0.08)',
        color: 'rgba(199,210,254,0.65)',
        fontSize: 13, fontWeight: 700, cursor: 'pointer',
        whiteSpace: 'nowrap', transition: 'all 0.15s',
        fontFamily: 'inherit',
    },
    main: {
        flex: 1, minWidth: 0,
        display: 'flex', flexDirection: 'column',
        height: '100vh', overflow: 'hidden',
    },
    topbar: {
        height: 58, minHeight: 58,
        background: '#fff',
        borderBottom: '1px solid #e9eef5',
        display: 'flex', alignItems: 'center', justifyContent: 'space-between',
        padding: '0 28px',
    },
    pageTitle: {
        fontSize: 12, fontWeight: 800, color: '#94a3b8',
        textTransform: 'uppercase', letterSpacing: '0.1em',
    },
    versionBadge: {
        fontSize: 10, fontWeight: 800, color: '#6366f1',
        background: '#eef2ff', border: '1px solid #c7d2fe',
        padding: '3px 11px', borderRadius: 99,
        letterSpacing: '0.04em',
    },
    content: { flex: 1, overflowY: 'auto', padding: 28, background: '#f8fafc' },
};

export default function Sidebar({ children, activePage }) {
    const [collapsed, setCollapsed] = useState(false);
    const [isFullscreen, setIsFullscreen] = useState(false);
    const r = routes();
    const lang = locale();

    // Sync fullscreen state with browser
    useEffect(() => {
        const onChange = () => setIsFullscreen(!!document.fullscreenElement);
        document.addEventListener('fullscreenchange', onChange);
        return () => document.removeEventListener('fullscreenchange', onChange);
    }, []);

    const toggleFullscreen = () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(() => {});
        } else {
            document.exitFullscreen().catch(() => {});
        }
    };

    const toggleLang = () => {
        const newLocale = lang === 'en' ? 'bn' : 'en';
        const url = new URL(window.location.href);
        url.searchParams.set('locale', newLocale);
        window.location.href = url.toString();
    };

    const currentLabel = MENU.find(m => m.id === activePage)?.label || 'LogTracker';

    return (
        <div style={{ display: 'flex', height: '100vh', overflow: 'hidden', width: '100%' }}>
            {/* ── Sidebar ── */}
            <aside style={S.aside(collapsed)}>
                {/* Logo */}
                <div style={S.logo}>
                    <div style={S.logoIcon}>
                        <ShieldCheck size={18} color="#fff" />
                    </div>
                    {!collapsed && <span style={S.logoText}>LogTracker</span>}
                    <button
                        style={S.toggleBtn}
                        onClick={() => setCollapsed(c => !c)}
                        onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.14)'}
                        onMouseLeave={e => e.currentTarget.style.background = 'rgba(255,255,255,0.07)'}
                    >
                        {collapsed ? <ChevronRight size={14} /> : <ChevronLeft size={14} />}
                    </button>
                </div>

                {/* Nav */}
                <nav style={S.nav}>
                    {!collapsed && <span style={S.navLabel}>Navigation</span>}
                    {MENU.map(({ id, label, Icon, routeKey }) => {
                        const isActive = activePage === id;
                        return (
                            <a
                                key={id}
                                href={r[routeKey] || '#'}
                                style={S.navItem(isActive, collapsed)}
                                title={collapsed ? label : undefined}
                                onMouseEnter={e => { if (!isActive) { e.currentTarget.style.background = 'rgba(255,255,255,0.07)'; e.currentTarget.style.color = '#fff'; } }}
                                onMouseLeave={e => { if (!isActive) { e.currentTarget.style.background = 'transparent'; e.currentTarget.style.color = 'rgba(199,210,254,0.7)'; } }}
                            >
                                <Icon size={17} color={isActive ? '#a5b4fc' : 'currentColor'} style={{ flexShrink: 0 }} />
                                {!collapsed && <span>{label}</span>}
                            </a>
                        );
                    })}
                </nav>

                {/* Language Toggle */}
                <div style={S.footer}>
                    <button
                        style={S.langBtn}
                        onClick={toggleLang}
                        onMouseEnter={e => { e.currentTarget.style.background = 'rgba(255,255,255,0.1)'; e.currentTarget.style.color = '#fff'; }}
                        onMouseLeave={e => { e.currentTarget.style.background = 'rgba(255,255,255,0.05)'; e.currentTarget.style.color = 'rgba(199,210,254,0.65)'; }}
                    >
                        <Globe size={15} style={{ flexShrink: 0 }} />
                        {!collapsed && <span>{lang === 'bn' ? 'বাংলা' : 'English'}</span>}
                    </button>
                </div>
            </aside>

            {/* ── Main Content ── */}
            <div style={S.main}>
                <header style={S.topbar}>
                    <span style={S.pageTitle}>{currentLabel}</span>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <span style={S.versionBadge}>AUDIT ENGINE v2.0</span>
                        <button
                            onClick={toggleFullscreen}
                            title={isFullscreen ? 'Exit Fullscreen' : 'Enter Fullscreen'}
                            style={{
                                width: 34, height: 34,
                                borderRadius: 9,
                                border: '1px solid #e2e8f0',
                                background: isFullscreen ? '#eef2ff' : '#f8fafc',
                                color: isFullscreen ? '#6366f1' : '#94a3b8',
                                cursor: 'pointer',
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                transition: 'all 0.15s',
                            }}
                            onMouseEnter={e => { e.currentTarget.style.background = '#eef2ff'; e.currentTarget.style.color = '#6366f1'; e.currentTarget.style.borderColor = '#c7d2fe'; }}
                            onMouseLeave={e => { e.currentTarget.style.background = isFullscreen ? '#eef2ff' : '#f8fafc'; e.currentTarget.style.color = isFullscreen ? '#6366f1' : '#94a3b8'; e.currentTarget.style.borderColor = '#e2e8f0'; }}
                        >
                            {isFullscreen ? <Minimize2 size={15} /> : <Maximize2 size={15} />}
                        </button>
                    </div>
                </header>
                <main style={S.content}>
                    {children}
                </main>
            </div>
        </div>
    );
}
