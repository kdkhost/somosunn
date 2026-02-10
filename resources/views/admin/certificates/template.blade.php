<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Certificado</title>
    <style>
        @page {
            margin: 0px;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0px;
            padding: 0px;
            width: 1122px;
            /* A4 Landscape at 96 DPI approx */
            height: 793px;
            overflow: hidden;
        }

        /* Web Preview Scaling */
        @media screen {
            body {
                width: 100%;
                height: auto;
                background: transparent;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 0;
                overflow: visible;
            }

            .container {
                max-width: 100%;
                max-height: 70vh;
                width: auto !important;
                height: auto !important;
                aspect-ratio: 1122 / 793;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
                transform-origin: center;
            }
        }

        .container {
            position: relative;
            width: 100%;
            height: 100%;
            background-color: white;
            @if($course->certificate_bg)
                @php 
                    $bgPath = (isset($isPreview) && $isPreview) ? '/' . ltrim($course->certificate_bg, '/') : public_path($course->certificate_bg);
                @endphp
                background-image: url("{{ $bgPath }}");
                background-size: 100% 100%;
                background-repeat: no-repeat;
            @endif
        }

        .element {
            position: absolute;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <div class="container">
        @php
            $settings = $course->certificate_settings ?? [];

            // Extract special fields that might be strings (not style arrays)
            // If settings['title'] is an array (style), we look for 'custom_title' used for text.
            if (isset($settings['custom_title'])) {
                $certTitle = $settings['custom_title'];
            } elseif (isset($settings['title']) && is_string($settings['title'])) {
                $certTitle = $settings['title'];
            } else {
                $certTitle = 'CERTIFICADO DE CONCLUSÃO';
            }

            if (isset($settings['custom_presentation_text'])) {
                $certPresentation = $settings['custom_presentation_text'];
            } elseif (isset($settings['presentation_text']) && is_string($settings['presentation_text'])) {
                $certPresentation = $settings['presentation_text'];
            } else {
                $certPresentation = $course->default_presentation_text;
            }

            // Map keys used in editor to real data
            $dataMap = [
                'student_name' => $user->name,
                'course_name' => $course->title,
                'completion_date' => ($type === 'event' ? 'Participou em: ' : 'Concluído em: ') . (\Carbon\Carbon::parse($user->enrollments()->where('enrollable_id', $course->id)->where('enrollable_type', get_class($course))->first()->completed_at ?? now())->format('d/m/Y')),
                'certificate_code' => 'Validação: ' . $certHash,
                'author_name' => $authorName,
                'workload_hours' => $workload > 0 ? 'Carga Horária: ' . str_replace('.', ',', $workload) . 'h' : ($type === 'event' ? 'Evento' : 'Mentoria'),
                'platform_logo' => 'LOGO UNN',
                'title' => $certTitle,
                'presentation_text' => $certPresentation
            ];
        @endphp

        {{-- Only render draggable elements from saved settings --}}

        @foreach($settings as $key => $style)
            @continue($key === 'platform_logo')
            @continue(!is_array($style))

            <div class="element" style="
                        left: {{ $style['x'] }}%;
                        top: {{ $style['y'] }}%;
                        font-size: {{ $style['fontSize'] }}px;
                        color: {{ $style['color'] }};
                        font-weight: {{ $style['fontWeight'] }};
                        font-family: {{ $style['fontFamily'] ?? 'Arial, sans-serif' }};
                        transform: translate(-50%, -50%); /* Center based on coords */
                        z-index: {{ $style['zIndex'] ?? 10 }};
                    ">
                {{ $dataMap[$key] ?? '' }}
            </div>
        @endforeach

        @php 
                                        $logoStyle = $settings['platform_logo'];

            // Use the same logic as Auth Visual component
            $logoPath = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
            $logoRelPath = $logoPath ? ltrim($logoPath, '/') : 'img/logo.png';

            // For PDF (DomPDF), use absolute filesystem path
            // For Preview (Browser), force relative URL to avoid connection refused
            if (isset($isPreview) && $isPreview) {
                $logoUrl = asset($logoRelPath);
                // If asset returns full URL, ensure its reachable or use relative
                // But we decided to use relative path for preview to avoid domain issues
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
            transform: translate(-50%, -50%);
                z-index: {{ $logoStyle['zIndex'] ?? 20 }};
       
     ">

                <img src="{{ $logoUrl }}" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
        
        {{-- Instructor Signature --}}
        @if($course->instructor_signature)
            @php 
                $sigPath = (isset($isPreview) && $isPreview) ? asset($course->instructor_signature) : public_path($course->instructor_signature);
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
</body>
</html>
