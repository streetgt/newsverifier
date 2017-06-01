<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
</head>
<body>
    <form action="{{ route('external') }}" method="post">

        {{ csrf_field() }}

        <input type="text" name="url">

        <input type="submit">

    </form>


</body>
</html>
