<?php $__env->startSection('title', 'Meu Perfil - UNN'); ?>

<?php $__env->startSection('panel_content'); ?>
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">Meu perfil</h1>
                <p class="text-slate-600 mt-1">Mantenha seus dados atualizados para melhor experiência na plataforma.</p>
            </div>
            <a href="<?php echo e(route('panel.dashboard')); ?>"
                class="inline-flex items-center justify-center rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 transition">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </div>

    <form method="POST" action="<?php echo e(route('panel.profile.update')); ?>" enctype="multipart/form-data" class="mt-6 space-y-6">
        <?php echo csrf_field(); ?>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-user text-slate-500"></i> Dados pessoais
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <div>
                    <label class="text-sm font-bold text-slate-700">Nome completo *</label>
                    <input name="name" value="<?php echo e(old('name', $user->name)); ?>" required
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">E-mail *</label>
                    <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Telefone</label>
                    <input id="profile_phone" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>"
                        data-mask-phone maxlength="16" inputmode="tel" autocomplete="tel"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Documento</label>
                    <input id="profile_doc" name="doc" value="<?php echo e(old('doc', $user->doc)); ?>"
                        data-mask-cpf-cnpj maxlength="18" inputmode="numeric" autocomplete="off"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Ocupação</label>
                    <input name="occupation" value="<?php echo e(old('occupation', $user->occupation)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Empresa</label>
                    <input name="company" value="<?php echo e(old('company', $user->company)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Segmento</label>
                    <input name="segment" value="<?php echo e(old('segment', $user->segment)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Interesses</label>
                    <input name="interests" value="<?php echo e(old('interests', $user->interests)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-slate-700">Bio</label>
                    <textarea name="bio" rows="4"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"><?php echo e(old('bio', $user->bio)); ?></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                <div>
                    <label class="text-sm font-bold text-slate-700">Nova senha</label>
                    <input type="password" name="password" minlength="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                    <p class="text-xs text-slate-500 mt-2">Deixe em branco para manter a senha atual.</p>
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Confirmar senha</label>
                    <input type="password" name="password_confirmation" minlength="6"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-image text-slate-500"></i> Fotos
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
                <div>
                    <div class="text-sm font-bold text-slate-700">Foto de perfil</div>
                    <div class="mt-3 flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center">
                            <?php if($user->photo): ?>
                                <img src="<?php echo e(asset($user->photo)); ?>" alt="Avatar" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-slate-500 font-bold"><?php echo e(mb_substr((string) $user->name, 0, 1)); ?></span>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="photo" accept="image/*"
                            class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-[#1F5EDB] file:px-5 file:py-2 file:text-sm file:font-bold file:text-white hover:file:brightness-110">
                    </div>
                </div>

                <div>
                    <div class="text-sm font-bold text-slate-700">Capa</div>
                    <div class="mt-3">
                        <?php if($user->cover_photo): ?>
                            <div class="w-full h-28 rounded-2xl overflow-hidden bg-slate-100">
                                <img src="<?php echo e(asset($user->cover_photo)); ?>" alt="Capa" class="w-full h-full object-cover">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="cover_photo" accept="image/*"
                            class="mt-3 block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-slate-900 file:px-5 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-slate-800">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-slate-500"></i> Endereço
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-5">
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">CEP</label>
                    <input id="profile_cep" name="cep" value="<?php echo e(old('cep', $user->cep)); ?>"
                        data-mask-cep maxlength="9" inputmode="numeric" autocomplete="postal-code"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                    <p id="profile_cep_feedback" class="mt-2 text-xs text-slate-500"></p>
                </div>
                <div class="md:col-span-3">
                    <label class="text-sm font-bold text-slate-700">Rua/Avenida</label>
                    <input id="profile_street" name="street" value="<?php echo e(old('street', $user->street)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">Número</label>
                    <input id="profile_number" name="number" value="<?php echo e(old('number', $user->number)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-3">
                    <label class="text-sm font-bold text-slate-700">Complemento</label>
                    <input id="profile_complement" name="complement" value="<?php echo e(old('complement', $user->complement)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm font-bold text-slate-700">Bairro</label>
                    <input id="profile_neighborhood" name="neighborhood" value="<?php echo e(old('neighborhood', $user->neighborhood)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">Cidade</label>
                    <input id="profile_city" name="city" value="<?php echo e(old('city', $user->city)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-1">
                    <label class="text-sm font-bold text-slate-700">UF</label>
                    <input id="profile_state" name="state" value="<?php echo e(old('state', $user->state)); ?>" maxlength="2" autocomplete="address-level1"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm uppercase">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-share-alt text-slate-500"></i> Redes sociais
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <div>
                    <label class="text-sm font-bold text-slate-700">Website</label>
                    <input name="website" value="<?php echo e(old('website', $user->website)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Instagram</label>
                    <input name="instagram" value="<?php echo e(old('instagram', $user->instagram)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Facebook</label>
                    <input name="facebook" value="<?php echo e(old('facebook', $user->facebook)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">Twitter</label>
                    <input name="twitter" value="<?php echo e(old('twitter', $user->twitter)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">LinkedIn</label>
                    <input name="linkedin" value="<?php echo e(old('linkedin', $user->linkedin)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-700">YouTube</label>
                    <input name="youtube" value="<?php echo e(old('youtube', $user->youtube)); ?>"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fas fa-lock text-slate-500"></i> Privacidade
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                    <input type="checkbox" name="show_email_public" <?php echo e(old('show_email_public', (bool) $user->show_email_public) ? 'checked' : ''); ?>>
                    <span class="text-sm font-semibold text-slate-700">Mostrar e-mail publicamente</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                    <input type="checkbox" name="show_phone_public" <?php echo e(old('show_phone_public', (bool) $user->show_phone_public) ? 'checked' : ''); ?>>
                    <span class="text-sm font-semibold text-slate-700">Mostrar telefone publicamente</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                    <input type="checkbox" name="show_address_public" <?php echo e(old('show_address_public', (bool) $user->show_address_public) ? 'checked' : ''); ?>>
                    <span class="text-sm font-semibold text-slate-700">Mostrar endereço publicamente</span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                    <input type="checkbox" name="hide_profile" <?php echo e(old('hide_profile', (bool) $user->hide_profile) ? 'checked' : ''); ?>>
                    <span class="text-sm font-semibold text-slate-700">Ocultar meu perfil</span>
                </label>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-end">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-full bg-[#1F5EDB] px-8 py-3 text-sm font-extrabold text-white hover:brightness-110 transition">
                <i class="fas fa-save mr-2"></i> Salvar alterações
            </button>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (function () {
            function applyProfileMasks() {
                if (typeof Inputmask !== 'function') {
                    return;
                }

                var cepInput = document.getElementById('profile_cep');
                var phoneInput = document.getElementById('profile_phone');
                var docInput = document.getElementById('profile_doc');

                if (cepInput) {
                    Inputmask('99999-999').mask(cepInput);
                }
                if (phoneInput) {
                    Inputmask({ mask: ['(99) 9999-9999', '(99) 99999-9999'], keepStatic: true }).mask(phoneInput);
                }
                if (docInput) {
                    Inputmask({ mask: ['999.999.999-99', '99.999.999/9999-99'], keepStatic: true }).mask(docInput);
                }
            }

            function initViaCepAutofill() {
                var cepInput = document.getElementById('profile_cep');
                if (!cepInput) {
                    return;
                }

                var feedback = document.getElementById('profile_cep_feedback');
                var streetInput = document.getElementById('profile_street');
                var neighborhoodInput = document.getElementById('profile_neighborhood');
                var cityInput = document.getElementById('profile_city');
                var stateInput = document.getElementById('profile_state');
                var lastCep = '';

                function setFeedback(message, isError) {
                    if (!feedback) {
                        return;
                    }

                    feedback.textContent = message || '';
                    feedback.classList.toggle('text-red-600', !!isError);
                    feedback.classList.toggle('text-slate-500', !isError);
                }

                function fetchCep() {
                    var digits = (cepInput.value || '').replace(/\D/g, '');
                    if (digits.length !== 8 || digits === lastCep) {
                        return;
                    }

                    lastCep = digits;
                    setFeedback('Buscando endereco...', false);

                    fetch('https://viacep.com.br/ws/' + digits + '/json/')
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (data) {
                            if (!data || data.erro) {
                                setFeedback('CEP nao encontrado. Complete o endereco manualmente.', true);
                                return;
                            }

                            if (streetInput && !streetInput.value) {
                                streetInput.value = data.logradouro || '';
                            }
                            if (neighborhoodInput && !neighborhoodInput.value) {
                                neighborhoodInput.value = data.bairro || '';
                            }
                            if (cityInput && !cityInput.value) {
                                cityInput.value = data.localidade || '';
                            }
                            if (stateInput && !stateInput.value) {
                                stateInput.value = (data.uf || '').toUpperCase();
                            }

                            setFeedback('Endereco preenchido automaticamente.', false);
                        })
                        .catch(function () {
                            setFeedback('Nao foi possivel consultar o CEP agora. Complete manualmente.', true);
                        });
                }

                cepInput.addEventListener('blur', fetchCep);
                cepInput.addEventListener('input', function () {
                    if ((cepInput.value || '').replace(/\D/g, '').length < 8) {
                        lastCep = '';
                        setFeedback('', false);
                    }
                });
            }

            function initProfileEnhancements() {
                applyProfileMasks();
                initViaCepAutofill();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initProfileEnhancements);
            } else {
                initProfileEnhancements();
            }
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('panel.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH G:\Tudo\MEU-SISTEMA\SOMOS_UNN\resources\views\panel\profile\edit.blade.php ENDPATH**/ ?>