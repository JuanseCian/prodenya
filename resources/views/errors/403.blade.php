@extends('errors.layout')

@section('title', 'Acceso restringido')
@section('code')4<span>0</span>3@endsection
@section('heading', 'No tenés permiso para ver esta página')
@section('message', 'Tu usuario no tiene acceso a esta sección. Si creés que deberías poder entrar, pedile a un administrador que revise tus permisos.')

@section('actions')
    @include('errors.partials.acciones')
@endsection
