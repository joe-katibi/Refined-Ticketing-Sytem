{{-- Toast Notification Component --}}
<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999; margin-top: 60px;">
    {{-- Toast notifications will be dynamically inserted here --}}
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configure toastr options
        toastr.options = {
            closeButton: true,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "5000",  // Show for 5 seconds
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            // Ensure toast appears above other elements
            escapeHtml: false,
            target: 'body'
        };

        // Check if there's a toast notification to display
        @if(session('toast_show'))
            var type = "{{ session('toast_type') }}";
            var message = "{{ session('toast_message') }}";
            
            // Display the toast notification
            switch(type) {
                case 'success':
                    toastr.success(message);
                    break;
                case 'info':
                    toastr.info(message);
                    break;
                case 'warning':
                    toastr.warning(message);
                    break;
                case 'error':
                    toastr.error(message);
                    break;
                default:
                    toastr.info(message);
            }
        @endif
    });
</script>
@endpush

