@extends('api\v1\provider\customrequestcontroller::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: {!! config('api\v1\provider\customrequestcontroller.name') !!}
    </p>
@endsection
