@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold">Dashboard Admin</h2>
    <p>Total: {{ $total }}</p>
    <p>Menunggu: {{ $menunggu }}</p>
    <p>Diproses: {{ $diproses }}</p>
    <p>Selesai: {{ $selesai }}</p>
    <a href="{{ route('logout') }}" class="btn btn-danger">Logout</a>
</div>
@endsection