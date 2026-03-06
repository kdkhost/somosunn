<?php

namespace Tests\Feature;

use App\Http\Controllers\MentorshipCheckoutController;
use App\Models\Mentorship;
use App\Models\User;
use Tests\TestCase;

class MentorshipCheckoutAvailabilityTest extends TestCase
{
    public function test_checkout_redirects_when_mentorship_is_closed(): void
    {
        $user = new User();
        $user->id = 999;
        $user->role = 'member';
        $user->level = 'iniciante';

        $mentorship = new Mentorship([
            'title' => 'Mentoria encerrada',
            'mentor_id' => $user->id,
            'price' => 297,
            'schedule' => [
                'timezone' => 'America/Sao_Paulo',
                'sessions' => [
                    [
                        'date' => now()->subDay()->format('Y-m-d'),
                        'time' => '20:00',
                    ],
                ],
            ],
        ]);
        $mentorship->id = 123;

        $this->be($user);
        $this->app['session']->start();

        $response = app(MentorshipCheckoutController::class)->show($mentorship);

        $this->assertSame(route('mentorships.show', $mentorship), $response->getTargetUrl());
        $this->assertSame('Esta mentoria já encerrou.', session('error'));
    }
}
