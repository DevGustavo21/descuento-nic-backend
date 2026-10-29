@if (session()->has('impersonator_id'))
    <div style="background:#0f172a;color:#fff;padding:10px 16px;display:flex;align-items:center;justify-content:center;gap:12px;font-size:14px;font-weight:600">
        <span>Estás viendo el panel como {{ auth()->user()?->name }} (modo soporte).</span>
        <form method="POST" action="{{ route('impersonation.stop') }}" style="margin:0">
            @csrf
            <button type="submit" style="background:#e11d48;color:#fff;border:0;border-radius:8px;padding:6px 12px;font-weight:700;cursor:pointer">
                Volver al admin
            </button>
        </form>
    </div>
@endif
