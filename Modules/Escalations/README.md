# Escalations Module

This module provides SLA tracking, dashboard metrics, and a notification system for escalations.

## Features

- **SLA Tracking**: Automatic 5-minute SLA calculation and breach detection
- **Dashboard**: Real-time metrics per sub-department (total received, closed within/outside SLA, backlog)
- **Notification System**: Real-time notifications for ticket creators and editors
- **Scheduled Checks**: Automatic SLA breach detection and notifications

## Setup Instructions

1. Run the migrations:
   ```
   php artisan module:migrate Escalations
   ```

2. Publish the assets:
   ```
   php artisan module:publish Escalations
   ```

3. Compile the assets:
   ```
   npm run dev
   ```

## Testing the Implementation

### Testing Notifications

Use the test command to generate sample notifications:

```
php artisan escalations:test-notifications --user=1 --type=all
```

Options:
- `--user=ID`: User ID to create notifications for (required)
- `--type=TYPE`: Type of notification to create (default: all)
  - Available types: created, updated, closed, assigned, sla_warning, sla_breach, all

### Testing SLA Checker

Run the SLA checker command to check for breaches:

```
php artisan escalations:check-sla
```

### Dashboard Access

Access the dashboard at:
- Main dashboard: `/escalations/dashboard`
- Sub-department details: `/escalations/dashboard/{sub_department_id}`

### Notifications Access

- Notification menu: Automatically injected into the navbar
- Notification list: `/escalations/notifications`

## Implementation Details

### SLA Calculation

- SLA deadline is set to 5 minutes after escalation creation
- SLA breach is flagged if escalation is closed after the deadline
- SLA warning notifications are sent when approaching the deadline

### Notification Types

- Escalation created
- Escalation updated
- Escalation closed
- Escalation assigned
- SLA warning (approaching breach)
- SLA breach

### Dashboard Metrics

- Total received escalations
- Total closed escalations
- Closed within SLA
- Closed outside SLA
- Current backlog
- SLA compliance percentage

## Scheduled Tasks

The module automatically registers a scheduled task to check for SLA breaches every minute. Ensure your Laravel scheduler is running:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```
