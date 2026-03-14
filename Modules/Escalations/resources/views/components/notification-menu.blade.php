<li class="nav-item dropdown notifications-menu">
    <a href="#" class="nav-link" data-toggle="dropdown" id="notification-dropdown">
        <i class="far fa-bell"></i>
        <span class="badge badge-danger navbar-badge notification-count" style="display: none;">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header"><span class="notification-count">0</span> Notifications</span>
        <div class="dropdown-divider"></div>
        <div class="notification-items">
            <!-- Notification items will be loaded here -->
            <a href="#" class="dropdown-item text-center">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </a>
        </div>
        <div class="dropdown-divider"></div>
        <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">See All Notifications</a>
    </div>
</li>

