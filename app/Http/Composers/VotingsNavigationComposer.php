<?php

namespace App\Http\Composers;

use App\Models\Voting;
use App\SupportedLocales;
use App\Models\Construction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class VotingsNavigationComposer
{
    public function compose(View $view): void
    {
        try {
            if (! $this->shouldResolveFrontNavigation()) {
                $view->with('showVotingsLink', false);
                $view->with('activeConstructionsNav', collect());

                return;
            }

            $hasOpenVotings = once(fn (): bool => Voting::query()->publishedOpen()->exists());
            $view->with('showVotingsLink', $hasOpenVotings);

            if (! Auth::check()) {
                $view->with('activeConstructionsNav', collect());

                return;
            }

            $view->with('activeConstructionsNav', once(fn () => Construction::query()
                ->active()
                ->orderBy('starts_at')
                ->get(['title', 'slug'])));
        } catch (\Throwable $exception) {
            report($exception);
            $view->with('showVotingsLink', false);
            $view->with('activeConstructionsNav', collect());
        }
    }

    private function shouldResolveFrontNavigation(): bool
    {
        $routeName = request()->route()?->getName();

        if (! is_string($routeName) || $routeName === '') {
            return false;
        }

        $baseRouteName = SupportedLocales::baseRouteName($routeName);

        return in_array($baseRouteName, [
            'home',
            'notices',
            'gallery',
            'contact',
            'private',
            'password.request',
            'password.reset',
            'privacy-policy',
            'legal-notice',
            'cookie-policy',
            'votings',
            'votings.results',
            'votings.pdf.delegated',
            'votings.pdf.in_person',
            'constructions',
            'constructions.show',
            'profile',
        ], true);
    }
}
