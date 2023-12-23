<?php

namespace CherryAnt\FilamentTwoFactor\Pages;

use Filament\Pages\Page;

class MyProfilePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'profile';

    protected static string $view = 'filament-two-factor::filament.pages.my-profile';

    public function getTitle(): string
    {
        return __('filament-two-factor::default.profile.my_profile');
    }

    public function getHeading(): string
    {
        return __('filament-two-factor::default.profile.my_profile');
    }

    public function getSubheading(): ?string
    {
        return __('filament-two-factor::default.profile.subheading') ?? null;
    }

    public static function getSlug(): string
    {
        return filament('filament-two-factor')->slug();
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-two-factor::default.profile.profile');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public function getRegisteredMyProfileComponents(): array
    {
        return filament('filament-two-factor')->getRegisteredMyProfileComponents();
    }

}
