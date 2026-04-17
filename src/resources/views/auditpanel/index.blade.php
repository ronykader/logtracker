<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log Panel</title>
    <style>
        body { margin: 0; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f8fafc; color: #111827; }
        .container { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .card { background: white; border-radius: 16px; box-shadow: 0 2px 18px rgba(15, 23, 42, 0.08); padding: 24px; }
        .header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
        .title { font-size: 1.75rem; font-weight: 700; margin: 0; }
        .subtitle { color: #6b7280; margin-top: 8px; }
        .table-container { overflow-x: auto; margin-top: 24px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 14px 12px; border-bottom: 1px solid #e5e7eb; }
        th { color: #374151; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; }
        tr:hover { background: #f9fafb; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; font-size: 0.8rem; font-weight: 600; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-top: 24px; }
        .filter-item { display: flex; flex-direction: column; gap: 6px; }
        .filter-item label { font-weight: 600; color: #374151; font-size: 0.92rem; }
        .filter-item input,
        .filter-item select { width: 100%; border: 1px solid #d1d5db; border-radius: 10px; padding: 10px 12px; background: #ffffff; color: #111827; }
        .filter-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-top: 18px; }
        .filter-button { border: none; background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); color: white; padding: 10px 18px; border-radius: 12px; cursor: pointer; font-weight: 700; transition: transform 0.2s ease, box-shadow 0.2s ease; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.18); }
        .filter-button:hover { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22); }
        .table-container { overflow-x: auto; margin-top: 24px; }
        .selected-row { background: #eef2ff; }
        .details { white-space: pre-wrap; word-break: break-word; font-size: 0.92rem; color: #374151; }
        .details-panel { margin-top: 24px; padding: 18px; border-radius: 16px; background: #f8fafc; border: 1px solid #e5e7eb; }
        .detail-title { margin: 0 0 14px; font-size: 1rem; font-weight: 700; color: #111827; }
        .detail-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 12px; }
        .detail-label { font-size: 0.85rem; color: #6b7280; margin-bottom: 4px; }
        .detail-value { font-size: 0.95rem; color: #111827; }
        .loading { font-size: 1rem; color: #4b5563; margin-top: 18px; }
        .error { color: #b91c1c; margin-top: 18px; }
        .details { white-space: pre-wrap; word-break: break-word; font-size: 0.92rem; color: #374151; }
        .detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-top: 18px; }
        .detail-card { background: white; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px; }
        .detail-row-diff { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .detail-row-diff.changed { background: #fef3c7; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); display: flex; align-items: center; justify-content: center; padding: 16px; z-index: 30; }
        .modal { width: min(1100px, 100%); max-height: 90vh; overflow-y: auto; background: white; border-radius: 18px; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2); padding: 24px; }
        .modal-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; border-bottom: 1px solid #e5e7eb; padding-bottom: 14px; }
        .modal-title { margin: 0; font-size: 1.35rem; font-weight: 800; color: #0f172a; }
        .modal-close { border: none; background: #dc2626; color: white; border-radius: 10px; padding: 10px 16px; cursor: pointer; font-weight: 700; box-shadow: 0 8px 20px rgba(220, 38, 38, 0.18); }
        .modal-close:hover { background: #b91c1c; }
        .modal-meta { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin-bottom: 18px; }
        .modal-meta-item { background: #eff6ff; border-radius: 12px; padding: 12px 14px; }
        .modal-meta-label { font-size: 0.82rem; color: #475569; margin-bottom: 4px; }
        .modal-meta-value { font-size: 0.95rem; color: #0f172a; font-weight: 600; }
        .modal-section { margin-top: 18px; }
        .modal-section h3 { margin-bottom: 12px; font-size: 1rem; color: #111827; }
        .modal-table { width: 100%; border-collapse: collapse; }
        .modal-table th, .modal-table td { padding: 12px 14px; border: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
        .modal-table th { background: #f8fafc; color: #374151; font-weight: 600; }
        .modal-table tr.changed td { background: #fef3c7; }
        .modal-value { white-space: pre-wrap; word-break: break-word; color: #111827; }
        .modal-copy { color: #2563eb; font-weight: 600; cursor: pointer; }
    </style>
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <div>
                    <h1 class="title">Audit Log Panel</h1>
                    <p class="subtitle">React-powered audit log list for this project.</p>
                </div>
            </div>
            <div id="audit-panel-root"></div>
        </div>
    </div>

    <script>
        const { useState, useEffect } = React;

        function statusBadge(type) {
            const normalized = String(type || '').toLowerCase();
            if (normalized.includes('delete')) return { label: type, className: 'badge badge-warning' };
            if (normalized.includes('update') || normalized.includes('edit')) return { label: type, className: 'badge badge-info' };
            return { label: type || 'unknown', className: 'badge badge-success' };
        }

        function parseJSON(value) {
            if (typeof value === 'object' && value !== null) {
                return value;
            }
            try {
                return JSON.parse(value || '{}');
            } catch (e) {
                return String(value || '');
            }
        }

        function isEqual(value1, value2) {
            return JSON.stringify(value1) === JSON.stringify(value2);
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function humanizeKey(key) {
            return String(key)
                .replace(/_/g, ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase())
                .replace(/Bng\b/, 'Bangla')
                .replace(/Eng\b/, 'English');
        }

        function formatObject(value) {
            if (value === null || value === undefined) {
                return '';
            }
            if (Array.isArray(value)) {
                return value
                    .map((item, index) => `• ${index + 1}. ${formatObject(item)}`)
                    .join('\n');
            }
            if (typeof value === 'object') {
                return Object.entries(value)
                    .map(([key, item]) => `${humanizeKey(key)}: ${formatObject(item)}`)
                    .join('\n');
            }
            return String(value);
        }

        function renderValue(value) {
            if (typeof value === 'string') {
                const parsed = parseJSON(value);
                if (typeof parsed === 'object' && parsed !== null) {
                    return formatObject(parsed);
                }
            }
            if (typeof value === 'object' && value !== null) {
                return formatObject(value);
            }
            return String(value || '');
        }

        function formatAuditText(value) {
            if (typeof value === 'string') {
                const parsed = parseJSON(value);
                if (typeof parsed === 'object' && parsed !== null) {
                    value = parsed;
                }
            }

            if (typeof value === 'object' && value !== null) {
                if (value.name) {
                    return String(value.name || '').trim() || formatObject(value);
                }
                if (value.officeNameBng) {
                    return String(value.officeNameBng || '').trim() || formatObject(value);
                }
                if (value.officeNameEng) {
                    return String(value.officeNameEng || '').trim() || formatObject(value);
                }

                const parts = [];
                if (value.name) parts.push(value.name);
                if (value.designation) parts.push(value.designation);
                if (value.officeNameBng) parts.push(`(${value.officeNameBng})`);
                if (parts.length) {
                    return parts.join(' ');
                }

                return formatObject(value);
            }

            return String(value || '–');
        }

        function commonPrefixLength(a, b) {
            const len = Math.min(String(a).length, String(b).length);
            let i = 0;
            while (i < len && a[i] === b[i]) {
                i += 1;
            }
            return i;
        }

        function commonSuffixLength(a, b, prefixLength) {
            const aStr = String(a);
            const bStr = String(b);
            const max = Math.min(aStr.length - prefixLength, bStr.length - prefixLength);
            let i = 0;
            while (i < max && aStr[aStr.length - 1 - i] === bStr[bStr.length - 1 - i]) {
                i += 1;
            }
            return i;
        }

        function highlightChange(oldValue, newValue) {
            const oldStr = String(oldValue || '');
            const newStr = String(newValue || '');

            if (oldStr === newStr) {
                return escapeHtml(newStr);
            }

            const prefixLen = commonPrefixLength(oldStr, newStr);
            const suffixLen = commonSuffixLength(oldStr, newStr, prefixLen);
            const prefix = escapeHtml(newStr.slice(0, prefixLen));
            const changedPart = escapeHtml(newStr.slice(prefixLen, newStr.length - suffixLen));
            const suffix = escapeHtml(newStr.slice(newStr.length - suffixLen));

            return React.createElement(
                React.Fragment,
                null,
                prefix,
                React.createElement('mark', null, changedPart),
                suffix
            );
        }

        function renderDiffValue(oldValue, newValue) {
            const oldObject = typeof oldValue === 'object' && oldValue !== null;
            const newObject = typeof newValue === 'object' && newValue !== null;

            if (oldObject || newObject) {
                return renderValue(newValue);
            }

            return highlightChange(oldValue, newValue);
        }

        function diffRows(oldData, newData) {
            const oldObject = parseJSON(oldData);
            const newObject = parseJSON(newData);

            const isOldObject = oldObject && typeof oldObject === 'object' && !Array.isArray(oldObject);
            const isNewObject = newObject && typeof newObject === 'object' && !Array.isArray(newObject);

            if (!isOldObject || !isNewObject) {
                return [
                    {
                        key: 'value',
                        oldValue: oldObject,
                        newValue: newObject,
                        changed: !isEqual(oldObject, newObject),
                    },
                ];
            }

            const oldKeys = Object.keys(oldObject);
            const newKeys = Object.keys(newObject);
            const keys = Array.from(new Set([...oldKeys, ...newKeys]));

            return keys.map((key) => {
                const oldValue = oldObject[key];
                const newValue = newObject[key];
                return {
                    key,
                    oldValue,
                    newValue,
                    changed: !isEqual(oldValue, newValue),
                };
            });
        }

        function AuditPanel() {
            const [logs, setLogs] = useState([]);
            const [filters, setFilters] = useState({ tables: [], users: [], types: [] });
            const [params, setParams] = useState({ table: '', type: '', search: '' });
            const [page, setPage] = useState(1);
            const [pageSize, setPageSize] = useState(10);
            const [meta, setMeta] = useState({ current_page: 1, last_page: 1, per_page: 10, from: 0, to: 0, total: 0 });
            const [selectedLog, setSelectedLog] = useState(null);
            const [isModalOpen, setIsModalOpen] = useState(false);
            const [loading, setLoading] = useState(true);
            const [error, setError] = useState(null);

            const openDetails = (log) => {
                setSelectedLog(log);
                setIsModalOpen(true);
            };

            const closeDetails = () => {
                setIsModalOpen(false);
            };

            const buildQuery = (overrideParams = {}) => {
                return {
                    page,
                    per_page: pageSize,
                    ...params,
                    ...overrideParams,
                };
            };

            const loadLogs = (overrideParams = {}) => {
                setLoading(true);
                const query = new URLSearchParams(buildQuery(overrideParams));

                fetch('{{ url(config('logtracker.api_prefix', 'api/audit-panel-data')) }}?' + query.toString(), { credentials: 'same-origin' })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Failed to load audit data');
                        }
                        return response.json();
                    })
                    .then((json) => {
                        setLogs(json.data || []);
                        setFilters(json.filters || { tables: [], users: [], types: [] });
                        if (json.meta) {
                            setMeta({
                                current_page: json.meta.current_page || 1,
                                last_page: json.meta.last_page || 1,
                                per_page: json.meta.per_page || 10,
                                from: json.meta.from || 0,
                                to: json.meta.to || 0,
                                total: json.meta.total || 0,
                            });
                            setPage(json.meta.current_page || 1);
                            setPageSize(json.meta.per_page || 10);
                        }
                        setLoading(false);
                    })
                    .catch((err) => {
                        setError(err.message || 'Unable to fetch audit data');
                        setLoading(false);
                    });
            };

            useEffect(() => {
                loadLogs({ page: 1 });
            }, []);

            const handleChange = (event) => {
                const { name, value } = event.target;
                setParams((prev) => ({ ...prev, [name]: value }));
                setPage(1);
            };

            const handleSearch = (event) => {
                event.preventDefault();
                setPage(1);
                loadLogs({ page: 1 });
            };

            const handleReset = () => {
                const resetParams = { table: '', type: '', search: '' };
                setParams(resetParams);
                setPage(1);
                loadLogs({ ...resetParams, page: 1, per_page: pageSize });
            };

            const handlePageSizeChange = (event) => {
                const value = Number(event.target.value) || 10;
                setPageSize(value);
                setPage(1);
                loadLogs({ page: 1, per_page: value });
            };

            const gotoPage = (nextPage) => {
                setPage(nextPage);
                loadLogs({ page: nextPage });
            };

            const selectedDetails = selectedLog ? parseJSON(selectedLog.details || selectedLog.data || {}) : {};
            const selectedNewData = selectedLog ? parseJSON(selectedLog.new_log_details || selectedLog.new_data || {}) : {};
            const detailDiffs = diffRows(selectedDetails, selectedNewData);

            if (loading) {
                return React.createElement('p', { className: 'loading' }, 'Loading audit logs...');
            }

            if (error) {
                return React.createElement('p', { className: 'error' }, error);
            }

            return React.createElement(
                React.Fragment,
                null,
                React.createElement(
                    'form',
                    { className: 'filter-grid', onSubmit: handleSearch },
                    React.createElement(
                        'div',
                        { className: 'filter-item' },
                        React.createElement('label', null, 'Table'),
                        React.createElement(
                            'select',
                            { name: 'table', value: params.table, onChange: handleChange },
                            React.createElement('option', { value: '' }, 'All tables'),
                            ...(filters.tables || []).map((table) => React.createElement('option', { key: table, value: table }, table))
                        )
                    ),
                    React.createElement(
                        'div',
                        { className: 'filter-item' },
                        React.createElement('label', null, 'Log type'),
                        React.createElement(
                            'select',
                            { name: 'type', value: params.type, onChange: handleChange },
                            React.createElement('option', { value: '' }, 'All log types'),
                            ...(filters.types || []).map((type) => React.createElement('option', { key: type, value: type }, type))
                        )
                    ),
                    React.createElement(
                        'div',
                        { className: 'filter-item' },
                        React.createElement('label', null, 'Search'),
                        React.createElement('input', {
                            type: 'search',
                            name: 'search',
                            value: params.search,
                            placeholder: 'Search logs',
                            onChange: handleChange,
                        })
                    ),
                    React.createElement(
                        'div',
                        { className: 'filter-item' },
                        React.createElement('label', null, 'Page size'),
                        React.createElement(
                            'select',
                            { name: 'pageSize', value: pageSize, onChange: handlePageSizeChange },
                            [10, 20, 50, 100].map((size) => React.createElement('option', { key: size, value: size }, `${size} per page`))
                        )
                    ),
                    React.createElement(
                        'div',
                        { className: 'filter-item', style: { alignSelf: 'end', display: 'flex', gap: '10px', flexWrap: 'wrap' } },
                        React.createElement(
                            'button',
                            { type: 'submit', className: 'filter-button' },
                            'Refresh'
                        ),
                        React.createElement(
                            'button',
                            { type: 'button', className: 'filter-button', style: { background: '#6b7280' }, onClick: handleReset },
                            'Reset'
                        )
                    )
                ),
                logs.length === 0
                    ? React.createElement('p', { className: 'loading' }, 'No audit records found for the selected filters.')
                    : React.createElement(
                          React.Fragment,
                          null,
                          React.createElement(
                              'div',
                              { className: 'table-container' },
                              React.createElement(
                                  'table',
                                  null,
                              React.createElement(
                                  'thead',
                                  null,
                                  React.createElement(
                                      'tr',
                                      null,
                                      ['Date', 'Time', 'User', 'Table', 'Type', 'Action'].map((heading) => React.createElement('th', { key: heading }, heading))
                                  )
                              ),
                              React.createElement(
                                  'tbody',
                                  null,
                                  logs.map((log) => {
                                      const badge = statusBadge(log.log_type);
                                      const isSelected = selectedLog && selectedLog.id === log.id;
                                      return React.createElement(
                                          'tr',
                                          {
                                              key: log.id,
                                              className: isSelected ? 'selected-row' : '',
                                              onClick: () => setSelectedLog(log),
                                          },
                                          React.createElement('td', null, log.log_date),
                                          React.createElement('td', null, log.log_time),
                                          React.createElement('td', null, formatAuditText(log.users || log.user_id || '–')),
                                          React.createElement('td', null, log.table_name || '–'),
                                          React.createElement('td', null, React.createElement('span', { className: badge.className }, badge.label)),
                                          React.createElement(
                                              'td',
                                              null,
                                              React.createElement(
                                                  'button',
                                                  {
                                                      type: 'button',
                                                      className: 'filter-button',
                                                      onClick: (event) => {
                                                          event.stopPropagation();
                                                          openDetails(log);
                                                      },
                                                  },
                                                  'Details'
                                              )
                                          )
                                      );
                                  })
                              ))
                          ),
                          React.createElement(
                              'div',
                              { className: 'filter-row', style: { justifyContent: 'space-between', marginTop: '16px' } },
                              React.createElement(
                                  'div',
                                  null,
                                  `Showing ${meta.from || 0} - ${meta.to || 0} of ${meta.total || 0} records`
                              ),
                              React.createElement(
                                  'div',
                                  null,
                                  React.createElement(
                                      'button',
                                      {
                                          type: 'button',
                                          className: 'filter-button',
                                          style: { background: '#9ca3af', minWidth: '100px' },
                                          onClick: () => gotoPage(Math.max(meta.current_page - 1, 1)),
                                          disabled: meta.current_page <= 1,
                                      },
                                      'Previous'
                                  ),
                                  React.createElement(
                                      'button',
                                      {
                                          type: 'button',
                                          className: 'filter-button',
                                          style: { marginLeft: '12px', minWidth: '100px' },
                                          onClick: () => gotoPage(Math.min(meta.current_page + 1, Math.max(1, meta.last_page))),
                                          disabled: meta.current_page >= meta.last_page,
                                      },
                                      'Next'
                                  )
                              )
                          )
                      ),
                isModalOpen && React.createElement(
                    'div',
                    { className: 'modal-overlay', onClick: closeDetails },
                    React.createElement(
                        'div',
                        { className: 'modal', onClick: (event) => event.stopPropagation() },
                        React.createElement(
                            'div',
                            { className: 'modal-header' },
                            React.createElement('h2', { className: 'modal-title' }, 'Audit log details'),
                            React.createElement(
                                'button',
                                { type: 'button', className: 'modal-close', onClick: closeDetails },
                                'Close'
                            )
                        ),
                        React.createElement(
                            'div',
                            { className: 'modal-meta' },
                            React.createElement(
                                'div',
                                { className: 'modal-meta-item' },
                                React.createElement('div', { className: 'modal-meta-label' }, 'User'),
                                React.createElement('div', { className: 'modal-meta-value' }, formatAuditText(selectedLog.users || selectedLog.user_id || '–'))
                            ),
                            React.createElement(
                                'div',
                                { className: 'modal-meta-item' },
                                React.createElement('div', { className: 'modal-meta-label' }, 'Table'),
                                React.createElement('div', { className: 'modal-meta-value' }, selectedLog.table_name || '–')
                            ),
                            React.createElement(
                                'div',
                                { className: 'modal-meta-item' },
                                React.createElement('div', { className: 'modal-meta-label' }, 'Type'),
                                React.createElement('div', { className: 'modal-meta-value' }, selectedLog.log_type || '–')
                            ),
                            React.createElement(
                                'div',
                                { className: 'modal-meta-item' },
                                React.createElement('div', { className: 'modal-meta-label' }, 'Date'),
                                React.createElement('div', { className: 'modal-meta-value' }, selectedLog.log_date || '–')
                            )
                        ),
                        React.createElement(
                            'div',
                            { className: 'modal-section' },
                            React.createElement('h3', null, 'Old / New data comparison'),
                            detailDiffs.length === 0
                                ? React.createElement('p', { className: 'loading' }, 'No value changes detected.')
                                : React.createElement(
                                      'table',
                                      { className: 'modal-table' },
                                      React.createElement(
                                          'thead',
                                          null,
                                          React.createElement(
                                              'tr',
                                              null,
                                              ['Field', 'Old value', 'New value'].map((heading) => React.createElement('th', { key: heading }, heading))
                                          )
                                      ),
                                      React.createElement(
                                          'tbody',
                                          null,
                                          detailDiffs.map((row) =>
                                              React.createElement(
                                                  'tr',
                                                  { key: row.key, className: row.changed ? 'changed' : '' },
                                                  React.createElement('td', null, humanizeKey(row.key)),
                                                  React.createElement(
                                                      'td',
                                                      { className: 'modal-value' },
                                                      renderValue(row.oldValue)
                                                  ),
                                                  React.createElement(
                                                      'td',
                                                      { className: 'modal-value' },
                                                      renderDiffValue(row.oldValue, row.newValue)
                                                  )
                                              )
                                          )
                                      )
                                  )
                        )
                    )
                )
            );
        }

        const root = ReactDOM.createRoot(document.getElementById('audit-panel-root'));
        root.render(React.createElement(AuditPanel));
    </script>
</body>
</html>
