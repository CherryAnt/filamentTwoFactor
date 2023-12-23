<x-filament-two-factor::grid-section md=1 title="{{ __('filament-two-factor::default.profile.password.heading') }}" description="{{ __('filament-two-factor::default.profile.password.subheading') }}">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">

            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit" class="align-right">
                    {{ __('filament-two-factor::default.profile.password.submit.label') }}
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-two-factor::grid-section>
