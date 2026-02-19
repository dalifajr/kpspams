@props(['menu', 'number' => null])

<a href="{{ $menu['url'] }}" class="menu-card" style="--menu-color: {{ $menu['color'] }}">
    @if ($number)
        <span class="menu-number">{{ $number }}</span>
    @endif
    <span class="menu-icon material-symbols-rounded">{{ $menu['icon'] }}</span>
    <span class="menu-label">{{ $menu['label'] }}</span>
</a>
