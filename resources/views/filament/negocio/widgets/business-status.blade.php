@php
    $card = $this->getStatusCard();
    $business = $this->getBusiness();
    $tones = [
        'success' => ['bg' => '#ecfdf5', 'border' => '#99f6e4', 'title' => '#0f766e', 'text' => '#115e59', 'btn' => '#0f766e'],
        'warning' => ['bg' => '#fffbeb', 'border' => '#fde68a', 'title' => '#b45309', 'text' => '#92400e', 'btn' => '#b45309'],
        'danger' => ['bg' => '#fff1f2', 'border' => '#fecdd3', 'title' => '#be123c', 'text' => '#9f1239', 'btn' => '#e11d48'],
    ];
    $tone = $tones[$card['tone']] ?? $tones['warning'];
@endphp

<x-filament-widgets::widget>
    <div style="border:1px solid {{ $tone['border'] }};background:{{ $tone['bg'] }};border-radius:16px;padding:20px 22px;display:flex;gap:18px;align-items:center;justify-content:space-between;flex-wrap:wrap">
        <div style="display:flex;gap:14px;align-items:center;min-width:240px;flex:1">
            @if ($business)
                <img
                    src="{{ $business->logoUrl() }}"
                    alt="{{ $business->name }}"
                    style="width:56px;height:56px;border-radius:14px;object-fit:cover;background:#fff;border:1px solid rgba(15,23,42,.08)"
                >
            @endif
            <div>
                <p style="margin:0;font-size:0.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:{{ $tone['title'] }}">
                    Estado de la cuenta
                </p>
                <h3 style="margin:4px 0 0;font-size:1.15rem;font-weight:800;color:#0f172a;line-height:1.25">
                    {{ $card['title'] }}
                </h3>
                <p style="margin:6px 0 0;font-size:0.92rem;color:{{ $tone['text'] }};max-width:46rem;line-height:1.45">
                    {{ $card['body'] }}
                </p>
            </div>
        </div>

        @if ($card['cta_url'] && $card['cta_label'])
            <a
                href="{{ $card['cta_url'] }}"
                style="display:inline-flex;align-items:center;justify-content:center;background:{{ $tone['btn'] }};color:#fff;font-weight:700;font-size:0.9rem;padding:10px 16px;border-radius:12px;text-decoration:none;white-space:nowrap"
            >
                {{ $card['cta_label'] }}
            </a>
        @endif
    </div>
</x-filament-widgets::widget>
