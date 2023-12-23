<?php

namespace CherryAnt\FilamentTwoFactor\Traits;

use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use CherryAnt\FilamentTwoFactor\Models\TwoFactorSession;

trait TwoFactorAuthenticatable
{
    public static function bootTwoFactorAuthenticatable()
    {
        static::deleting(function ($model) {
            $model->twofactorSessions()->get()->each->delete();
        });
    }
    public function initializeTwoFactorAuthenticatable()
    {
        $this->with[] = "twofactorSessions";
    }

    public function twofactorSessions()
    {
        return $this->morphMany(TwoFactorSession::class,'authenticatable');
    }

    public function twofactorSession(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->twofactorSessions->first()
        );
    }

    public function hasEnabledTwoFactor(): bool
    {
        return $this->twofactorSession?->is_enabled ?? false;
    }

    public function hasConfirmedTwoFactor(): bool
    {
        return $this->twofactorSession?->is_confirmed ?? false;
    }

    public function twoFactorRecoveryCodes(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->twofactorSession ? json_decode(decrypt(
                $this->twofactorSession->two_factor_recovery_codes),true) : null
        );
    }

    public function twoFactorSecret(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->twofactorSession?->two_factor_secret
        );
    }

    public function enableTwoFactorAuthentication()
    {
        $twoFactorData = [
            'two_factor_secret' => encrypt(filament('filament-two-factor')->getEngine()->generateSecretKey()),
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
        ];
        if ($this->twofactor_session){
            $this->disableTwoFactorAuthentication(); // Delete the session if it exists.
        }
        $this->twofactorSession = $this->twofactorSessions()->create($twoFactorData);
        $this->load('twofactorSessions');
    }

    public function disableTwoFactorAuthentication()
    {
        $this->twofactorSession?->delete();
    }

    public function confirmTwoFactorAuthentication()
    {
        $this->twofactorSession?->confirm();
        $this->setTwoFactorSession();
    }

    public function setTwoFactorSession(?int $lifetime=null)
    {
        $this->twofactorSession->setSession($lifetime);
    }

    public function hasValidTwoFactorSession(): bool
    {
        return $this->twofactorSession?->is_valid ?? false;
    }

    public function generateRecoveryCodes()
    {
        return encrypt(json_encode(Collection::times(8, function () {
            return Str::random(10) . '-' . Str::random(10);;
        })->all()));
    }

    public function getTwoFactorQrCodeUrl()
    {
        return filament('filament-two-factor')->getQrCodeUrl(
            config('app.name'),
            $this->email,
            decrypt($this->twofactorSession->two_factor_secret)
        );
    }

    public function reGenerateRecoveryCodes()
    {
        $this->twofactor_session->forceFill([
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
        ])->save();
    }

}
