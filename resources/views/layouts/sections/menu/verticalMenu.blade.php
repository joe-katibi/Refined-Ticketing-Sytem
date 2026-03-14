@php
$configData = Helper::appClasses();
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- ! Hide app brand if navbar-full -->
  @if(!isset($navbarFull))
  <div class="app-brand demo">
    <a href="{{url('/home')}}" class="app-brand-link">
      <span class="app-brand-logo demo">
      <img src="{{ asset('assets/img/logo/logo.png') }}" alt="Logo" style="height: 20px;">
      </span>
      <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
      <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
    </a>
  </div>
  @endif


  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">

    @foreach ($menuData['menu'] as $menu)
    {{-- adding active and open class if child is active --}}

    {{-- menu headers --}}
    @if (isset($menu->menuHeader))
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
    </li>

    @else

{{-- active menu method --}}
@php
    $activeClass = null;
    $currentRouteName = Route::currentRouteName();

    // Check if the current route matches the menu slug
    if ($currentRouteName === $menu->slug) {
        $activeClass = 'active';
    }
    // Check submenu for the active class
    elseif (isset($menu->submenu)) {
        if (gettype($menu->slug) === 'array') {
            // Handle multiple slugs for the top-level menu
            foreach($menu->slug as $slug) {
                if (str_contains($currentRouteName, $slug) && strpos($currentRouteName, $slug) === 0) {
                    $activeClass = 'active open';
                    break;
                }
            }
        } else {
            // Handle single slug for the top-level menu
            if (str_contains($currentRouteName, $menu->slug) && strpos($currentRouteName, $menu->slug) === 0) {
                $activeClass = 'active open';
            }
        }

        // Check each submenu item for the active class
        foreach ($menu->submenu as $submenu) {
            if (str_contains($currentRouteName, $submenu->slug) && strpos($currentRouteName, $submenu->slug) === 0) {
                $activeClass = 'active open'; // Set the active class for submenus
                break;
            }
        }
    }

    // Check permissions
    $showMenuItem = true;
    if (isset($menu->permission)) {
        $showMenuItem = in_array($menu->permission, $menuData['userPermissions']);
    }
@endphp

 @if($showMenuItem)
    {{-- main menu --}}
    <li class="menu-item {{$activeClass}}">
      <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}" class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
        @isset($menu->icon)
        <i class="{{ $menu->icon }}"></i>
        @endisset
        <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
        @isset($menu->badge)
        <div class="badge badge-xs bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>

        @endisset
      </a>

      {{-- submenu --}}
      @isset($menu->submenu)
      @include('layouts.sections.menu.submenu',['menu' => $menu->submenu])
      @endisset
    </li>
    @endif
    @endif
    @endforeach
  </ul>

</aside>

