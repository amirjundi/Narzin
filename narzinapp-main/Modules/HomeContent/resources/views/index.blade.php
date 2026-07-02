@extends('homecontent::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('homecontent.name') !!}</p>
@endsection
