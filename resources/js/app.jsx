import React from 'react';
import { createRoot } from 'react-dom/client';
import Sidebar from './components/Sidebar';
import AuditTrail from './pages/AuditTrail';
import Insights from './pages/Insights';
import SystemLogs from './pages/SystemLogs';
import './app.css';

function getActivePage() {
    const path = window.location.pathname;
    if (/\/insights(\/|\?|$)/.test(path)) return 'insights';
    if (/\/system-logs(\/|\?|$)/.test(path)) return 'system';
    return 'audit';
}

function App() {
    const activePage = getActivePage();

    return (
        <Sidebar activePage={activePage}>
            {activePage === 'audit'    && <AuditTrail />}
            {activePage === 'insights' && <Insights />}
            {activePage === 'system'   && <SystemLogs />}
        </Sidebar>
    );
}

createRoot(document.getElementById('logtracker-root')).render(<App />);
