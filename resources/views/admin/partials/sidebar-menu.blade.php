@foreach($menus as $menu)

{{-- 🔥 Skip empty parent menus --}}
@if(!$menu->route && $menu->childrenRecursive->isEmpty() && !$menu->url)
@continue
@endif

<li>
    <a href="{{ $menu->route ? route($menu->route) : 'javascript:void(0);' }}">
        @if($menu->icon)
        <i class="{{ $menu->icon }}"></i>
        @endif

        <span>{{ $menu->name }}</span>

        @if($menu->childrenRecursive->isNotEmpty())
        <span class="menu-arrow"></span>
        @endif
    </a>

    @if($menu->childrenRecursive->isNotEmpty())
    <ul class="nav-second-level" aria-expanded="false">
        @include('admin.partials.sidebar-menu', [
        'menus' => $menu->childrenRecursive
        ])
    </ul>
    @endif
</li>
@endforeach