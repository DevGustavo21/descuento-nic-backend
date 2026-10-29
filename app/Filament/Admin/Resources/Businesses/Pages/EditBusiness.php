<?php

namespace App\Filament\Admin\Resources\Businesses\Pages;

use App\Filament\Admin\Resources\Businesses\BusinessResource;
use App\Models\Business;
use App\Notifications\BusinessApprovedNotification;
use App\Notifications\BusinessRejectedNotification;
use App\Support\Audit;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditBusiness extends EditRecord
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Business $record */
        $record = $this->getRecord();

        return [
            Action::make('saveHeader')
                ->label('Guardar')
                ->icon(Heroicon::OutlinedCheck)
                ->color('success')
                ->keyBindings(['mod+s'])
                ->action('save'),
            Action::make('approve')
                ->label('Aprobar')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn () => $record->status !== 'approved')
                ->requiresConfirmation()
                ->action(function () use ($record): void {
                    $record->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                        'rejection_reason' => null,
                        'rejected_at' => null,
                        'rejected_by' => null,
                    ]);

                    Audit::log('business.approved', $record);
                    $record->users()->each(fn ($user) => $user->notify(new BusinessApprovedNotification($record)));
                    Notification::make()->title('Negocio aprobado')->success()->send();
                    $this->refreshFormData(['status', 'approved_at', 'rejection_reason']);
                }),
            Action::make('reject')
                ->label('Rechazar')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('gray')
                ->outlined()
                ->visible(fn () => $record->status !== 'rejected')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Motivo del rechazo')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) use ($record): void {
                    $record->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                        'rejected_at' => now(),
                        'rejected_by' => auth()->id(),
                        'approved_at' => null,
                        'approved_by' => null,
                    ]);

                    Audit::log('business.rejected', $record, ['reason' => $data['rejection_reason']]);
                    $record->users()->each(fn ($user) => $user->notify(new BusinessRejectedNotification($record)));
                    Notification::make()->title('Negocio rechazado')->warning()->send();
                    $this->refreshFormData(['status', 'rejection_reason', 'rejected_at']);
                }),
            DeleteAction::make()
                ->label('Borrar')
                ->color('danger')
                ->visible(fn () => auth()->user()?->isAdmin()),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar')
            ->icon(Heroicon::OutlinedCheck)
            ->color('success');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar')
            ->color('gray')
            ->outlined();
    }
}
