@extends('errors.layout')

@section('error_code', '429')
@section('error_heading', 'Muitas requisicoes')
@section('error_message', 'O limite de tentativas foi atingido em um curto periodo de tempo.')
@section('error_hint', 'Aguarde alguns instantes antes de tentar novamente.')
@section('error_primary_label', 'Voltar para a home')
@section('error_accent', '#D97706')
@section('error_accent_soft', '#F59E0B')

