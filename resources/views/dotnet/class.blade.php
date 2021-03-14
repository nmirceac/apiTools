@include('api-tools::dotnet.class.header')

@foreach($class['methods'] as $method)
        @include('api-tools::dotnet.class.method', ['method'=>$method])
@endforeach

    @include('api-tools::dotnet.class.footer')
