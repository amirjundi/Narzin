@extends('telemetry::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('telemetry.name') !!}</p>
@endsection
