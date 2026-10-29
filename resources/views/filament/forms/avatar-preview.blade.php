@php
    $url = $url ?? null;
    $name = $name ?? 'Usuario';
    $initial = mb_strtoupper(mb_substr(trim($name), 0, 1) ?: 'U');
@endphp

<div style="display:flex;align-items:center;gap:16px;margin-bottom:4px">
    @if ($url)
        <img
            src="{{ $url }}"
            alt="{{ $name }}"
            style="width:88px;height:88px;border-radius:999px;object-fit:cover;border:2px solid #e2e8f0;background:#f8fafc"
        >
    @else
        <div style="width:88px;height:88px;border-radius:999px;display:grid;place-items:center;background:#e11d48;color:#fff;font-weight:800;font-size:1.75rem;border:2px solid #e2e8f0">
            {{ $initial }}
        </div>
    @endif
    <div>
        <p style="margin:0;font-weight:700;color:#0f172a">{{ $name }}</p>
        <p style="margin:4px 0 0;font-size:0.875rem;color:#64748b">
            {{ $url ? 'Foto de perfil actual' : 'Todavía no hay foto cargada' }}
        </p>
    </div>
</div>
