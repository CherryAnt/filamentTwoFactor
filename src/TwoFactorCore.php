<?php

namespace CherryAnt\FilamentTwoFactor;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use CherryAnt\FilamentTwoFactor\Livewire\PersonalInfo;
use CherryAnt\FilamentTwoFactor\Livewire\TwoFactorAuthentication;
use CherryAnt\FilamentTwoFactor\Livewire\UpdatePassword;
use CherryAnt\FilamentTwoFactor\Middleware\MustTwoFactor;
use CherryAnt\FilamentTwoFactor\Pages\TwoFactorPage;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorCore implements Plugin
{
    use EvaluatesClosures;

    protected $engine;

    protected $cache;

    protected $twoFactorRouteAction;

    protected $registeredMyProfileComponents = [];

    protected $passwordUpdateRules = ['min:8'];

    protected bool $passwordUpdateRequireCurrent = true;

    public function __construct(Google2FA $engine, ?Repository $cache = null)
    {
        $this->engine = $engine;
        $this->cache = $cache;
        $this->twoFactorRouteAction = TwoFactorPage::class;
    }

    public function getId(): string
    {
        return 'filament-two-factor';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages($this->preparePages())
            ->authMiddleware([MustTwoFactor::class]);
        Livewire::component('two-factor-page', Pages\TwoFactorPage::class);
    }

    protected function preparePages(): array
    {
        $collection = collect();
        $collection->push(Pages\MyProfilePage::class);

        return $collection->toArray();
    }

    public function boot(Panel $panel): void
    {
        Livewire::component('two_factor_authentication', TwoFactorAuthentication::class);
        Livewire::component('personal_info', PersonalInfo::class);
        Livewire::component('update_password', UpdatePassword::class);
        $this->myProfileComponents([
            'personal_info' => PersonalInfo::class,
            'update_password' => UpdatePassword::class,
            'two_factor_authentication' => TwoFactorAuthentication::class,
        ]);

        if ($panel->hasTenancy()) {
            $tenantId = request()->route()->parameter('tenant');
            if ($tenantId && $tenant = app($panel->getTenantModel())::where($panel->getTenantSlugAttribute() ?? 'id', $tenantId)->first()) {
                $panel->userMenuItems([
                    'account' => MenuItem::make()->url(Pages\MyProfilePage::getUrl(panel: $panel->getId(), tenant: $tenant)),
                ]);
            }
        } else {
            $panel->userMenuItems([
                'account' => MenuItem::make()->url(Pages\MyProfilePage::getUrl()),
            ]);
        }
    }

    public function auth()
    {
        return Filament::getCurrentPanel()->auth();
    }

    public function getCurrentPanel()
    {
        return Filament::getCurrentPanel();
    }

    public function getTwoFactorRouteAction(): string | Closure | array | null
    {
        return $this->twoFactorRouteAction;
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function generateSecretKey()
    {
        return $this->engine->generateSecretKey();
    }

    public function getTwoFactorQrCodeSvg(string $url)
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(150, 1, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
                new SvgImageBackEnd()
            )
        ))->writeString($url);

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    public function getQrCodeUrl($companyName, $companyEmail, $secret)
    {
        return $this->engine->getQRCodeUrl($companyName, $companyEmail, $secret);
    }

    public function verify(string $code, ?Authenticatable $user = null)
    {
        if (is_null($user)) {
            $user = Filament::auth()->user();
        }
        $secret = decrypt($user->two_factor_secret);

        $timestamp = $this->engine->verifyKeyNewer(
            $secret,
            $code,
            optional($this->cache)->get($key = 'filament.2fa_codes.' . md5($code))
        );

        if ($timestamp !== false) {
            optional($this->cache)->put($key, $timestamp, ($this->engine->getWindow() ?: 1) * 60);

            return true;
        }

        return false;
    }

    public function slug()
    {
        return 'profile';
    }

    public function myProfileComponents(array $components)
    {
        $this->registeredMyProfileComponents = [
            ...$components,
            ...$this->registeredMyProfileComponents,
        ];

        return $this;
    }

    public function getRegisteredMyProfileComponents(): array
    {
        $components = collect($this->registeredMyProfileComponents)->filter(
            fn (string $component) => $component::canView()
        )->sortBy(
            fn (string $component) => $component::getSort()
        );

        //$components = $components->only(['two_factor_authentication']);
        return $components->all();
    }

    public function passwordUpdateRules(array | Password $rules, bool $requiresCurrentPassword = true)
    {
        $this->passwordUpdateRequireCurrent = $requiresCurrentPassword;
        $this->passwordUpdateRules = $rules;

        return $this;
    }

    public function getPasswordUpdateRequiresCurrent()
    {
        return $this->passwordUpdateRequireCurrent;
    }

    public function getPasswordUpdateRules()
    {
        return $this->passwordUpdateRules;
    }
}
