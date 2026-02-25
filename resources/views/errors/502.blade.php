@extends('errors.layout')

@section('error_code', '502')
@section('error_heading', 'Resposta invalida do gateway')
@section('error_message', 'Um servico intermediario respondeu de forma inesperada.')
@section('error_hint', 'Espere alguns segundos e tente novamente.')
@section('error_primary_label', 'Atualizar pagina')
@section('error_primary_url', url()->current())
@section('error_accent', '#0369A1')
@section('error_accent_soft', '#0EA5E9')

