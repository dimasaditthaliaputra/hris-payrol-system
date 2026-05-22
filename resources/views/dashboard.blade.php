@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Selamat Datang, {{ auth()->user()->name }}!</h3>
            </div>
            <div class="card-body">
                <p>Anda berhasil masuk ke sistem HRIS Payroll.</p>
                <p>Role Anda saat ini: <strong>{{ auth()->user()->role->display_name ?? '-' }}</strong></p>
            </div>
        </div>
    </div>
</div>
@endsection
