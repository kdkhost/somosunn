

<?php $__env->startSection('title', 'Criar Curso - UNN'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto px-4 py-10">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Criar Novo Curso</h1>
        <p class="text-gray-600">Compartilhe seu conhecimento com a comunidade.</p>
    </div>

    <form action="<?php echo e(route('courses.store')); ?>" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-8 space-y-6">
        <?php echo csrf_field(); ?>

        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Título do Curso</label>
            <input type="text" name="title" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ex: Masterclass de Marketing">
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço (R$)</label>
                <input type="number" step="0.01" name="price" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Duração (minutos)</label>
                <input type="number" name="duration" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Ex: 120">
            </div>
        </div>

        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição Curta</label>
            <textarea name="short_description" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Resumo para listagem..."></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição Completa</label>
            <textarea name="full_description" rows="5" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Detalhes do curso, o que o aluno vai aprender..."></textarea>
        </div>

        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Capa do Curso</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                <div class="space-y-1 text-center">
                    <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-3"></i>
                    <div class="flex text-sm text-gray-600">
                        <label for="thumbnail" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                            <span>Upload um arquivo</span>
                            <input id="thumbnail" name="thumbnail" type="file" class="sr-only">
                        </label>
                        <p class="pl-1">ou arraste e solte</p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_certificate_enabled" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <span class="text-sm text-gray-700">Emitir certificado ao concluir</span>
            </label>
        </div>

        <div class="pt-4 border-t">
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#1F5EDB] hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                Criar Curso
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\courses\create.blade.php ENDPATH**/ ?>