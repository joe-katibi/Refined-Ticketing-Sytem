<!-- Notification Scripts -->
<script src="{{ asset('modules/escalations/js/notifications.js') }}"></script>
<script>
    // Set justLoggedIn flag on login
    @if(session('login_success'))
        sessionStorage.setItem('justLoggedIn', 'true');
    @endif
</script>
<!-- End Notification Scripts -->

