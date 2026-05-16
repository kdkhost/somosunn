{{--
Form Tailwind de configuracao de UM provedor S3 (parcial reutilizada
nas abas IDrive e2, Wasabi e AWS S3 da view storage-providers).

Variaveis recebidas:
  - $provider : array com key, name, access_key, secret_masked, bucket,
                region, endpoint, url, path_style, configured
  - $isActive : bool

Spec: .kiro/specs/multi-provider-s3-storage (task 5.2)
--}}
@php
    $providerKey = $provider['key'];
    $hints = [
        'idrive' => 'Path Style ativado e recomendado. Endpoint exemplo: https://b1l1.la4.idrivee2-XX.com',
        'wasabi' => 'Endpoint regional exemplo: s3.us-east-1.wasabisys.com (sem https://). Path Style ativado.',
        'aws'    => 'Endpoint pode ficar vazio (usa endpoint padrao da AWS). Path Style desativado.',
    ];
    $hintText = $hints[$providerKey] ?? '';
@endphp

<form action="{{ url('/painel/admin/settings/storage-providers/' . $providerKey) }}"
      method="POST"
      class="space-y-4">
    @csrf
    <input type="hidden" name="provider" value="{{ $providerKey }}">

    @if ($hintText)
        <div class="rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            <i class="fas fa-info-circle mr-2"></i> {{ $hintText }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="{{ $providerKey }}_access_key_p" class="block text-sm font-medium text-gray-700 mb-1">
                Access Key <span class="text-red-500">*</span>
            </label>
            <input type="text" id="{{ $providerKey }}_access_key_p" name="access_key"
                   value="{{ $provider['access_key'] }}" autocomplete="off"
                   placeholder="ex: AKIA..."
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#1F5EDB] focus:ring focus:ring-[#1F5EDB]/20">
        </div>
        <div>
            <label for="{{ $providerKey }}_secret_key_p" class="block text-sm font-medium text-gray-700 mb-1">
                Secret Key <span class="text-red-500">*</span>
            </label>
            <div class="flex">
                <input type="password" id="{{ $providerKey }}_secret_key_p" name="secret_key"
                       autocomplete="new-password"
                       placeholder="{{ $provider['secret_masked'] ? 'atual: ' . $provider['secret_masked'] : 'cole o secret aqui' }}"
                       class="flex-1 rounded-l-md border-gray-300 shadow-sm focus:border-[#1F5EDB] focus:ring focus:ring-[#1F5EDB]/20">
                <button type="button" tabindex="-1"
                        class="btn-toggle-secret rounded-r-md border border-l-0 border-gray-300 bg-gray-50 px-3 hover:bg-gray-100"
                        data-target="{{ $providerKey }}_secret_key_p"
                        title="Mostrar/ocultar">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @if ($provider['secret_masked'])
                <p class="mt-1 text-xs text-gray-500">
                    Atualmente: <code class="bg-gray-100 px-1.5 py-0.5 rounded">{{ $provider['secret_masked'] }}</code>.
                    Preencha somente para alterar.
                </p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="{{ $providerKey }}_bucket_p" class="block text-sm font-medium text-gray-700 mb-1">
                Bucket <span class="text-red-500">*</span>
            </label>
            <input type="text" id="{{ $providerKey }}_bucket_p" name="bucket"
                   value="{{ $provider['bucket'] }}" autocomplete="off"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#1F5EDB] focus:ring focus:ring-[#1F5EDB]/20">
        </div>
        <div>
            <label for="{{ $providerKey }}_region_p" class="block text-sm font-medium text-gray-700 mb-1">
                Region <span class="text-red-500">*</span>
            </label>
            <input type="text" id="{{ $providerKey }}_region_p" name="region"
                   value="{{ $provider['region'] }}" autocomplete="off"
                   placeholder="ex: us-east-1"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#1F5EDB] focus:ring focus:ring-[#1F5EDB]/20">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="{{ $providerKey }}_endpoint_p" class="block text-sm font-medium text-gray-700 mb-1">
                Endpoint
            </label>
            <input type="text" id="{{ $providerKey }}_endpoint_p" name="endpoint"
                   value="{{ $provider['endpoint'] }}" autocomplete="off"
                   placeholder="(opcional - vazio = endpoint padrao do provedor)"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#1F5EDB] focus:ring focus:ring-[#1F5EDB]/20">
            <p class="mt-1 text-xs text-gray-500">Para Wasabi/IDrive use o host (s3.us-east-1.wasabisys.com). Para AWS deixe vazio.</p>
        </div>
        <div>
            <label for="{{ $providerKey }}_url_p" class="block text-sm font-medium text-gray-700 mb-1">
                URL Publica (CDN)
            </label>
            <input type="text" id="{{ $providerKey }}_url_p" name="url"
                   value="{{ $provider['url'] }}" autocomplete="off"
                   placeholder="(opcional)"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#1F5EDB] focus:ring focus:ring-[#1F5EDB]/20">
            <p class="mt-1 text-xs text-gray-500">URL base usada para servir arquivos publicos. Vazio = URL gerada pelo SDK.</p>
        </div>
    </div>

    <div>
        <label class="inline-flex items-center text-sm">
            <input type="checkbox" name="path_style" value="1" id="{{ $providerKey }}_path_style_p"
                   {{ $provider['path_style'] ? 'checked' : '' }}
                   class="rounded border-gray-300 text-[#1F5EDB] focus:ring-[#1F5EDB]">
            <span class="ml-2 text-gray-700">Usar Path-Style URLs</span>
        </label>
        <p class="mt-1 text-xs text-gray-500">
            Recomendado para IDrive e2 e Wasabi. AWS S3 normalmente usa Virtual Hosted Style (desativado).
        </p>
    </div>

    <div class="flex items-center justify-end gap-3 pt-2">
        <button type="button"
                class="btn-test-provider inline-flex items-center rounded-md border border-[#177FD6] bg-white px-4 py-2 text-sm font-medium text-[#177FD6] hover:bg-[#177FD6]/5 disabled:opacity-50"
                data-provider="{{ $providerKey }}"
                data-name="{{ $provider['name'] }}"
                {{ $provider['configured'] ? '' : 'disabled' }}>
            <i class="fas fa-vial mr-2"></i> Testar Conexao
        </button>
        <button type="submit"
                class="inline-flex items-center rounded-md bg-[#1F5EDB] px-4 py-2 text-sm font-medium text-white shadow hover:bg-[#1D3FC4]">
            <i class="fas fa-save mr-2"></i> Salvar {{ $provider['name'] }}
        </button>
    </div>
</form>
