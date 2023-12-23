<?php

namespace CherryAnt\FilamentTwoFactor\Middleware;

use Closure;
use Illuminate\Http\Request;
use Filament\Facades\Filament;
use Symfony\Component\HttpFoundation\Response;

class MustTwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            filament()->auth()->check() &&
            !str($request->route()->getName())->contains('logout')
        ){
            $twofactor = filament('filament-two-factor');
            $myProfileRouteName = 'filament.' . filament()->getCurrentPanel()->getId() . '.pages.my-profile';
            $myProfileRouteParameters = [];

            if (filament()->hasTenancy()){
                if (!$tenantId = request()->route()->parameter('tenant')){
                    return $next($request);
                }
                $myProfileRouteParameters = ['tenant' => $tenantId];
                $twoFactorRoute = route('filament.' . filament()->getCurrentPanel()->getId() . '.auth.two-factor',['tenant'=>$tenantId, 'next' => request()->getRequestUri()]);
            } else {
                $twoFactorRoute = route('filament.' . filament()->getCurrentPanel()->getId() . '.auth.two-factor', ['next' => request()->getRequestUri()]);
            }

            if ($twofactor->shouldForceTwoFactor() && !$request->routeIs($myProfileRouteName)){
                return redirect()->route($myProfileRouteName, $myProfileRouteParameters);
            } else if (filament()->auth()->user()->hasConfirmedTwoFactor() && !filament()->auth()->user()->hasValidTwoFactorSession()) {
                return redirect($twoFactorRoute);
            }
        }
        // Fall through
        return $next($request);
    }
}