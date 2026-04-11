<?php

namespace App\Http\Responses;

use App\SupportedLocales;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
  public function toResponse($request): RedirectResponse
  {
    $user = $request->user();

    if ($user !== null && $user->owner()->exists()) {
      return redirect()->intended(
        route(SupportedLocales::routeName('profile'), absolute: false),
      );
    }

    return redirect()->intended('/admin');
  }
}
