<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\CustomFont;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\Certificate\CertificateFontCssGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CertificateFidelityTest extends TestCase
{
    use RefreshDatabase;

    public function test_hidden_elements_are_not_rendered_in_template(): void
    {
        $user = User::create([
            'name' => 'UNIT_TEST_HIDDEN_NAME_' . Str::random(8),
            'email' => 'hidden_' . Str::random(8) . '@example.test',
            'password' => Hash::make('password'),
        ]);

        $course = Course::create([
            'user_id' => $user->id,
            'title' => 'Course ' . Str::random(8),
            'price' => 0,
            'is_certificate_enabled' => true,
            'status' => 'published',
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'enrollable_id' => $course->id,
            'enrollable_type' => Course::class,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $course->certificate_settings = [
            'schemaVersion' => 2,
            'meta' => [
                'backgroundFit' => 'cover',
            ],
            'elements' => [
                'student_name' => [
                    'x' => 10,
                    'y' => 20,
                    'visible' => false,
                    'locked' => false,
                    'zIndex' => 10,
                    'fontFamily' => 'Arial, sans-serif',
                    'fontSize' => 30,
                    'fontWeight' => 'bold',
                    'color' => '#000000',
                ],
                'course_name' => [
                    'x' => 10,
                    'y' => 30,
                    'visible' => true,
                    'locked' => false,
                    'zIndex' => 10,
                    'fontFamily' => 'Arial, sans-serif',
                    'fontSize' => 20,
                    'fontWeight' => 'normal',
                    'color' => '#000000',
                ],
                'platform_logo' => [
                    'x' => 50,
                    'y' => 10,
                    'width' => 120,
                    'height' => 60,
                    'visible' => true,
                    'locked' => false,
                    'zIndex' => 20,
                    'mandatory' => true,
                ],
            ],
        ];
        $course->save();

        $html = view('admin.certificates.template', [
            'user' => $user,
            'course' => $course,
            'certHash' => 'TESTCERT' . Str::random(10),
            'authorName' => 'Autor',
            'workload' => 0,
            'type' => 'course',
            'fontCss' => '',
            'isPreview' => true,
        ])->render();

        $this->assertStringContainsString($course->title, $html);
        $this->assertStringNotContainsString($user->name, $html);
    }

    public function test_font_face_is_injected_when_active_custom_font_is_used(): void
    {
        $user = User::create([
            'name' => 'UNIT_TEST_FONT_' . Str::random(8),
            'email' => 'font_' . Str::random(8) . '@example.test',
            'password' => Hash::make('password'),
        ]);

        $course = Course::create([
            'user_id' => $user->id,
            'title' => 'Course ' . Str::random(8),
            'price' => 0,
            'is_certificate_enabled' => true,
            'status' => 'published',
        ]);

        Enrollment::create([
            'user_id' => $user->id,
            'enrollable_id' => $course->id,
            'enrollable_type' => Course::class,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        CustomFont::create([
            'name' => 'My Font',
            'type' => 'file',
            'file_path' => 'uploads/fonts/myfont.ttf',
            'google_font_url' => null,
            'font_family' => 'MyFont',
            'is_active' => true,
            'uploaded_by' => $user->id,
        ]);

        $course->certificate_settings = [
            'schemaVersion' => 2,
            'meta' => [
                'backgroundFit' => 'cover',
            ],
            'elements' => [
                'student_name' => [
                    'x' => 10,
                    'y' => 20,
                    'visible' => true,
                    'locked' => false,
                    'zIndex' => 10,
                    'fontFamily' => 'MyFont',
                    'fontSize' => 30,
                    'fontWeight' => 'bold',
                    'color' => '#000000',
                ],
                'course_name' => [
                    'x' => 10,
                    'y' => 30,
                    'visible' => true,
                    'locked' => false,
                    'zIndex' => 10,
                    'fontFamily' => 'Arial, sans-serif',
                    'fontSize' => 20,
                    'fontWeight' => 'normal',
                    'color' => '#000000',
                ],
                'platform_logo' => [
                    'x' => 50,
                    'y' => 10,
                    'width' => 120,
                    'height' => 60,
                    'visible' => true,
                    'locked' => false,
                    'zIndex' => 20,
                    'mandatory' => true,
                ],
            ],
        ];
        $course->save();

        $fontCss = app(CertificateFontCssGenerator::class)->buildFontCss($course->certificate_settings, true);

        $this->assertNotEmpty($fontCss);
        $this->assertStringContainsString('@font-face', $fontCss);
        $this->assertStringContainsString("font-family:'MyFont'", $fontCss);
        $this->assertStringContainsString('uploads/fonts/myfont.ttf', $fontCss);

        $html = view('admin.certificates.template', [
            'user' => $user,
            'course' => $course,
            'certHash' => 'TESTCERT' . Str::random(10),
            'authorName' => 'Autor',
            'workload' => 0,
            'type' => 'course',
            'fontCss' => $fontCss,
            'isPreview' => true,
        ])->render();

        $this->assertStringContainsString('@font-face', $html);
    }
}

