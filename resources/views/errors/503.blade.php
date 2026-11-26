@extends('errors.layout')

@section('title', 'Sistema en mantenimiento')
@section('code')5<span>0</span>3@endsection
@section('heading', 'Estamos haciendo mejoras')
@section('message', 'PRODENYA está en mantenimiento por unos minutos. No se perdió ningún dato: volvé a intentar en un rato y vas a poder seguir trabajando normalmente.')

@section('actions')
    <button type="button" class="error-button error-button--primary" onclick="window.location.reload()">Reintentar</button>
@endsection
