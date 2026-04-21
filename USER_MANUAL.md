# Logtracker User Manual

Welcome to the modernized **Logtracker** system. This guide will help you navigate and utilize the premium audit logging tools designed to provide total visibility into your application's data activity.

---

## 1. Getting Started

### Accessing the Suite
The LogTracker Suite is usually accessible at `/audit-panel`.
1. **Audit Trail**: View detailed database activity logs.
2. **Analytics Insights**: Advanced charts and trend analysis.
3. **System Logs**: Human-readable view of `laravel.log`.

### Sidebar Navigation
The professional sidebar allows you to switch between views instantly. It can be collapsed using the chevron icon in the top left for a focused workspace.

---

## 2. Dashboard Overview

### Activity Heatmap
The **Activity Heatmap** provides a 30-day "bird's eye view" of your system's stability.
*   **Intensity**: Darker emerald squares indicate higher volumes of database changes on that specific day.
*   **Interactivity**: Hover over any square to see the exact date and volume of logs for that day.
*   **Trend Tracking**: Use this to quickly spot unusual spikes in activity.

### Global Search
Use the **Global Search** bar to find specific records instantly. You can search by:
*   **User Information**: Name or Email of the person who performed the action.
*   **Table Name**: Target specific database tables (e.g., `users`, `invoices`).
*   **Context**: Search for specific **URLs** or **Route Names**.
*   **Content**: Search for specific values *within* the modified data.

### Date Range Picker
The **Unified Date Picker** allows you to focus on a specific window of time.
1. Click the date field.
2. Select your start date and end date on the calendar.
3. The dashboard will automatically refresh to show logs within that period.

---

## 3. Reviewing Audit Details

Click the **View Details** (eye icon) on any log entry to open the high-fidelity metadata modal.

### Metadata Cards
*   **IP Address**: The network origin of the request.
*   **User Agent**: The browser and device used.
*   **Source URL**: The exact page where the action occurred.
*   **Route Name**: The internal Laravel route responsible for the change.

### Data Comparison
The detail view provides a high-fidelity comparison with **Visual Highlighting**:
*   **Previous Data (Light Red)**: What the record looked like before the change.
*   **Modified Data (Light Green + Bold)**: The new values after the update.
*   **JSON/Object Rendering**: Complex data (like arrays or formatted dates) is now clearly readable, automatically stringified to prevent rendering errors.
*   *Note: Sensitive fields (like passwords) are automatically masked.*

---

## 4. System Logs
The **System Log Viewer** transforms the raw logs into a high-fidelity diagnostic tool.
*   **Multi-file Support**: Switch between today's `laravel.log` and past daily logs (e.g., `laravel-2026-03-14.log`) using the file selector in the top toolbar.
*   **Level Badges**: Errors are highlighted in Rose, Warnings in Amber, and Info in Blue.
*   **Search and Filter**: Filter logs by level or search by keyword within the message and stack trace.
*   **Management Actions**:
    *   **Single Delete**: Remove individual noisy entries (trash icon).
    *   **Bulk Delete**: Select multiple entries and remove them in one click.
    *   **Clear All**: Wipe the currently selected log file (zero-byte clear).
*   **Collapsible Stack Traces**: Deep-dive into technical errors without cluttering your view.

---

## 5. Maintenance (For Administrators)

Logtracker is built to be "Lean and Clean."

### Log Pruning
To keep the database running fast, the system automatically removes old logs based on your **Retention Policy** (Default: 90 days).
*   **Command**: `php artisan logtracker:prune`
*   **Recommendation**: This should be scheduled to run daily via the Laravel Task Scheduler.

### NoSQL Synchronization
For high-security environments, logs can be mirrored to a MongoDB instance.
*   **Command**: `php artisan logtracker:sync-mongo`
*   **Benefit**: Provides an immutable backup of all audit logs outside your primary SQL database.

---

## 6. Troubleshooting

| Issue | Potential Solution |
| :--- | :--- |
| **"Unauthorized Access"** | Your User ID must be added to the `LOGTRACKER_ALLOWED_IDS` in the environment file. |
| **Missing URL/Routes** | Ensure the log entries were created after the Phase 5 modernization update. |
| **Slow Dashboard Load** | Ensure all database migrations/indexes have been applied with `php artisan migrate`. |

---
*© 2026 Logtracker Audit System - Premium Enterprise Edition*
