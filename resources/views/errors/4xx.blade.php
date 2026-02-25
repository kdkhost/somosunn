@extends('errors.layout')

@php
    $status = (isset($exception) && method_exists($exception, 'getStatusCode')) ? (string) $exception->getStatusCode() : '4xx';
@endphp

@section('error_code', $status)
@section('error_heading', 'Erro de requisicao')
@section('error_message', 'Nao foi possivel concluir sua requisicao por um problema no acesso a este recurso.')
@section('error_hint', 'Revise a URL, faca login novamente se necessario e tente outra vez.')
@section('error_primary_label', 'Ir para a pagina inicial')
@section('error_accent', '#2563EB')
@section('error_accent_soft', '#22D3EE')

