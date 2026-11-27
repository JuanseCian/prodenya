
@extends('errors.layout')

@section('title', 'Página no encontrada')

@section('code')
    4<span>0</span>4
@endsection

@section('heading', 'No encontramos lo que buscás')

@section('message', 'La dirección puede estar mal escrita, o la página se movió o ya no existe. Volvé al inicio para seguir trabajando.')

@section('actions')
    @include('errors.partials.acciones')
@endsection
