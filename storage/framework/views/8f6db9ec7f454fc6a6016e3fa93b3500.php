<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Certificado</title>

    <?php
        $normalizer = app(\App\Services\Certificate\CertificateSettingsNormalizer::class);
        $normalized = $normalizer->normalize($course->certificate_settings ?? []);
        $certMeta = $normalized['meta'] ?? [];
        $elements = $normalized['elements'] ?? [];
        $backgroundFit = ($certMeta['backgroundFit'] ?? 'cover') === 'stretch' ? 'stretch' : 'cover';
    ?>

    <?php if(!empty($fontCss)): ?>
        <style><?php echo $fontCss; ?></style>
    <?php endif; ?>

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

            <?php if($course->certificate_bg): ?>
                <?php
                    $bgPath = (isset($isPreview) && $isPreview)
                        ? '/' . ltrim($course->certificate_bg, '/')
                        : public_path($course->certificate_bg);
                ?>
                background-image: url("<?php echo e($bgPath); ?>");
                background-size: <?php echo e($backgroundFit === 'stretch' ? '100% 100%' : 'cover'); ?>;
                background-position: center;
                background-repeat: no-repeat;
            <?php endif; ?>
        }

        .element {
            position: absolute;
        }
    </style>
</head>

<body>
    <div class="preview-stage">
        <div class="container">
            <?php
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
            ?>

            
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $style): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($key === 'platform_logo'): ?> <?php continue; ?> <?php endif; ?>
                <?php if($key === 'instructor_signature'): ?> <?php continue; ?> <?php endif; ?>
                <?php if(!is_array($style)) continue; ?>
                <?php if(array_key_exists('visible', $style) && !$style['visible']) continue; ?>

                <?php
                    $textValue = $dataMap[$key] ?? ($style['text'] ?? '');
                    $isMultiline = (bool) ($style['multiline'] ?? false);
                    $maxWidth = $style['maxWidth'] ?? null;
                    $textAlign = $style['textAlign'] ?? 'left';
                    $lineHeight = $style['lineHeight'] ?? null;
                    $letterSpacing = $style['letterSpacing'] ?? null;
                ?>

                <div class="element" style="
                            left: <?php echo e($style['x']); ?>%;
                            top: <?php echo e($style['y']); ?>%;
                            font-size: <?php echo e($style['fontSize']); ?>px;
                            color: <?php echo e($style['color']); ?>;
                            font-weight: <?php echo e($style['fontWeight']); ?>;
                            font-family: <?php echo e($style['fontFamily'] ?? 'Arial, sans-serif'); ?>;
                            text-align: <?php echo e($textAlign); ?>;
                            z-index: <?php echo e($style['zIndex'] ?? 10); ?>;
                            white-space: <?php echo e($isMultiline ? 'pre-line' : 'nowrap'); ?>;
                            width: <?php echo e($isMultiline && $maxWidth ? $maxWidth . 'px' : 'auto'); ?>;
                            <?php if($lineHeight): ?> line-height: <?php echo e($lineHeight); ?>; <?php endif; ?>
                            <?php if($letterSpacing !== null): ?> letter-spacing: <?php echo e($letterSpacing); ?>px; <?php endif; ?>
                        ">
                    <?php echo e($textValue); ?>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php
                $logoStyle = $elements['platform_logo'] ?? ['x' => 50, 'y' => 10, 'width' => 120, 'height' => 60, 'zIndex' => 20];

                $logoPath = \App\Models\Setting::get('logo_auth') ?: \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
                $logoRelPath = $logoPath ? ltrim($logoPath, '/') : 'img/logo.png';

                if (isset($isPreview) && $isPreview) {
                    $logoUrl = '/' . $logoRelPath;
                } else {
                    $logoUrl = public_path($logoRelPath);
                }
            ?>

            <div class="element" style="
                left: <?php echo e($logoStyle['x']); ?>%;
                top: <?php echo e($logoStyle['y']); ?>%;
                width: <?php echo e($logoStyle['width'] ?? 120); ?>px;
                height: <?php echo e($logoStyle['height'] ?? 60); ?>px;
                z-index: <?php echo e($logoStyle['zIndex'] ?? 20); ?>;
            ">
                <img src="<?php echo e($logoUrl); ?>" style="width: 100%; height: 100%; object-fit: contain;">
            </div>

            
            <?php if($course->instructor_signature): ?>
                <?php
                    $sigStyle = $elements['instructor_signature'] ?? null;
                    $sigHidden = is_array($sigStyle) && array_key_exists('visible', $sigStyle) && !$sigStyle['visible'];
                ?>

                <?php if(!$sigHidden): ?>
                    <?php
                        $sigPath = (isset($isPreview) && $isPreview)
                            ? '/' . ltrim($course->instructor_signature, '/')
                            : public_path($course->instructor_signature);

                        $sigX = (is_array($sigStyle) && isset($sigStyle['x'])) ? $sigStyle['x'] : null;
                        $sigY = (is_array($sigStyle) && isset($sigStyle['y'])) ? $sigStyle['y'] : null;
                        $sigW = (is_array($sigStyle) && isset($sigStyle['width'])) ? $sigStyle['width'] : 200;
                        $sigH = (is_array($sigStyle) && isset($sigStyle['height'])) ? $sigStyle['height'] : 60;
                        $sigZ = (is_array($sigStyle) && isset($sigStyle['zIndex'])) ? $sigStyle['zIndex'] : 10;
                    ?>

                    <?php if($sigX !== null && $sigY !== null): ?>
                        <div class="element" style="
                            left: <?php echo e($sigX); ?>%;
                            top: <?php echo e($sigY); ?>%;
                            width: <?php echo e($sigW); ?>px;
                            height: <?php echo e($sigH); ?>px;
                            z-index: <?php echo e($sigZ); ?>;
                        ">
                            <img src="<?php echo e($sigPath); ?>" style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                    <?php else: ?>
                        
                        <div class="element" style="
                            right: 10%;
                            bottom: 10%;
                            width: 200px;
                        ">
                            <img src="<?php echo e($sigPath); ?>" style="width: 100%; height: auto; object-fit: contain;">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if(isset($isPreview) && $isPreview): ?>
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
    <?php endif; ?>
</body>

</html>
<?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\admin\certificates\template.blade.php ENDPATH**/ ?>