@extends('errors.layout')

@section('error_code', '408')
@section('error_heading', 'Tempo de requisicao esgotado')
@section('error_message', 'A resposta demorou mais do que o esperado e a conexao foi encerrada.')
@section('error_hint', 'Verifique sua conexao e atualize a pagina para tentar novamente.')
@section('error_primary_label', 'Tentar novamente')
@section('error_primary_url', url()->current())
@section('error_accent', '#0891B2')
@section('error_accent_soft', '#2DD4BF')

