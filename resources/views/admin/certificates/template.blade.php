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
            
            // Map keys used in editor to real data
            $dataMap = [
                'student_name' => $user->name,
                'course_name' => $course->title,
                'completion_date' => ($type === 'event' ? 'Participou em: ' : 'Concluído em: ') . now()->format('d/m/Y'),
                'certificate_code' => 'Validação: ' . $certHash,
                'author_name' => $authorName,
                'workload_hours' => $workload > 0 ? 'Carga Horária: ' . $workload . 'h' : ($type === 'event' ? 'Evento' : 'Mentoria'),
                'platform_logo' => 'LOGO UNN'
            ];
        @endphp

        @foreach($settings as $key => $style)
            @continue($key === 'platform_logo')
            
            <div class="element" style="
                left: {{ $style['x'] }}%;
                top: {{ $style['y'] }}%;
                font-size: {{ $style['fontSize'] }}px;
                color: {{ $style['color'] }};
                font-weight: {{ $style['fontWeight'] }};
                font-family: {{ $style['fontFamily'] ?? 'Arial, sans-serif' }};
            ">
                {{ $dataMap[$key] ?? '' }}
            </div>
        @endforeach

        {{-- Platform Logo --}}
        @if(isset($settings['platform_logo']))
            @php 
                $logoStyle = $settings['platform_logo'];
                $logoRelPath = 'img/logo.png';
                $logoUrl = (isset($isPreview) && $isPreview) ? asset($logoRelPath) : public_path($logoRelPath);
                $logoExists = (isset($isPreview) && $isPreview) ? true : file_exists($logoUrl);
            @endphp
            <div class="element" style="
                left: {{ $logoStyle['x'] }}%;
                top: {{ $logoStyle['y'] }}%;
                width: {{ $logoStyle['width'] ?? 120 }}px;
                height: {{ $logoStyle['height'] ?? 60 }}px;
            ">
                @if($logoExists)
                    <img src="{{ $logoUrl }}" style="width: 100%; height: 100%; object-fit: contain;">
                @else
                    <span style="font-size: 20px; font-weight: bold; color: #0066cc;">{{ $dataMap['platform_logo'] }}</span>
                @endif
            </div>
        @endif
        
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
