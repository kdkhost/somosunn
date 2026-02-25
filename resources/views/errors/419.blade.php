@extends('errors.layout')

@section('error_code', '419')
@section('error_heading', 'Sessao expirada')
@section('error_message', 'Sua sessao perdeu validade por seguranca e precisa ser renovada.')
@section('error_hint', 'Atualize a pagina, faca login novamente e repita a operacao.')
@section('error_primary_label', 'Atualizar e continuar')
@section('error_primary_url', url()->current())
@section('error_accent', '#0EA5E9')
@section('error_accent_soft', '#22D3EE')

