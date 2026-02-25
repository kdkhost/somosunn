@extends('errors.layout')

@php
    $status = (isset($exception) && method_exists($exception, 'getStatusCode')) ? (string) $exception->getStatusCode() : '5xx';
@endphp

@section('error_code', $status)
@section('error_heading', 'Erro interno temporario')
@section('error_message', 'Houve uma falha interna ao processar esta requisicao.')
@section('error_hint', 'Tente novamente em alguns instantes. Se continuar, acione o suporte.')
@section('error_primary_label', 'Ir para a pagina inicial')
@section('error_accent', '#DC2626')
@section('error_accent_soft', '#F97316')

