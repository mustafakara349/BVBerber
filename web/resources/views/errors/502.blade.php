@php
    $metadata = $metadata ?? \App\Support\Exceptions\WebErrorMetadata::resolve(502, isset($exception) ? $exception->getMessage() : null);
@endphp
@extends('errors.layout')

@section('title', $metadata->title)
@section('heading', $metadata->title)
@section('message', $metadata->description)
