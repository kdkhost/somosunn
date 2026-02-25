@extends('errors.layout')

@section('error_code', '401')
@section('error_heading', 'Autenticacao necessaria')
@section('error_message', 'Esta area exige login para continuar.')
@section('error_hint', 'Entre com sua conta e tente novamente.')
@section('error_primary_label', 'Fazer login')
@section('error_primary_url', route('login'))
@section('error_accent', '#0284C7')
@section('error_accent_soft', '#06B6D4')

