@echo off
title UNN Scheduler (Cron)
echo Iniciando Agendador de Tarefas do Laravel (Schedule Work)...
echo Mantenha esta janela aberta para que as tarefas automaticas funcionem.
echo.
cd /d "%~dp0"

php artisan schedule:work
