@extends('errors.layout')

@section('title', 'Error del servidor')
@section('code')5<span>0</span>0@endsection
@section('heading', 'Algo salió mal')
@section('message', 'Ocurrió un error inesperado y no pudimos completar la operación. Probá de nuevo en unos minutos; si sigue pasando, avisá al equipo de sistemas.')

@section('actions')
    <button type="button" class="error-button error-button--primary" onclick="window.location.reload()">Reintentar</button>
    <a href="{{ url('/') }}" class="error-button error-button--ghost">Ir al inicio</a>
@endsection
