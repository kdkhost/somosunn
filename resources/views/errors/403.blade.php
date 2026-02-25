@extends('errors.layout')

@section('error_code', '403')
@section('error_heading', 'Acesso negado')
@section('error_message', 'Voce nao tem permissao para visualizar este conteudo.')
@section('error_hint', 'Se voce acredita que isso e um engano, fale com um administrador.')
@section('error_primary_label', 'Voltar para a home')
@section('error_accent', '#F97316')
@section('error_accent_soft', '#FB7185')

