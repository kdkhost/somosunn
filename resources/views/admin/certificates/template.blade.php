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
            width: 1122px; /* A4 Landscape at 96 DPI approx */
            height: 793px;
            overflow: hidden;
        }

        /* Web Preview Scaling */
        @media screen {
            body {
                width: 100%;
                height: auto;
                background: #525659;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 40px 0;
                overflow: auto;
            }
            .container {
                width: 1122px !important;
                height: 793px !important;
                box-shadow: 0 0 20px rgba(0,0,0,0.5);
                transform-origin: top center;
            }
        }

        .container {
            position: relative;
            width: 100%;
            height: 100%;
            background-color: white;
            @if($course->certificate_bg)
                @php 
                    $bgPath = (isset($isPreview) && $isPreview) ? asset($course->certificate_bg) : public_path($course->certificate_bg);
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
            $certTitle = $settings['title'] ?? 'CERTIFICADO DE CONCLUSÃO';
            $certPresentation = $settings['presentation_text'] ?? $course->default_presentation_text;
            
            // Map keys used in editor to real data
            $dataMap = [
                'student_name' => $user->name,
                'course_name' => $course->title,
                'completion_date' => ($type === 'event' ? 'Participou em: ' : 'Concluído em: ') . (\Carbon\Carbon::parse($user->enrollments()->where('enrollable_id', $course->id)->where('enrollable_type', get_class($course))->first()->completed_at ?? now())->format('d/m/Y')),
                'certificate_code' => 'Validação: ' . $certHash,
                'author_name' => $authorName,
                'workload_hours' => $workload > 0 ? 'Carga Horária: ' . $workload . 'h' : ($type === 'event' ? 'Evento' : 'Mentoria'),
                'platform_logo' => 'LOGO UNN',
                'title' => $certTitle,
                'presentation_text' => $certPresentation
            ];
        @endphp

        {{-- Render Fixed Title (if not in draggable elements) --}}
        @if(!isset($settings['title']) || !is_array($settings['title']))
            <div class="element" style="width: 100%; text-align: center; top: 15%; font-size: 40px; font-weight: bold; color: #222;">
                {{ $certTitle }}
            </div>
        @endif

        {{-- Render Fixed Presentation Text (if not in draggable elements) --}}
        @if(!isset($settings['presentation_text']) || !is_array($settings['presentation_text']))
             <div class="element" style="width: 100%; text-align: center; top: 25%; font-size: 18px; color: #555;">
                {{ $certPresentation }}
            </div>
        @endif

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
            ">
                {{ $dataMap[$key] ?? '' }}
            </div>
        @endforeach

            @php 
                $logoStyle = $settings['platform_logo'];
                $logoRelPath = 'img/logo.png'; 
                
                // Try to find a real logo
                if(file_exists(public_path('uploads/branding/logo_admin.png'))) {
                    $logoRelPath = 'uploads/branding/logo_admin.png';
                }
                
                // For PDF (DomPDF), use absolute filesystem path
                // For Preview (Browser), use URL
                if (isset($isPreview) && $isPreview) {
                     $logoUrl = asset($logoRelPath);
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
