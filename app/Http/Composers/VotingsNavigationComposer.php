<?php

namespace App\Http\Composers;

use App\Models\Voting;
use Illuminate\Contracts\View\View;

class VotingsNavigationComposer
{
    public function compose(View $view): void
    {
        try {
            $hasOpenVotings = Voting::query()->publishedOpen()->exists();
            $view->with('showVotingsLink', $hasOpenVotings);
        } catch (\Throwable $exception) {
            report($exception);
            $view->with('showVotingsLink', false);
        }
    }
}
