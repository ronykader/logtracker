import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import Sidebar from './components/Sidebar';
import AuditTrail from './pages/AuditTrail';
import Insights from './pages/Insights';
import SystemLogs from './pages/SystemLogs';
import { initLogtracker, isReady } from './utils/api';
import './app.css';

function getActivePage() {
    const path = window.location.pathname;
    if (/\/insights(\/|\?|$)/.test(path)) return 'insights';
    if (/\/system-logs(\/|\?|$)/.test(path)) return 'system';
    return 'audit';
}

function App() {
    const [ready, setReady] = useState(isReady());
    const [bootError, setBootError] = useState(null);

    useEffect(() => {
        if (!ready) {
            // Standalone Mode: If window.LOGTRACKER_API_URL is defined, use it.
            // Otherwise, it will fallback to the current origin.
            initLogtracker(window.LOGTRACKER_API_URL)
                .then(() => setReady(true))
                .catch(err => setBootError(err.message));
        }
    }, [ready]);

    if (bootError) return(
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100vh', background: '#f8fafc', padding: 20, textAlign: 'center' }}>
            <div className="lt-card" style={{ padding: 40, maxWidth: 400 }}>
                <div style={{ color: '#f43f5e', marginBottom: 16 }}>⚠️</div>
                <h3 style={{ fontSize: 18, color: '#0f172a', marginBottom: 8 }}>Logtracker Boot Failed</h3>
                <p style={{ fontSize: 14, color: '#64748b' }}>{bootError}</p>
            </div>
        </div>
    );

    if (!ready) return (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', height: '100vh', background: '#f8fafc' }}>
            <div className="shimmer" style={{ width: 60, height: 60, borderRadius: '50%' }}></div>
        </div>
    );

    const activePage = getActivePage();

    return (
        <Sidebar activePage={activePage}>
            {activePage === 'audit'    && <AuditTrail />}
            {activePage === 'insights' && <Insights />}
            {activePage === 'system'   && <SystemLogs />}
        </Sidebar>
    );
}

const rootElement = document.getElementById('logtracker-root');
if (rootElement) {
    createRoot(rootElement).render(<App />);
}
