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
        }
        .container {
            position: relative;
            width: 100%;
            height: 100%;
            @if($course->certificate_bg)
            background-image: url("{{ public_path($course->certificate_bg) }}");
            background-size: cover;
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
            @continue($key === 'platform_logo') {{-- Handle logo specially if needed, or just as text for now --}}
            
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
                $logoUrl = public_path('img/logo.png'); // Default system logo
                // Fetch actual site logo if available from settings model later
            @endphp
            <div class="element" style="
                left: {{ $logoStyle['x'] }}%;
                top: {{ $logoStyle['y'] }}%;
                width: {{ $logoStyle['width'] ?? 120 }}px;
                height: {{ $logoStyle['height'] ?? 60 }}px;
            ">
                @if(file_exists($logoUrl))
                    <img src="{{ $logoUrl }}" style="width: 100%; height: 100%; object-fit: contain;">
                @else
                    <span style="font-size: 20px; font-weight: bold; color: #0066cc;">{{ $dataMap['platform_logo'] }}</span>
                @endif
            </div>
        @endif
        
        {{-- Instructor Signature --}}
        @if($course->instructor_signature)
            <div class="element" style="
                right: 10%;
                bottom: 10%;
                width: 200px;
            ">
                <img src="{{ public_path($course->instructor_signature) }}" style="width: 100%; border-bottom: 1px solid #000;">
                <div style="text-align: center; font-size: 12px; margin-top: 5px;">{{ $course->author_name }}</div>
            </div>
        @endif
    </div>
</body>
</html>