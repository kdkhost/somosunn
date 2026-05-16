<?php

/**
 * ============================================================
 * PROPRIEDADE INTELECTUAL E DIREITOS AUTORAIS
 * ============================================================
 *
 * @autor marcelo-brad rj
 * @contato
 * Tel: 21 981325441
 * WhatsApp: 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 *
 * ============================================================
 *
 * Sistema UNN - Unit tests para StorageTestResult (value object).
 *
 * Cobre:
 *   - status inicial pending
 *   - addSuccess / addFailure preserva ordem dos steps
 *   - markSuccess / markFailed / markTimeout transitam status
 *   - lastStepFailed reflete ultimo step
 *   - toArray serializa todos os campos esperados
 *   - totalLatencyMs e recomputado como soma dos steps
 *
 * Spec: .kiro/specs/multi-provider-s3-storage (task 3.5 - parcial)
 * Requirements: 5.2, 5.3, 5.4, 5.6
 */

namespace Tests\Unit\Support;

use App\Support\StorageTestResult;
use Tests\TestCase;

class StorageTestResultTest extends TestCase
{
    public function test_initial_state_is_pending_with_no_steps(): void
    {
        $result = new StorageTestResult('idrive');

        $this->assertSame(StorageTestResult::STATUS_PENDING, $result->status);
        $this->assertSame('idrive', $result->provider);
        $this->assertSame([], $result->steps);
        $this->assertNull($result->errorMessage);
        $this->assertSame(0.0, $result->totalLatencyMs);
        $this->assertFalse($result->isSuccess());
        $this->assertFalse($result->lastStepFailed());
    }

    public function test_add_success_appends_step_with_correct_shape(): void
    {
        $result = new StorageTestResult('aws');
        $result->addSuccess('upload', 'arquivo enviado', 12.345);

        $this->assertCount(1, $result->steps);
        $step = $result->steps[0];

        $this->assertSame('upload', $step['name']);
        $this->assertSame('success', $step['status']);
        $this->assertSame('arquivo enviado', $step['detail']);
        $this->assertSame(12.35, $step['latency_ms'], 'latencia deve ser arredondada para 2 casas');
    }

    public function test_add_failure_appends_step_with_correct_shape(): void
    {
        $result = new StorageTestResult('wasabi');
        $result->addFailure('upload', 'access denied', 5.0);

        $this->assertCount(1, $result->steps);
        $this->assertSame('failed', $result->steps[0]['status']);
        $this->assertSame('access denied', $result->steps[0]['detail']);
    }

    public function test_steps_preserve_insertion_order(): void
    {
        $result = new StorageTestResult('aws');
        $result->addSuccess('step1', 'a', 1.0);
        $result->addSuccess('step2', 'b', 2.0);
        $result->addFailure('step3', 'c', 3.0);

        $names = array_column($result->steps, 'name');
        $this->assertSame(['step1', 'step2', 'step3'], $names);
    }

    public function test_mark_success_sets_status_and_clears_error_message(): void
    {
        $result = new StorageTestResult('idrive');
        $result->errorMessage = 'algo errado';

        $result->markSuccess();

        $this->assertTrue($result->isSuccess());
        $this->assertSame(StorageTestResult::STATUS_SUCCESS, $result->status);
        $this->assertNull($result->errorMessage);
    }

    public function test_mark_failed_sets_status_and_error_message(): void
    {
        $result = new StorageTestResult('idrive');
        $result->markFailed('credenciais invalidas');

        $this->assertSame(StorageTestResult::STATUS_FAILED, $result->status);
        $this->assertSame('credenciais invalidas', $result->errorMessage);
        $this->assertFalse($result->isSuccess());
    }

    public function test_mark_timeout_uses_default_message(): void
    {
        $result = new StorageTestResult('aws');
        $result->markTimeout();

        $this->assertSame(StorageTestResult::STATUS_TIMEOUT, $result->status);
        $this->assertNotNull($result->errorMessage);
        $this->assertStringContainsString('30 second', (string) $result->errorMessage);
    }

    public function test_mark_timeout_accepts_custom_detail(): void
    {
        $result = (new StorageTestResult('aws'))->markTimeout('connection still pending');

        $this->assertSame(StorageTestResult::STATUS_TIMEOUT, $result->status);
        $this->assertSame('connection still pending', $result->errorMessage);
    }

    public function test_total_latency_is_recomputed_from_steps_on_success(): void
    {
        $result = new StorageTestResult('idrive');
        $result->addSuccess('a', '', 10.5);
        $result->addSuccess('b', '', 20.0);
        $result->addSuccess('c', '', 5.25);
        $result->markSuccess();

        $this->assertSame(35.75, $result->totalLatencyMs);
    }

    public function test_total_latency_is_recomputed_on_failure(): void
    {
        $result = new StorageTestResult('aws');
        $result->addSuccess('a', '', 10.0);
        $result->addFailure('b', 'erro', 7.5);
        $result->markFailed('falha no step b');

        $this->assertSame(17.5, $result->totalLatencyMs);
    }

    public function test_last_step_failed_reflects_only_the_last_step(): void
    {
        $result = new StorageTestResult('aws');

        $result->addSuccess('s1', '', 1.0);
        $this->assertFalse($result->lastStepFailed());

        $result->addFailure('s2', 'erro', 2.0);
        $this->assertTrue($result->lastStepFailed());

        $result->addSuccess('s3', '', 3.0);
        $this->assertFalse($result->lastStepFailed(), 'step seguinte de sucesso deve resetar o flag');
    }

    public function test_to_array_serializes_all_expected_keys(): void
    {
        $result = new StorageTestResult('idrive');
        $result->addSuccess('upload', 'ok', 10.0);
        $result->addSuccess('exists', 'found', 5.0);
        $result->markSuccess();

        $arr = $result->toArray();

        $this->assertArrayHasKey('provider', $arr);
        $this->assertArrayHasKey('status', $arr);
        $this->assertArrayHasKey('error_message', $arr);
        $this->assertArrayHasKey('total_latency_ms', $arr);
        $this->assertArrayHasKey('steps', $arr);

        $this->assertSame('idrive', $arr['provider']);
        $this->assertSame('success', $arr['status']);
        $this->assertNull($arr['error_message']);
        $this->assertSame(15.0, $arr['total_latency_ms']);
        $this->assertCount(2, $arr['steps']);
        $this->assertSame('upload', $arr['steps'][0]['name']);
        $this->assertSame('exists', $arr['steps'][1]['name']);
    }

    public function test_chained_calls_return_same_instance(): void
    {
        $result = new StorageTestResult('aws');

        $chain = $result
            ->addSuccess('a', '', 1.0)
            ->addFailure('b', 'erro', 2.0)
            ->markFailed('teste falhou');

        $this->assertSame($result, $chain);
    }
}
