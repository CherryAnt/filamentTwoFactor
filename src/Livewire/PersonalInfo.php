<?php

namespace CherryAnt\FilamentTwoFactor\Livewire;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class PersonalInfo extends MyProfileComponent
{
    protected string $view = 'filament-two-factor::livewire.personal-info';

    public ?array $data = [];

    public $user;

    public $userClass;

    public array $only = ['name', 'email'];

    public static $sort = 10;

    public function mount()
    {
        $this->user = Filament::getCurrentPanel()->auth()->user();
        $this->userClass = get_class($this->user);

        $this->form->fill($this->user->only($this->only));
    }

    protected function getProfileFormSchema()
    {
        $groupFields = Forms\Components\Group::make([
            $this->getNameComponent(),
            $this->getEmailComponent(),
        ])->columnSpan(1);

        return [$groupFields];
    }

    protected function getNameComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('name')
            ->required()
            ->label(__('filament-two-factor::default.fields.name'));
    }

    protected function getEmailComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('email')
            ->required()
            ->email()
            ->unique($this->userClass, ignorable: $this->user)
            ->label(__('filament-two-factor::default.fields.email'));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getProfileFormSchema())->columns(1)
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = collect($this->form->getState())->only($this->only)->all();
        $this->user->update($data);
        $this->sendNotification();
    }

    protected function sendNotification(): void
    {
        Notification::make()
            ->success()
            ->title(__('filament-two-factor::default.profile.personal_info.notify'))
            ->send();
    }
}
