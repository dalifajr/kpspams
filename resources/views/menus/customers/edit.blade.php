@php($title = 'Edit Pelanggan')
@php($bodyClass = 'page-customer-admin')
@extends('layouts.app')

@section('content')
    <div class="customer-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('menu.customers.show', $customer) }}" class="chip-back" aria-label="Kembali ke detail pelanggan">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Edit {{ $customer->name }}</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="customer-detail-card">
            @include('menus.customers.partials.form', [
                'action' => route('menu.customers.update', $customer),
                'method' => 'PUT',
                'areas' => $areas,
                'golongans' => $golongans,
                'customer' => $customer,
            ])
        </div>
    </div>

    <x-bottom-nav />
@endsection
