@extends('layouts.app')

@section('content')
    <new-nota-component :role_id="{{auth()->user()->role_id}}" :registersall="{{$notes}}"></new-nota-component>
@endsection