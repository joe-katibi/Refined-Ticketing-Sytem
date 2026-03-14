@php
$containerNav = ($configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');
@endphp

<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
  <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{$containerNav}}">
      @endif

      <!--  Brand demo (display only for navbar-full and hide on below xl) -->
      @if(isset($navbarFull))
      <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{url('/')}}" class="app-brand-link gap-2">
          <span class="app-brand-logo demo">
            @include('_partials.macros',["height"=>20])
          </span>
          <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
        </a>
      </div>
      @endif

      <!-- ! Not required for layout-without-menu -->
      @if(!isset($navbarHideToggle))
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
          <i class="ti ti-menu-2 ti-sm"></i>
        </a>
      </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

        @if($configData['hasCustomizer'] == true)
        <!-- Style Switcher -->
        <div class="navbar-nav align-items-center">
          <div class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
              <i class='ti ti-sun ti-md'></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-start dropdown-styles">
              <li>
                <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                  <span class="align-middle"><i class='ti ti-sun me-2'></i>Light</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                  <span class="align-middle"><i class="ti ti-moon me-2"></i>Dark</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                  <span class="align-middle"><i class="ti ti-device-desktop me-2"></i>System</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
        <!--/ Style Switcher -->
        @endif

        <ul class="navbar-nav flex-row align-items-center ms-auto">

          <!-- User -->
          <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
              <div class="avatar avatar-online">
                <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}" alt class="h-auto rounded-circle">
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                  <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                      <div class="avatar avatar-online">
                        <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}" alt class="h-auto rounded-circle">
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <span class="fw-medium d-block">
                        @if (Auth::check())
                        {{ Auth::user()->name }}
                        @else
                        John Doe
                        @endif
                      </span>
                      <small class="text-muted">Admin</small>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <a class="dropdown-item" href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                  <i class="ti ti-user-check me-2 ti-sm"></i>
                  <span class="align-middle">My Profile</span>
                </a>
              </li>
              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <div class="dropdown-item">
                  <div class="d-flex align-items-center">
                    <i class="ti ti-typography me-2 ti-sm"></i>
                    <span class="align-middle me-2">Font Size:</span>
                    <select class="form-select form-select-sm" id="fontSizeSelector" style="width: auto;">
                      <option value="text-xs" {{ $configData['fontSize'] === 'text-xs' ? 'selected' : '' }}>Small (12px)</option>
                      <option value="text-sm" {{ $configData['fontSize'] === 'text-sm' ? 'selected' : '' }}>Medium (13px)</option>
                      <option value="text-base" {{ $configData['fontSize'] === 'text-base' ? 'selected' : '' }}>Large (14px)</option>
                    </select>
                  </div>
                </div>
              </li>
              <li>
                <div class="dropdown-divider"></div>
              </li>
              @if (Auth::check())
              <li>
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class='ti ti-logout me-2'></i>
                  <span class="align-middle">Logout</span>
                </a>
              </li>
              <form method="POST" id="logout-form" action="{{ route('logout') }}">
                @csrf
              </form>
              @else
              <li>
                <a class="dropdown-item" href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                  <i class='ti ti-login me-2'></i>
                  <span class="align-middle">Login</span>
                </a>
              </li>
              @endif
            </ul>
          </li>
          <!--/ User -->
        </ul>
      </div>

      @if(!isset($navbarDetached))
    </div>
    @endif
  </nav>
  <!-- / Navbar -->

  <!-- Font Size Selector JavaScript -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const fontSizeSelector = document.getElementById('fontSizeSelector');
    
    // Function to get cookie value
    function getCookie(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
      return null;
    }
    
    // Function to apply font size to HTML element
    function applyFontSize(fontSize) {
      const htmlElement = document.documentElement;
      // Remove existing font size classes
      htmlElement.classList.remove('text-xs', 'text-sm', 'text-base');
      // Add new font size class
      htmlElement.classList.add(fontSize);
    }
    
    // On page load, ensure font size is applied from cookie or default
    const savedFontSize = getCookie('fontSize') || 'text-sm';
    applyFontSize(savedFontSize);
    
    if (fontSizeSelector) {
      // Set the selector to match the current font size
      fontSizeSelector.value = savedFontSize;
      
      fontSizeSelector.addEventListener('change', function() {
        const selectedFontSize = this.value;
        
        // Show loading state
        const originalText = this.options[this.selectedIndex].text;
        this.options[this.selectedIndex].text = 'Applying...';
        this.disabled = true;
        
        // Send AJAX request to update font size
        fetch('{{ route('font.update') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            fontSize: selectedFontSize
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Apply font size immediately using our helper function
            applyFontSize(selectedFontSize);
            
            // Show success message (optional)
            if (typeof toastr !== 'undefined') {
              toastr.success('Font size updated successfully!');
            }
          } else {
            // Show error message
            if (typeof toastr !== 'undefined') {
              toastr.error('Failed to update font size. Please try again.');
            }
            console.error('Font size update failed:', data);
          }
        })
        .catch(error => {
          console.error('Error updating font size:', error);
          if (typeof toastr !== 'undefined') {
            toastr.error('An error occurred while updating font size.');
          }
        })
        .finally(() => {
          // Restore original state
          this.options[this.selectedIndex].text = originalText;
          this.disabled = false;
        });
      });
    }
  });
  </script>

