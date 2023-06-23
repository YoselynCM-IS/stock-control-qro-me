@extends('layouts.app')

@section('content')
    <libros-component :role_id="{{auth()->user()->role_id}}"></libros-component>
@endsection