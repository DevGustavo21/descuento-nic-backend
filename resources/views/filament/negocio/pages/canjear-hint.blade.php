<div class="mb-6 space-y-4">
    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-slate-700">
        <p class="font-semibold text-rose-800">Cómo canjear</p>
        <ol class="mt-2 list-decimal space-y-1 pl-4">
            <li>El cliente abre el cupón y muestra el <strong>código</strong> o el <strong>QR</strong>.</li>
            <li>Escaneá el QR con este teléfono (o escribí el código).</li>
            <li>Pedí el <strong>teléfono del cliente</strong> y registrá el canje.</li>
        </ol>
    </div>

    @if ($preview)
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cupón detectado</p>
            <p class="mt-1 font-semibold text-slate-900">{{ $preview['title'] }}</p>
            <p class="text-slate-600">{{ $preview['discount'] }} · quedan {{ $preview['remaining'] }} de {{ $preview['max'] }}</p>
        </div>
    @endif
</div>
