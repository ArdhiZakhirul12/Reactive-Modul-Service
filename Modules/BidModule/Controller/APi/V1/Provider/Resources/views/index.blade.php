@extends('bidmodule/controller/api/v1/provider::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: {!! config('bidmodule/controller/api/v1/provider.name') !!}
    </p>
@endsection
