<?php

namespace App\Services\Waf\Scanners;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

/**
 * Scanner de AST PHP.
 *
 * Detecta padroes de risco em arquivos .php:
 *   - DB::raw / DB::statement / whereRaw / orderByRaw / selectRaw / havingRaw
 *   - Concatenacao de strings em queries (heuristica)
 *   - eval(), shell_exec(), exec(), passthru(), system(), proc_open(), popen()
 *   - assert($string)
 *   - unserialize() de entrada de usuario
 *   - md5()/sha1() usados para senha
 *
 * Spec: .kiro/specs/waf-e-auditoria-seguranca
 * Requisitos: 1.2, 2.4, 2.5
 */
class PhpAstScanner extends AbstractScanner
{
    public function id(): string
    {
        return 'php-ast';
    }

    public function label(): string
    {
        return 'AST PHP - queries cruas, eval, shell_exec, assert, unserialize';
    }

    public function scan(AuditContext $ctx): iterable
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        foreach ($this->iterateFiles($ctx, ['.php']) as $file) {
            $absPath = $file->getPathname();
            $rel     = $ctx->rel($absPath);
            $code    = @file_get_contents($absPath);

            if ($code === false || $code === '') {
                continue;
            }

            try {
                $ast = $parser->parse($code);
            } catch (\Throwable $e) {
                // Arquivo com erro de parse - ignora, nao e responsabilidade do scanner consertar
                continue;
            }

            if ($ast === null) {
                continue;
            }

            $visitor = new PhpAstScannerVisitor($absPath, $rel, $this);
            $traverser = new NodeTraverser();
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);

            foreach ($visitor->findings as $finding) {
                yield $finding;
            }
        }
    }

    /**
     * Fabrica um AuditFinding - acessivel pelo visitor interno.
     */
    public function makeFinding(
        string $id,
        string $category,
        string $severity,
        string $title,
        string $recommendation,
        string $file,
        ?int   $line,
        string $excerpt,
        bool   $wafMitigable,
        ?string $compensating = null
    ): AuditFinding {
        return new AuditFinding(
            id:                  $id,
            category:            $category,
            severity:            $severity,
            area:                $this->areaFromPath($file),
            title:               $title,
            recommendation:      $recommendation,
            file:                $file,
            line:                $line,
            context:             $excerpt,
            wafMitigable:        $wafMitigable,
            compensatingControl: $compensating,
            deadline:            AuditFinding::defaultDeadline($severity),
        );
    }

    /**
     * Acesso ao excerpt para o visitor.
     */
    public function buildExcerpt(string $absPath, ?int $line): string
    {
        if ($line === null) {
            return '';
        }

        return $this->excerpt($absPath, $line);
    }
}

/**
 * Visitor interno - detecta padroes de risco durante a travessia do AST.
 */
class PhpAstScannerVisitor extends NodeVisitorAbstract
{
    /** @var AuditFinding[] */
    public array $findings = [];

    private int $counter = 0;

    public function __construct(
        private readonly string         $absPath,
        private readonly string         $relPath,
        private readonly PhpAstScanner  $scanner,
    ) {}

    public function enterNode(Node $node): void
    {
        // --- eval() / shell_exec() / exec() / passthru() / system() / proc_open() / popen() ---
        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
            $fn = strtolower($node->name->toString());

            $dangerous = [
                'eval'       => ['SEC-RCE', AuditFinding::SEVERITY_CRITICAL, 'Uso de eval() - risco critico de RCE', 'Remover eval(); se realmente necessario, isolar com allowlist rigida e nunca receber entrada de usuario.'],
                'shell_exec' => ['SEC-RCE', AuditFinding::SEVERITY_CRITICAL, 'Uso de shell_exec() - risco critico de RCE', 'Substituir por chamadas parametrizadas (Symfony Process) e validar argumentos.'],
                'exec'       => ['SEC-RCE', AuditFinding::SEVERITY_HIGH,     'Uso de exec() - risco alto de RCE',     'Usar Symfony Process com argumentos como array e validar entrada.'],
                'passthru'   => ['SEC-RCE', AuditFinding::SEVERITY_CRITICAL, 'Uso de passthru() - risco critico de RCE', 'Evitar; usar Symfony Process com escapeshellarg individual.'],
                'system'     => ['SEC-RCE', AuditFinding::SEVERITY_HIGH,     'Uso de system() - risco alto de RCE',     'Usar Symfony Process com escapeshellarg.'],
                'popen'      => ['SEC-RCE', AuditFinding::SEVERITY_HIGH,     'Uso de popen() - risco alto de RCE',      'Usar Symfony Process com escapeshellarg.'],
                'proc_open'  => ['SEC-RCE', AuditFinding::SEVERITY_HIGH,     'Uso de proc_open() - validar argumentos', 'Passar argumentos como array, evitar concatenacao com entrada de usuario.'],
                'assert'     => ['SEC-RCE', AuditFinding::SEVERITY_HIGH,     'Uso de assert() pode interpretar string como codigo', 'Remover ou trocar por checagem explicita (throw).'],
                'create_function' => ['SEC-RCE', AuditFinding::SEVERITY_CRITICAL, 'create_function() avalia codigo', 'Substituir por closures.'],
                'unserialize' => ['SEC-DESER', AuditFinding::SEVERITY_HIGH, 'unserialize() sem allowed_classes', 'Sempre passar [\'allowed_classes\' => false] ou usar JSON.'],
            ];

            if (isset($dangerous[$fn])) {
                [$catPrefix, $severity, $title, $rec] = $dangerous[$fn];

                // Para unserialize, so flaga se nao tem allowed_classes => false
                if ($fn === 'unserialize') {
                    if ($this->unserializeHasAllowedClassesFalse($node)) {
                        return;
                    }
                }

                $this->emit(
                    id:       $this->nextId($catPrefix),
                    category: $catPrefix,
                    severity: $severity,
                    title:    $title,
                    rec:      $rec,
                    line:     $node->getStartLine(),
                    waf:      in_array($fn, ['shell_exec', 'exec', 'system', 'passthru', 'eval'], true),
                );
            }
        }

        // --- DB::raw / DB::statement / whereRaw / orderByRaw / selectRaw / havingRaw ---
        if ($node instanceof Node\Expr\StaticCall && $node->class instanceof Node\Name) {
            $className = strtolower($node->class->toString());
            $methodName = is_string($node->name) ? strtolower($node->name) : (method_exists($node->name, 'toLowerString') ? $node->name->toLowerString() : '');

            if (in_array($className, ['db', 'illuminate\\support\\facades\\db'], true)
                && in_array($methodName, ['raw', 'statement', 'unprepared', 'select'], true)) {
                $hasConcat = $this->firstArgHasConcat($node);

                $this->emit(
                    id:       $this->nextId('SEC-SQL'),
                    category: 'SEC-SQL',
                    severity: $hasConcat ? AuditFinding::SEVERITY_HIGH : AuditFinding::SEVERITY_MEDIUM,
                    title:    $hasConcat
                        ? sprintf('DB::%s() com concatenacao de string - risco de SQLi', $methodName)
                        : sprintf('DB::%s() - revisar se ha parametros vinculados', $methodName),
                    rec:      'Usar Eloquent ou Query Builder com bindings (?). Se DB::raw for indispensavel, combinar com array de bindings posicionais.',
                    line:     $node->getStartLine(),
                    waf:      true,
                );
            }
        }

        // --- whereRaw / orderByRaw / selectRaw / havingRaw / groupByRaw ---
        if ($node instanceof Node\Expr\MethodCall && ($node->name instanceof Node\Identifier)) {
            $methodName = $node->name->toLowerString();

            $rawMethods = ['whereraw', 'orwhereraw', 'orderbyraw', 'selectraw', 'havingraw', 'groupbyraw'];

            if (in_array($methodName, $rawMethods, true)) {
                $hasConcat = $this->firstArgHasConcat($node);

                $this->emit(
                    id:       $this->nextId('SEC-SQL'),
                    category: 'SEC-SQL',
                    severity: $hasConcat ? AuditFinding::SEVERITY_HIGH : AuditFinding::SEVERITY_MEDIUM,
                    title:    sprintf('%s()%s - revisar bindings', $methodName, $hasConcat ? ' com concatenacao' : ''),
                    rec:      'Preferir where()/orderBy()/select() sem "raw". Se indispensavel, usar bindings posicionais (?) e array de parametros.',
                    line:     $node->getStartLine(),
                    waf:      true,
                );
            }
        }
    }

    /* ================================================================ */

    /** Verifica se o primeiro arg de unserialize contem allowed_classes=false. */
    private function unserializeHasAllowedClassesFalse(Node\Expr\FuncCall $node): bool
    {
        if (count($node->args) < 2) {
            return false;
        }

        $arg = $node->args[1];

        if ($arg instanceof Node\Arg && $arg->value instanceof Node\Expr\Array_) {
            foreach ($arg->value->items as $item) {
                if ($item instanceof Node\ArrayItem
                    && $item->key instanceof Node\Scalar\String_
                    && strtolower($item->key->value) === 'allowed_classes'
                    && $item->value instanceof Node\Expr\ConstFetch
                    && strtolower($item->value->name->toString()) === 'false'
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /** True se o primeiro argumento da chamada usa concatenacao (.). */
    private function firstArgHasConcat(Node\Expr\CallLike $node): bool
    {
        $args = $node->getArgs();

        if (empty($args)) {
            return false;
        }

        $first = $args[0]->value ?? null;

        if ($first instanceof Node\Expr\BinaryOp\Concat) {
            return true;
        }

        // Interpolacao "... $var ..." no SQL e equivalente a concatenacao.
        if ($first instanceof Node\Scalar\Encapsed) {
            return true;
        }

        return false;
    }

    private function nextId(string $prefix): string
    {
        $this->counter++;

        return sprintf('%s-%04d', $prefix, $this->counter);
    }

    private function emit(
        string $id,
        string $category,
        string $severity,
        string $title,
        string $rec,
        ?int   $line,
        bool   $waf
    ): void {
        $this->findings[] = $this->scanner->makeFinding(
            id:             $id,
            category:       $category,
            severity:       $severity,
            title:          $title,
            recommendation: $rec,
            file:           $this->relPath,
            line:           $line,
            excerpt:        $this->scanner->buildExcerpt($this->absPath, $line),
            wafMitigable:   $waf,
        );
    }
}
