@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold text-accent mb-4">Employee Profile: {{ $employee->first_name }} {{ $employee->last_name }}</h4>
    
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <p><strong>Full Name:</strong> {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}</p>
            <p><strong>Position:</strong> {{ $employee->position_name ?? 'Not Assigned' }}</p>
            <p><strong>Username:</strong> {{ $employee->username }}</p>
            <!-- You can add more profile details here -->
        </div>
    </div>
</div>
@endsection