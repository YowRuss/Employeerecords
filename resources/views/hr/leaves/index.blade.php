@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="text-header-blue fw-bold m-0"><i class="bi bi-check-circle-fill me-2 text-header-blue"></i> HR Leave Approvals</h4>
            <p class="text-muted small m-0">Review, approve, or deny employee leave applications, and manage leave credits.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn fw-bold shadow-sm" style="background-color: #1A3E6F; color: white;" data-bs-toggle="modal" data-bs-target="#configureRatesModal">
                <i class="bi bi-gear me-1"></i> Configure Rates
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-light border shadow-sm btn-sm fw-bold text-muted px-3 d-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success shadow-sm border-0 rounded-3">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger shadow-sm border-0 rounded-3">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs mb-4 staff-tabs" id="hrLeaveTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests" type="button" role="tab" style="color: #1A3E6F;">
                <i class="bi bi-inbox me-1"></i> Leave Requests
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-secondary fw-bold" id="balances-tab" data-bs-toggle="tab" data-bs-target="#balances" type="button" role="tab">
                <i class="bi bi-wallet2 me-1"></i> Employee Balances
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link text-secondary fw-bold" id="seminars-tab" data-bs-toggle="tab" data-bs-target="#seminars" type="button" role="tab">
                <i class="bi bi-award me-1"></i> Seminar Approvals
            </button>
        </li>
    </ul>

    <div class="tab-content" id="hrLeaveTabsContent">
        @include('hr.leaves.partials.requests_tab')
        @include('hr.leaves.partials.balances_tab')
        @include('hr.leaves.partials.seminars_tab')
    </div>
</div>

@include('hr.leaves.partials.settings_modal')

@if($errors->any() || session('active_tab') === 'balances' || request()->hasAny(['teaching_page', 'non_teaching_page', 'unassigned_page', 'balance_tab']))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var balancesTab = document.getElementById('balances-tab');
        if (balancesTab && window.bootstrap) {
            window.bootstrap.Tab.getOrCreateInstance(balancesTab).show();
        }

        var balanceTabs = {
            'teaching': 'pills-teaching-tab',
            'non-teaching': 'pills-non-teaching-tab',
            'unassigned': 'pills-unassigned-tab'
        };
        var subTabId = balanceTabs[@json(request('balance_tab'))];
        var subTab = subTabId ? document.getElementById(subTabId) : null;
        if (subTab && window.bootstrap) {
            window.bootstrap.Tab.getOrCreateInstance(subTab).show();
        }
    });
</script>
@endif
@endsection