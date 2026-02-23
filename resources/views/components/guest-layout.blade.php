@props(['title' => null])

@if ($title)
@section('title', $title)
@endif

@include('layouts.guest', ['slot' => $slot])