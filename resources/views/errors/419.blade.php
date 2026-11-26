@extends('errors.layout')

@section('title', 'Sesión vencida')
@section('code')4<span>1</span>9@endsection
@section('heading', 'Tu sesión venció')
@section('message', 'Pasó mucho tiempo sin actividad y, por seguridad, la página expiró. Volvé a cargarla o ingresá de nuevo para continuar.')

@section('actions')
    <button type="button" class="error-button error-button--primary" onclick="window.location.reload()">Recargar página</button>
    <a href="{{ Route::has('login') ? route('login') : url('/') }}" class="error-button error-button--ghost">Ingresar de nuevo</a>
@endsection
