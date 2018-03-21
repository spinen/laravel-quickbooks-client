<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }} - QuickBooks Authorization</title>
</head>
<body>
<div class="container">
    <h1 class="page-heading">QuickBooks Authorization</h1>

    {{-- TODO: Text here to explain what is going on --}}
    {{-- TODO: Work on UI --}}
    <a href="{!! $authorization_uri !!}">Connect to QuickBooks</a>
</div>
</body>
</html>
