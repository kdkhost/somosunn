<?php

namespace App\Http\Controllers\Panel\Admin\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

trait ManagesContentVisibility
{
    protected function visibilityRule(): string
    {
        return 'nullable|string|in:ambos,somos_unn,somos_unicas';
    }

    protected function applyVisibilityData(
        Request $request,
        array $data,
        ?string $currentVisibility = null,
        bool $currentSomosUnicas = false,
        ?string $table = null
    ): array {
        $visibility = $this->resolveVisibilityValue($request, $currentVisibility, $currentSomosUnicas);

        if ($table === null || $this->supportsColumn($table, 'visibility')) {
            $data['visibility'] = $visibility;
        } else {
            unset($data['visibility']);
        }

        if ($table === null || $this->supportsColumn($table, 'is_somos_unicas')) {
            $data['is_somos_unicas'] = $visibility === 'somos_unicas';
        } else {
            unset($data['is_somos_unicas']);
        }

        return $data;
    }

    protected function resolveVisibilityValue(
        Request $request,
        ?string $currentVisibility = null,
        bool $currentSomosUnicas = false
    ): string {
        $allowed = ['ambos', 'somos_unn', 'somos_unicas'];
        $requestedVisibility = $request->input('visibility');

        if (is_string($requestedVisibility) && in_array($requestedVisibility, $allowed, true)) {
            return $requestedVisibility;
        }

        if (is_string($currentVisibility) && in_array($currentVisibility, $allowed, true)) {
            return $currentVisibility;
        }

        return $currentSomosUnicas ? 'somos_unicas' : 'ambos';
    }

    protected function supportsColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasTable($table) && Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
