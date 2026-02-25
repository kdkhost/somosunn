@extends('errors.layout')

@section('error_code', '504')
@section('error_heading', 'Tempo limite do gateway')
@section('error_message', 'A comunicacao com um servico externo excedeu o tempo limite.')
@section('error_hint', 'Aguarde um pouco e tente novamente.')
@section('error_primary_label', 'Tentar novamente')
@section('error_primary_url', url()->current())
@section('error_accent', '#0284C7')
@section('error_accent_soft', '#06B6D4')

