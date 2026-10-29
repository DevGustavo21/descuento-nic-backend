<?php

namespace App\Filament\Admin\Resources\Redemptions\Pages;

use App\Filament\Admin\Resources\Redemptions\RedemptionResource;
use App\Models\Redemption;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListRedemptions extends ListRecords
{
    protected static string $resource = RedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exportar CSV')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    protected function exportCsv(): StreamedResponse
    {
        $filename = 'canjes-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'fecha', 'codigo', 'titulo', 'negocio', 'fuente', 'notas']);

            Redemption::query()
                ->with(['coupon', 'business'])
                ->latest('redeemed_at')
                ->chunk(200, function ($rows) use ($handle): void {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->id,
                            optional($row->redeemed_at)?->toDateTimeString(),
                            $row->coupon?->code,
                            $row->coupon?->title,
                            $row->business?->name,
                            $row->source,
                            $row->notes,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
