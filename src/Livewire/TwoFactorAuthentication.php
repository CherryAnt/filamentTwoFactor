<?php

namespace CherryAnt\FilamentTwoFactor\Livewire;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Facades\Filament;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use Filament\Actions\Contracts\HasActions;
use CherryAnt\FilamentTwoFactor\Actions\PasswordButtonAction;

class TwoFactorAuthentication extends MyProfileComponent
{
    protected string $view = "filament-two-factor::livewire.two-factor-authentication";

    // public ?array $data = [];
    public $user;
    public $code;
    public bool $showRecoveryCodes = false;

    public static $sort = 30;

    public function mount()
    {
        $this->user = Filament::getCurrentPanel()->auth()->user();
    }

    public function enableAction(): Action
    {
        return PasswordButtonAction::make('enable')
            ->label(__('filament-two-factor::default.profile.2fa.actions.enable'))
            ->action(function(){
                // sleep(1);
                $this->user->enableTwoFactorAuthentication();
                Notification::make()
                    ->success()
                    ->title(__('filament-two-factor::default.profile.2fa.enabled.notify'))
                    ->send();
            });
    }

    public function disableAction(): Action
    {
        return PasswordButtonAction::make('disable')
            ->label(__('filament-two-factor::default.profile.2fa.actions.disable'))
            ->color('primary')
            ->requiresConfirmation()
            ->action(function(){
                $this->user->disableTwoFactorAuthentication();
                Notification::make()
                    ->warning()
                    ->title(__('filament-two-factor::default.profile.2fa.disabling.notify'))
                    ->send();
            });
    }

    public function confirmAction(): Action
    {
        return Action::make('confirm')
            ->color('success')
            ->label(__('filament-two-factor::default.profile.2fa.actions.confirm_finish'))
            ->modalWidth('sm')
            ->form([
                Forms\Components\TextInput::make('code')
                    ->label(__('filament-two-factor::default.fields.2fa_code'))
                    ->placeholder('###-###')
                    ->required()
            ])
            ->action(function($data,$action,$livewire){
                if (!filament('filament-two-factor')->verify(code:$data['code'])){
                    $livewire->addError('mountedActionsData.0.code', __('filament-two-factor::default.profile.2fa.confirmation.invalid_code'));
                    $action->halt();
                }
                $this->user->confirmTwoFactorAuthentication();
                $this->user->setTwoFactorSession();
                Notification::make()
                    ->success()
                    ->title(__('filament-two-factor::default.profile.2fa.confirmation.success_notification'))
                    ->send();
            });
    }

    public function regenerateCodesAction(): Action
    {
        return PasswordButtonAction::make('regenerateCodes')
            ->label(__('filament-two-factor::default.profile.2fa.actions.regenerate_codes'))
            ->requiresConfirmation()
            ->action(function(){
                // These needs to regenerate the codes, then show the section.
                $this->user->reGenerateRecoveryCodes();
                $this->showRecoveryCodes = true;
                Notification::make()
                    ->success()
                    ->title(__('filament-two-factor::default.profile.2fa.regenerate_codes.notify'))
                    ->send();
            });

    }

    public function getRecoveryCodesProperty(): Collection
    {
        return collect($this->user->two_factor_recovery_codes ?? []);
    }


    public function getTwoFactorQrCode()
    {
        return filament('filament-two-factor')->getTwoFactorQrCodeSvg($this->user->getTwoFactorQrCodeUrl());
    }

    public function toggleRecoveryCodes()
    {
        $this->showRecoveryCodes = !$this->showRecoveryCodes;
    }

    public function showRequiresTwoFactorAlert()
    {
        return filament('filament-two-factor')->shouldForceTwoFactor();
    }

}
