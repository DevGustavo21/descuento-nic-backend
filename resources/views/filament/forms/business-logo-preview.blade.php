@php
    $url = $url ?? null;
    $name = $name ?? 'Negocio';
    $status = $status ?? 'pending';
    $statusLabel = match ($status) {
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
        default => 'En revisión',
    };
    $statusColor = match ($status) {
        'approved' => '#0f766e',
        'rejected' => '#e11d48',
        default => '#b45309',
    };
@endphp

<div style="display:flex;align-items:center;gap:16px;grid-column:1 / -1;margin-bottom:4px">
    <img
        src="{{ $url }}"
        alt="{{ $name }}"
        style="width:72px;height:72px;border-radius:16px;object-fit:cover;background:#fff;border:1px solid #e2e8f0"
    >
    <div>
        <p style="margin:0;font-weight:800;color:#0f172a;font-size:1.05rem">{{ $name }}</p>
        <p style="margin:4px 0 0;font-size:0.85rem;font-weight:700;color:{{ $statusColor }}">
            Estado: {{ $statusLabel }}
        </p>
    </div>
</div>
