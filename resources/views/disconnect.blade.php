<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }} - QuickBooks Deauthorization</title>
</head>
<body>
<div class="container">
    <h1 class="page-heading">QuickBooks Deauthorization</h1>

    {{-- TODO: Text here to explain what is going on --}}
    {{-- TODO: Work on UI --}}
    <a href="{{ route('quickbooks.disconnect') }}"
       onclick="event.preventDefault();document.getElementById('disconnect-form').submit();">
        Disconnect from {{ $company->CompanyName }}
    </a>

    <form id="disconnect-form"
          action="{{ route('quickbooks.disconnect') }}"
          method="POST"
          style="display: none;"
    >
        {{ method_field('DELETE') }}
        {{ csrf_field() }}
    </form>
</div>
</body>
</html>
