@extends('banners::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('banners.name') !!}</p>
@endsection
