@extends('errors.layout')

@section('error_code', '405')
@section('error_heading', 'Metodo nao permitido')
@section('error_message', 'Esta acao nao pode ser executada com o metodo usado nesta requisicao.')
@section('error_hint', 'Retorne para a tela anterior e tente novamente pelo fluxo normal do sistema.')
@section('error_primary_label', 'Voltar para a home')
@section('error_accent', '#0F766E')
@section('error_accent_soft', '#14B8A6')

