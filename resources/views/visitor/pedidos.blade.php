@extends('layouts.app')

@section('content')
    <pedidos-component :role_id="{{auth()->user()->role_id}}" 
            :registers="{{$pedidos}}"></pedidos-component>
@endsection