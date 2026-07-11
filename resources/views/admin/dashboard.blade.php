@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container py-5">
    <h2 class="fw-bold mb-4">Dashboard Admin</h2>

    <p>Total Laporan: {{ $total }}</p>
    <p>Menunggu: {{ $menunggu }}</p>
    <p>Diproses: {{ $diproses }}</p>
    <p>Selesai: {{ $selesai }}</p>

    {{-- Tampilkan chart data jika perlu --}}
    <pre>{{ json_encode($chartData, JSON_PRETTY_PRINT) }}</pre>

    <div class="mt-4">
        <a href="{{ route('logout') }}" class="btn btn-danger">Logout</a>
    </div>
</div>
@endsection