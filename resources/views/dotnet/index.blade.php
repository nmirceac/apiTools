@extends('api-tools::dotnet.master')

@php
    function getHtmlElementFromPartType($type) {
        if($type=='paragraph') $type='p';

        return $type;
    }
@endphp

@section('content')

    <h1 style="text-transform: none;font-size: 160%;color:#00417c;">
        DotNet ApiClientTools package
    </h1>
    <hr>
    <small>Requires <a href="https://www.nuget.org/packages/Newtonsoft.Json" style="color:#00417c;text-decoration: underline;">Newtonsoft.Json</a></small>
    <hr>
    <p>
        <a href="{{ route(config('api.router.namedPrefix').'.dotNetCode', 'package') }}" style="color:#00417c;text-decoration: underline;">Download the full package</a>
    </p>
    <br>

    @foreach($schema['intro'] as $introPart)
        <{{ getHtmlElementFromPartType($introPart['type']) }}>
            {{ $introPart['content'] }}
        </{{ getHtmlElementFromPartType($introPart['type']) }}>
    @endforeach



    <br>
    <hr>
    <h3 style="font-size: 140%;color:#00417c;">Classes</h3>
    <hr>



    @foreach($schema['classes'] as $class)
    <br>
    <h4>{{ $class['name'] }}</h4>
    <hr>
    <p>{{ '@' }}apiHash {{ md5(json_encode($class)) }}</p>
    <p>{{ $class['description'] }}</p>
    <ul>
        <li>model {{ $class['model'] }}</li>
        <li>internal model {{ $class['internalModel'] }}</li>
        @if(isset($class['keyName']))<li>key name {{ $class['keyName'] }}</li>@endif
        @if(isset($class['orderBy']))<li>order by {{ $class['orderBy'] }} {{ $class['orderAsc'] ? 'ascendingly' : 'descendingly' }}</li>@endif
        @if(isset($class['itemsPerPage']))<li>pagination items per page {{ $class['itemsPerPage'] }}</li>@endif
        <li>methods {{ count($class['methods']) }}</li>
        <li>constants {{ count($class['constants']) }}</li>
    </ul>
    <p>
        <a href="{{ route(config('api.router.namedPrefix').'.dotNetCode', $class['model']) }}" style="color:#00417c;text-decoration: underline;">{{ $class['model'] }}.cs</a>
    </p>
    @endforeach







    @endsection
