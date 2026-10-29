<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class PanelBrand
{
    public static function logoHtml(string $subtitle = ''): HtmlString
    {
        $sub = $subtitle !== ''
            ? '<span style="display:block;font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.02em;margin-top:1px">'.e($subtitle).'</span>'
            : '';

        return new HtmlString(
            '<span style="display:inline-flex;align-items:center;gap:10px">'
            .'<span style="display:grid;place-items:center;width:34px;height:34px;border-radius:10px;background:#e11d48;color:#fff;font-weight:800;font-size:13px;font-family:Plus Jakarta Sans,ui-sans-serif,system-ui,sans-serif">D%</span>'
            .'<span style="line-height:1.1">'
            .'<span style="font-weight:800;font-size:1.05rem;letter-spacing:-0.02em;color:#0f172a;font-family:Plus Jakarta Sans,ui-sans-serif,system-ui,sans-serif">Descuento<span style="color:#e11d48">Nic</span></span>'
            .$sub
            .'</span></span>'
        );
    }
}
