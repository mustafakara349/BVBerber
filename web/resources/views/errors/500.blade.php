@php
    $refId = $referenceId ?? ($metadata->referenceId ?? app(\App\Services\ErrorTrackingService::class)->generateReferenceId());
    $metadata = $metadata ?? \App\Support\Exceptions\WebErrorMetadata::resolve(500, null, $refId);
@endphp
@extends('errors.layout')

@section('title', $metadata->title)
@section('heading', $metadata->title)
@section('message', $metadata->description)
