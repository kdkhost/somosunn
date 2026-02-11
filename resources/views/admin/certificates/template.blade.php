<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Certificado</title>

    @php
        $normalizer = app(\App\Services\Certificate\CertificateSettingsNormalizer::class);
        $normalized = $normalizer->normalize($course->certificate_settings ?? []);
        $certMeta = $normalized['meta'] ?? [];
        $elements = $normalized['elements'] ?? [];
        $backgroundFit = ($certMeta['backgroundFit'] ?? 'cover') === 'stretch' ? 'stretch' : 'cover';
    @endphp

    @if(!empty($fontCss))
        <style>{!! $fontCss !!}</style>
    @endif

    <style>
        @page {
            margin: 0px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0px;
            padding: 0px;
            width: 842px;
            /* A4 Landscape at 72 DPI */
            height: 595px;
            overflow: hidden;
        }

        .preview-stage {
            width: 842px;
            height: 595px;
        }

        /* Web Preview Scaling - preserve pixels via transform scale */
        @media screen {
            body {
                width: 100%;
                height: 100%;
                background: transparent;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 0;
                margin: 0;
                overflow: hidden;
            }

            .preview-stage {
                width: 100vw;
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                overflow: hidden;
            }
        }

        .container {
            position: relative;
            width: 842px;
            height: 595px;
            background-color: white;
            transform-origin: top left;

            @if($course->certificate_bg)
                @php
                    $bgPath = (isset($isPreview) && $isPreview)
                        ? '/' . ltrim($course->certificate_bg, '/')
                        : public_path($course->certificate_bg);
                @endphp
                background-image: url("{{ $bgPath }}");
                background-size: {{ $backgroundFit === 'stretch' ? '100% 100%' : 'cover' }};
                background-position: center;
                background-repeat: no-repeat;
            @endif
        }

        .element {
            position: absolute;
        }
    </style>
</head>

<body>
    <div class="preview-stage">
        <div class="container">
            @php
                $certTitle = (isset($certMeta['titleText']) && is_string($certMeta['titleText']))
                    ? $certMeta['titleText']
                    : 'CERTIFICADO DE CONCLUSÃO';

                $certPresentation = (isset($certMeta['presentationText']) && is_string($certMeta['presentationText']))
                    ? $certMeta['presentationText']
                    : ((string) ($course->default_presentation_text ?? ''));

                $enrollmentCompletedAt = $user->enrollments()
                    ->where('enrollable_id', $course->id)
                    ->where('enrollable_type', get_class($course))
                    ->value('completed_at');

                $fallbackDate = $enrollmentCompletedAt ? \Carbon\Carbon::parse($enrollmentCompletedAt) : now();
                $eventDate = ($type === 'event' && !empty($course->start_at)) ? \Carbon\Carbon::parse($course->start_at) : null;
                $completionDate = $eventDate ?: $fallbackDate;

                // Map keys used in editor to real data
                $dataMap = [
                    'student_name' => $user->name,
                    'course_name' => $course->title,
                    'completion_date' => ($type === 'event' ? 'Participou em: ' : 'Concluído em: ') . $completionDate->format('d/m/Y'),
                    'certificate_code' => 'Validação: ' . $certHash,
                    'author_name' => $authorName,
                    'workload_hours' => $workload > 0 ? 'Carga Horária: ' . str_replace('.', ',', $workload) . 'h' : ($type === 'event' ? 'Evento' : 'Mentoria'),
                    'platform_logo' => 'LOGO',
                    'title' => $certTitle,
                    'presentation_text' => $certPresentation,
                ];
            @endphp

            {{-- Render only saved elements (no fallbacks) --}}
            @foreach($elements as $key => $style)
                @if($key === 'platform_logo') @continue @endif
                @continue(!is_array($style))
                @continue(array_key_exists('visible', $style) && !$style['visible'])

                @php
                    $textValue = $dataMap[$key] ?? ($style['text'] ?? '');
                    $isMultiline = (bool) ($style['multiline'] ?? false);
                    $maxWidth = $style['maxWidth'] ?? null;
                    $textAlign = $style['textAlign'] ?? 'left';
                    $lineHeight = $style['lineHeight'] ?? null;
                    $letterSpacing = $style['letterSpacing'] ?? null;
                @endphp

                <div class="element" style="
                            left: {{ $style['x'] }}%;
                            top: {{ $style['y'] }}%;
                            font-size: {{ $style['fontSize'] }}px;
                            color: {{ $style['color'] }};
                            font-weight: {{ $style['fontWeight'] }};
                            font-family: {{ $style['fontFamily'] ?? 'Arial, sans-serif' }};
                            text-align: {{ $textAlign }};
                            z-index: {{ $style['zIndex'] ?? 10 }};
                            white-space: {{ $isMultiline ? 'normal' : 'nowrap' }};
                            width: {{ $isMultiline && $maxWidth ? $maxWidth . 'px' : 'auto' }};
                            @if($lineHeight) line-height: {{ $lineHeight }}; @endif
                            @if($letterSpacing !== null) letter-spacing: {{ $letterSpacing }}px; @endif
                        ">
                    {{ $textValue }}
                </div>
            @endforeach

            @php
                $logoStyle = $elements['platform_logo'] ?? ['x' => 50, 'y' => 10, 'width' => 120, 'height' => 60, 'zIndex' => 20];

                $logoPath = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
                $logoRelPath = $logoPath ? ltrim($logoPath, '/') : 'img/logo.png';

                if (isset($isPreview) && $isPreview) {
                    $logoUrl = '/' . $logoRelPath;
                } else {
                    $logoUrl = public_path($logoRelPath);
                }
            @endphp

            <div class="element" style="
                left: {{ $logoStyle['x'] }}%;
                top: {{ $logoStyle['y'] }}%;
                width: {{ $logoStyle['width'] ?? 120 }}px;
                height: {{ $logoStyle['height'] ?? 60 }}px;
                z-index: {{ $logoStyle['zIndex'] ?? 20 }};
            ">
                <img src="{{ $logoUrl }}" style="width: 100%; height: 100%; object-fit: contain;">
            </div>

            {{-- Instructor Signature --}}
            @if($course->instructor_signature)
                @php
                    $sigPath = (isset($isPreview) && $isPreview) ? '/' . ltrim($course->instructor_signature, '/') : public_path($course->instructor_signature);
                @endphp
                <div class="element" style="
                    right: 10%;
                    bottom: 10%;
                    width: 200px;
                ">
                    <img src="{{ $sigPath }}" style="width: 100%; border-bottom: 1px solid #000;">
                    <div style="text-align: center; font-size: 12px; margin-top: 5px;">{{ $course->author_name }}</div>
                </div>
            @endif
        </div>
    </div>

    @if(isset($isPreview) && $isPreview)
        <script>
            (function () {
                const CERT_W = 842;
                const CERT_H = 595;

                function applyScale() {
                    const container = document.querySelector('.container');
                    if (!container) return;
                    const scale = Math.max(0.1, Math.min(window.innerWidth / CERT_W, window.innerHeight / CERT_H));
                    container.style.transform = `scale(${scale})`;
                }

                window.addEventListener('resize', applyScale);
                applyScale();
            })();
        </script>
    @endif
</body>

</html>

