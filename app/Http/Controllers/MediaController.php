<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Support\BusinessLogo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function businessLogo(Business $business): BinaryFileResponse|Response
    {
        if ($business->logo_path && Storage::disk('public')->exists($business->logo_path)) {
            $absolute = Storage::disk('public')->path($business->logo_path);
            $mime = mime_content_type($absolute) ?: 'image/jpeg';

            return response()->file($absolute, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        $slug = $business->slug ?: 'descuento-nic';
        $static = public_path('logos/'.$slug.'.svg');

        if (is_file($static)) {
            return response()->file($static, [
                'Content-Type' => 'image/svg+xml',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        $svg = BusinessLogo::svgMarkup($business->name ?: 'DN', $slug);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
