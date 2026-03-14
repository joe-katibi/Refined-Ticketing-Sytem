<ul class="menu-sub">
  @if (isset($menu))
    @foreach ($menu as $submenu)
    @php
                  $showSubMenuItem = true;
                  if (isset($submenu->permission)) {
                      $showSubMenuItem = in_array($submenu->permission, $menuData['userPermissions']);
                  }
    @endphp

    {{-- active menu method --}}
    @if($showSubMenuItem)
    @php
    $activeClass = '';
    $currentRouteName = Route::currentRouteName();

    // Check if the current route matches the submenu's slug
    if ($currentRouteName === $submenu->slug) {
        $activeClass = 'active';
    } elseif (isset($submenu->submenu)) {
        // Check if any child submenu matches the current route
        foreach ($submenu->submenu as $childSubmenu) {
            if ($currentRouteName === $childSubmenu->slug) {
                $activeClass = 'active open';
                break;
            }
        }
    }
    @endphp



      <li class="menu-item {{$activeClass}}">
        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}" class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
          @if (isset($submenu->icon))
          <i class="{{ $submenu->icon }}"></i>
          @endif
          <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
          @isset($submenu->badge)
            <div class="badge badge-xs bg-{{ $submenu->badge[0] }} rounded-pill ms-auto">{{ $submenu->badge[1] }}</div>
          @endisset
        </a>

        {{-- submenu --}}
        @if (isset($submenu->submenu))
          @include('layouts.sections.menu.submenu',['menu' => $submenu->submenu])
        @endif
      </li>
      @endif
    @endforeach

  @endif
</ul>
<style>

.menu-item.active > a {
    background-color: orange;
    color: white; /* Optional: Adjust text color for better contrast */
}

</style>

