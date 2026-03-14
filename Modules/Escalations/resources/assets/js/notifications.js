/**
 * Escalation Notification System
 * Handles real-time notifications and toast messages
 */

$(function() {
    // Initialize notification system
    initNotificationSystem();
    
    // Check for new notifications every 30 seconds
    setInterval(checkNotifications, 30000);
    
    // Initial check for notifications
    checkNotifications();
});

/**
 * Initialize the notification system
 */
function initNotificationSystem() {
    // Initialize toastr options
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    // Show welcome notification on login
    if (sessionStorage.getItem('justLoggedIn') === 'true') {
        setTimeout(function() {
            toastr.info('Welcome back! Checking for new notifications...');
            sessionStorage.removeItem('justLoggedIn');
        }, 1000);
    }
}

/**
 * Check for new notifications
 */
function checkNotifications() {
    // Get unread count
    $.ajax({
        url: '/notifications/count',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            updateNotificationCount(data.count);
        },
        error: function(xhr, status, error) {
            console.error('Error fetching notification count:', error);
        }
    });
    
    // Get recent notifications
    $.ajax({
        url: '/notifications/recent',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            updateNotificationMenu(data.notifications);
            showNewNotifications(data.notifications);
        },
        error: function(xhr, status, error) {
            console.error('Error fetching recent notifications:', error);
        }
    });
}

/**
 * Update the notification count badge
 */
function updateNotificationCount(count) {
    const countElements = $('.notification-count');
    
    if (count > 0) {
        countElements.text(count).show();
    } else {
        countElements.hide();
    }
}

/**
 * Update the notification dropdown menu
 */
function updateNotificationMenu(notifications) {
    const container = $('.notification-items');
    container.empty();
    
    if (notifications.length === 0) {
        container.append(`
            <a href="#" class="dropdown-item text-center">
                <i class="fas fa-check-circle"></i> No new notifications
            </a>
        `);
        return;
    }
    
    // Add each notification to the menu
    notifications.forEach(function(notification) {
        let icon = 'fas fa-bell';
        let badgeClass = 'badge-info';
        
        // Set icon and badge class based on notification type
        switch(notification.type) {
            case 'create':
                icon = 'fas fa-plus-circle';
                badgeClass = 'badge-success';
                break;
            case 'update':
                icon = 'fas fa-edit';
                badgeClass = 'badge-info';
                break;
            case 'assign':
                icon = 'fas fa-user-check';
                badgeClass = 'badge-primary';
                break;
            case 'close':
                icon = 'fas fa-check-circle';
                badgeClass = 'badge-secondary';
                break;
            case 'sla_breach':
                icon = 'fas fa-exclamation-triangle';
                badgeClass = 'badge-danger';
                break;
        }
        
        // Create the notification item
        const item = `
            <a href="/escalation/${notification.escalation_id}" class="dropdown-item">
                <i class="${icon} mr-2"></i>
                <span class="badge ${badgeClass} mr-1">${notification.type}</span>
                ${notification.message}
                <span class="float-right text-muted text-sm">${timeAgo(notification.created_at)}</span>
            </a>
            <div class="dropdown-divider"></div>
        `;
        
        container.append(item);
    });
}

/**
 * Show toast notifications for new notifications
 * Uses local storage to track which notifications have been shown
 */
function showNewNotifications(notifications) {
    // Get the IDs of notifications we've already shown
    const shownNotifications = JSON.parse(localStorage.getItem('shownNotifications') || '[]');
    
    // Filter to only show notifications we haven't shown yet
    const newNotifications = notifications.filter(notification => {
        return !shownNotifications.includes(notification.id);
    });
    
    // Show toast for each new notification
    newNotifications.forEach(notification => {
        let toastType = 'info';
        
        // Set toast type based on notification type
        switch(notification.type) {
            case 'create':
                toastType = 'success';
                break;
            case 'update':
                toastType = 'info';
                break;
            case 'assign':
                toastType = 'info';
                break;
            case 'close':
                toastType = 'success';
                break;
            case 'sla_breach':
                toastType = 'error';
                break;
        }
        
        // Show the toast
        toastr[toastType](notification.message, 'Notification');
        
        // Add to shown notifications
        shownNotifications.push(notification.id);
    });
    
    // Save the updated list of shown notifications
    localStorage.setItem('shownNotifications', JSON.stringify(shownNotifications));
}

/**
 * Format a timestamp as a relative time string (e.g., "5 minutes ago")
 */
function timeAgo(timestamp) {
    const now = new Date();
    const date = new Date(timestamp);
    const seconds = Math.floor((now - date) / 1000);
    
    let interval = Math.floor(seconds / 31536000);
    if (interval >= 1) {
        return interval + " year" + (interval === 1 ? "" : "s") + " ago";
    }
    
    interval = Math.floor(seconds / 2592000);
    if (interval >= 1) {
        return interval + " month" + (interval === 1 ? "" : "s") + " ago";
    }
    
    interval = Math.floor(seconds / 86400);
    if (interval >= 1) {
        return interval + " day" + (interval === 1 ? "" : "s") + " ago";
    }
    
    interval = Math.floor(seconds / 3600);
    if (interval >= 1) {
        return interval + " hour" + (interval === 1 ? "" : "s") + " ago";
    }
    
    interval = Math.floor(seconds / 60);
    if (interval >= 1) {
        return interval + " minute" + (interval === 1 ? "" : "s") + " ago";
    }
    
    return "just now";
}
