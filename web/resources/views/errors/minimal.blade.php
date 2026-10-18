@php
    $code = isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
    $metadata = $metadata ?? \App\Support\Exceptions\WebErrorMetadata::resolve($code, isset($exception) ? $exception->getMessage() : null);
@endphp
@extends('errors.layout')

@section('title', $metadata->title)
@section('heading', $metadata->title)
@section('message', $metadata->description)
