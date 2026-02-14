# Instruções para agentes de IA e editores de código (OBRIGATÓRIO)

## UTF-8 sem BOM (regra absoluta)

- Este projeto usa **UTF-8 sem BOM** em TODOS os arquivos de texto (PHP, Blade, JS, CSS, JSON, MD, etc.).
- É proibido salvar arquivos como **UTF-8 com BOM**.
- Nunca introduza BOM (bytes `EF BB BF`) no início de nenhum arquivo.
- Se o seu editor tiver a opção, selecione **"UTF-8" / "UTF-8 (sem BOM)"** (não use "UTF-8 with BOM").
- Motivo: BOM causa erro de acentuação e pontuação no sistema.

## Checagem antes de commitar

- Rode: `php tools/check-no-bom.php`
- Se houver BOM, re-salve os arquivos como UTF-8 sem BOM e repita a checagem.
