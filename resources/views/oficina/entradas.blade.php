@extends('layouts.app')

@section('content')
    <editar-entradas-component :role_id="{{auth()->user()->role_id}}" :registersall="{{$entradas}}" :editoriales ="{{$editoriales}}"></editar-entradas-component>
@endsection