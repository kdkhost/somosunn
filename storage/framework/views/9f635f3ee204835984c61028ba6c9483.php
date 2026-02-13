<?php $__env->startSection('title', $course->title . ' - UNN'); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .unn-star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 6px;
        }

        .unn-star-rating input {
            display: none;
        }

        .unn-star-rating label {
            cursor: pointer;
            color: #cbd5e1;
            font-size: 24px;
            line-height: 1;
            margin: 0;
            transition: color 0.15s ease;
        }

        .unn-star-rating input:checked ~ label,
        .unn-star-rating label:hover,
        .unn-star-rating label:hover ~ label {
            color: #f59e0b;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $thumbUrl = null;
    if (!empty($course->thumbnail)) {
        $path = trim((string) $course->thumbnail);
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) $thumbUrl = $path;
        elseif (str_starts_with($path, 'storage/')) $thumbUrl = asset($path);
        elseif (str_starts_with($path, 'uploads/')) $thumbUrl = asset($path);
        else $thumbUrl = asset('storage/' . ltrim($path, '/'));
    }

    $isPaused = (string) ($course->status ?? '') === 'paused';
    $authorName = $course->author_name ?? optional($course->creator)->name ?? 'UNN Academy';
    $firstLesson = $course->lessons->first();

    $selectedRating = old('rating', optional($myReview)->rating);
    $selectedRating = is_numeric($selectedRating) ? max(1, min(5, (int) $selectedRating)) : null;
?>
<div class="bg-gray-50 min-h-screen pb-12">
    <div class="bg-[#1F5EDB] text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row gap-8">
            <div class="flex-1">
                <nav class="text-blue-200 text-sm mb-4">
                    <a href="<?php echo e(route('courses.index')); ?>" class="hover:text-white">Cursos</a> /
                    <span class="text-white"><?php echo e($course->title); ?></span>
                </nav>
                <h1 class="text-3xl md:text-4xl font-bold mb-4"><?php echo e($course->title); ?></h1>
                <p class="text-lg text-blue-100 max-w-2xl mb-6"><?php echo e($course->short_description); ?></p>

                <div class="flex items-center gap-6 text-sm">
                    <span>Criado por <strong><?php echo e($authorName); ?></strong></span>
                    <span><i class="far fa-clock mr-1"></i> <?php echo e($course->duration); ?> min</span>
                    <span><i class="far fa-calendar-alt mr-1"></i> <?php echo e($course->created_at->format('d/m/Y')); ?></span>
                </div>
            </div>

            <div class="md:w-1/3">
                <div class="bg-white rounded-lg shadow-xl overflow-hidden text-gray-900 p-1">
                    <?php if($thumbUrl): ?>
                        <img src="<?php echo e($thumbUrl); ?>" class="w-full h-48 object-cover rounded-t-lg" alt="<?php echo e($course->title); ?>">
                    <?php endif; ?>
                    <div class="p-6">
                        <?php if($isEnrolled): ?>
                            <div class="text-center">
                                <span class="block text-sm text-green-600 font-bold mb-2">Você já possui este curso!</span>
                                <?php if($firstLesson): ?>
                                    <a href="<?php echo e(route('courses.lessons.show', [$course->id, $firstLesson->id])); ?>" class="block w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold text-center rounded-lg transition">
                                        Continuar estudando
                                    </a>
                                <?php else: ?>
                                    <button type="button" disabled class="block w-full py-3 bg-gray-200 text-gray-500 font-bold text-center rounded-lg cursor-not-allowed">
                                        Curso sem aulas
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php
                                $regularPrice = (float) ($course->price ?? 0);
                                $effectivePrice = (float) ($course->effective_price ?? $regularPrice);
                                $flashActive = method_exists($course, 'isFlashSaleActive') ? (bool) $course->isFlashSaleActive() : false;
                            ?>
                            <div class="flex items-end gap-3 mb-4">
                                <div class="text-3xl font-bold text-gray-900">
                                    <?php echo e($effectivePrice > 0 ? 'R$ ' . number_format($effectivePrice, 2, ',', '.') : 'Gratuito'); ?>

                                </div>
                                <?php if($flashActive && $regularPrice > 0 && $effectivePrice < $regularPrice): ?>
                                    <div class="text-sm text-gray-400 line-through mb-1">
                                        <?php echo e('R$ ' . number_format($regularPrice, 2, ',', '.')); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if($isPaused): ?>
                                <button type="button" disabled class="block w-full py-3 bg-gray-200 text-gray-500 font-bold rounded-lg transition mb-3 cursor-not-allowed">
                                    Vendas pausadas
                                </button>
                                <p class="text-xs text-gray-500 text-center">Este curso está publicado, mas as vendas estão pausadas no momento.</p>
                            <?php else: ?>
                                <a href="<?php echo e(route('checkout.show', $course->id)); ?>" class="block w-full py-3 bg-[#1F5EDB] hover:bg-blue-700 text-white font-bold rounded-lg transition mb-3 text-center">
                                    Comprar agora
                                </a>
                                <p class="text-xs text-gray-500 text-center">Pagamento seguro e liberação automática após confirmação.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
                <h2 class="text-2xl font-bold mb-4">Sobre o curso</h2>
                <div class="prose max-w-none text-gray-600">
                    <?php echo \App\Support\RichText::toHtml($course->full_description); ?>

                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
                <h2 class="text-2xl font-bold mb-6">Conteúdo do curso</h2>
                <div class="border rounded-lg divide-y">
                    <?php $__empty_1 = true; $__currentLoopData = $course->lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-4 hover:bg-gray-50 flex items-center justify-between transition group">
                            <div class="flex items-center gap-3">
                                <?php if($isEnrolled || $lesson->is_free_preview): ?>
                                    <i class="fas fa-play-circle text-[#1F5EDB] text-xl"></i>
                                <?php else: ?>
                                    <i class="fas fa-lock text-gray-400"></i>
                                <?php endif; ?>
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo e($lesson->order); ?>. <?php echo e($lesson->title); ?></p>
                                    <?php if($lesson->is_free_preview && !$isEnrolled): ?>
                                        <span class="text-xs text-green-600 font-semibold bg-green-100 px-2 py-0.5 rounded">Aula grátis</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if($isEnrolled || $lesson->is_free_preview): ?>
                                <a href="<?php echo e(route('courses.lessons.show', [$course->id, $lesson->id])); ?>" class="text-sm font-semibold text-[#1F5EDB] opacity-0 group-hover:opacity-100 transition">
                                    Assistir <?php if($lesson->duration): ?> <span class="text-gray-400 font-normal ml-1">(<?php echo e(gmdate('H:i', $lesson->duration)); ?>)</span> <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-4 text-center text-gray-500">Nenhuma aula cadastrada ainda.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Avaliações</h2>
                        <p class="text-sm text-gray-500">Comentários dos alunos sobre este curso.</p>
                    </div>
                    <?php if($reviewsCount > 0): ?>
                        <div class="inline-flex items-center gap-2 rounded-full bg-amber-50 text-amber-700 px-4 py-2 text-sm font-semibold">
                            <i class="fas fa-star"></i>
                            <?php echo e(number_format((float) $reviewsAvg, 1, ',', '.')); ?>/5 (<?php echo e($reviewsCount); ?> <?php echo e($reviewsCount === 1 ? 'avaliação' : 'avaliações'); ?>)
                        </div>
                    <?php endif; ?>
                </div>

                <?php if($myReview): ?>
                    <div class="mb-4 rounded-lg px-4 py-3 border <?php echo e($myReview->status === 'approved' ? 'bg-green-50 border-green-200 text-green-700' : ($myReview->status === 'rejected' ? 'bg-red-50 border-red-200 text-red-700' : 'bg-yellow-50 border-yellow-200 text-yellow-700')); ?>">
                        <?php if($myReview->status === 'approved'): ?>
                            Sua avaliação está publicada.
                        <?php elseif($myReview->status === 'rejected'): ?>
                            Sua avaliação foi recusada. Você pode ajustar e enviar novamente.
                        <?php else: ?>
                            Sua avaliação está em moderação.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <article class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center shrink-0 overflow-hidden">
                                        <?php if(!empty($review->user->photo)): ?>
                                            <img src="<?php echo e($review->user->profile_photo_url ?? ''); ?>" alt="Foto de <?php echo e($review->user->name ?? 'Usuário'); ?>" class="w-full h-full object-cover rounded-full">
                                        <?php else: ?>
                                            <?php echo e(strtoupper(mb_substr((string) ($review->user->name ?? 'U'), 0, 1))); ?>

                                        <?php endif; ?>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 truncate"><?php echo e($review->user->name ?? 'Usuário'); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e(optional($review->created_at)->format('d/m/Y')); ?></div>
                                    </div>
                                </div>
                                <div class="text-amber-500 text-sm whitespace-nowrap">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?php echo e($i <= $review->rating ? 'fas' : 'far'); ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-sm text-gray-700 leading-relaxed"><?php echo nl2br(e($review->comment)); ?></p>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="md:col-span-2 rounded-lg border border-dashed border-slate-300 px-4 py-6 text-center text-slate-500">
                            Este curso ainda não possui avaliações aprovadas.
                        </div>
                    <?php endif; ?>
                </div>

                <?php if(auth()->guard()->check()): ?>
                    <form method="POST" action="<?php echo e(route('courses.reviews.store', $course->id)); ?>" class="border border-slate-200 rounded-xl p-5">
                        <?php echo csrf_field(); ?>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Envie sua avaliação</h3>
                        <p class="text-sm text-gray-500 mb-4">Sua avaliação será moderada antes de aparecer na página.</p>

                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-800 mb-2">Nota</label>
                            <div class="unn-star-rating" role="radiogroup" aria-label="Avaliação por estrelas">
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="course-rating-<?php echo e($i); ?>" name="rating" value="<?php echo e($i); ?>" <?php echo e((string) $selectedRating === (string) $i ? 'checked' : ''); ?>>
                                    <label for="course-rating-<?php echo e($i); ?>" title="<?php echo e($i); ?> de 5"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                            <?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-600 text-sm mt-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-4">
                            <label for="course-review-comment" class="block text-sm font-semibold text-gray-800 mb-2">Comentário</label>
                            <textarea id="course-review-comment" name="comment" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Conte como foi sua experiência com este curso..."><?php echo e(old('comment', optional($myReview)->comment)); ?></textarea>
                            <?php $__errorArgs = ['comment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-600 text-sm mt-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#1F5EDB] hover:bg-blue-700 text-white font-semibold px-5 py-2.5 transition">
                            <i class="fas fa-paper-plane"></i>
                            <?php echo e($myReview ? 'Atualizar avaliação' : 'Enviar avaliação'); ?>

                        </button>
                    </form>
                <?php else: ?>
                    <div class="rounded-lg border border-dashed border-slate-300 px-4 py-5 text-sm text-slate-600">
                        Faça <a href="<?php echo e(route('login')); ?>" class="text-blue-600 font-semibold">login</a> para avaliar este curso.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\courses\show.blade.php ENDPATH**/ ?>