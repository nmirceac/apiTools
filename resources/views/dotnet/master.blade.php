<!DOCTYPE html>
<html lang="en">
<head>
    @include('frontend.common.resources')
</head>
<body style="background-color: #222222;">


<div class="max-w-680 mx-auto bg-white">
    <div>
        <img src="/static/header.jpg" alt="Supporting our Supporters">
    </div>


    <div class="py-5 px-8">
        @yield('content')
    </div>

    <div>
        <img src="/static/thumbs-up-background.jpg" alt="Supporting our Supporters">
    </div>
</div>


@include('frontend.common.javascript')

</body>
</html>
