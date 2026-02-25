@extends('errors.layout')

@section('error_code', '508')
@section('error_heading', 'Loop detectado no servidor')
@section('error_message', 'Foi identificado um ciclo de processamento que impediu a conclusao da resposta.')
@section('error_hint', 'Tente novamente e, se persistir, contate o suporte com o horario deste erro.')
@section('error_primary_label', 'Voltar para a home')
@section('error_accent', '#1D4ED8')
@section('error_accent_soft', '#14B8A6')

