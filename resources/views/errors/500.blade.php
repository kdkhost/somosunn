@extends('errors.layout')

@section('error_code', '500')
@section('error_heading', 'Erro interno do servidor')
@section('error_message', 'Encontramos uma falha inesperada ao processar sua solicitacao.')
@section('error_hint', 'Nossa equipe ja pode ter sido notificada. Tente novamente em alguns minutos.')
@section('error_primary_label', 'Voltar para a home')
@section('error_accent', '#DC2626')
@section('error_accent_soft', '#F97316')

