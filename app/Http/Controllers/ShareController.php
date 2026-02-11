<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use App\Support\ShortLink;
use Illuminate\Support\Str;

class ShareController extends Controller
{
    public function product(string $code)
    {
        $decoded = ShortLink::decodeProduct($code);
        if (!$decoded) {
            abort(404);
        }

        $type = (string) $decoded['type'];
        $id = (int) $decoded['id'];

        $title = '';
        $description = '';
        $imagePath = '';
        $targetUrl = '';
        $label = '';

        if ($type === 'course') {
            $course = Course::with('creator')->findOrFail($id);
            $label = 'Curso';
            $title = (string) $course->title;
            $description = (string) ($course->short_description ?: $course->full_description ?: '');
            $imagePath = (string) ($course->thumbnail ?? '');
            $targetUrl = route('courses.show', $course->slug ?: $course->id);
        } elseif ($type === 'mentorship') {
            $mentorship = Mentorship::with('mentor')->findOrFail($id);
            $label = 'Mentoria';
            $title = (string) $mentorship->title;
            $description = (string) ($mentorship->description ?? '');
            $imagePath = (string) ($mentorship->image ?? '');
            $targetUrl = route('mentorships.show', $mentorship);
        } elseif ($type === 'event') {
            $event = Event::with('user')->findOrFail($id);
            $label = 'Evento';
            $title = (string) $event->title;
            $description = (string) ($event->description ?? '');
            $imagePath = (string) ($event->image ?? '');
            $targetUrl = route('events.show', $event);
        } else {
            abort(404);
        }

        $metaTitle = trim($title) !== '' ? ($title . ' | Marketplace UNN') : 'Marketplace UNN';
        $metaDescription = $this->normalizeDescription($description);

        if ($metaDescription === '') {
            $metaDescription = "Confira este {$label} no Marketplace UNN.";
        }

        $metaImage = $this->assetUrl($imagePath);
        $canonical = route('share.product', ['code' => $code]);

        return view('share.product', compact(
            'code',
            'type',
            'label',
            'title',
            'metaTitle',
            'metaDescription',
            'metaImage',
            'canonical',
            'targetUrl'
        ));
    }

    private function normalizeDescription(string $description): string
    {
        $text = trim(strip_tags($description));
        $text = preg_replace('/\\s+/', ' ', $text) ?: '';
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        return Str::limit($text, 180, '…');
    }

    private function assetUrl(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}

