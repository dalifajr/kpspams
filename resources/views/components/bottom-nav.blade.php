@php
    $isDashboard = request()->routeIs('dashboard');
    $isProfile = request()->routeIs('profile.show');
    $isCatatMeter = request()->routeIs('catat-meter.*');
@endphp
<nav class="bottom-nav">
    <a href="{{ route('dashboard') }}" @class(['is-active' => $isDashboard]) @if($isDashboard) aria-current="page" @endif>
        <span class="material-symbols-rounded">home</span>
        <span>Home</span>
    </a>
    <a href="{{ route('catat-meter.index') }}" @class(['is-active' => $isCatatMeter]) @if($isCatatMeter) aria-current="page" @endif>
        <span class="material-symbols-rounded">speed</span>
        <span>Catat Meter</span>
    </a>
    <a href="{{ route('profile.show') }}" @class(['is-active' => $isProfile]) @if($isProfile) aria-current="page" @endif>
        <span class="material-symbols-rounded">person</span>
        <span>Profile</span>
    </a>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">
            <span class="material-symbols-rounded">logout</span>
            <span>Logout</span>
        </button>
    </form>
</nav>
