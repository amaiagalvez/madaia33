<?php

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Hash;

test('superadmin can reset a user password from users list with confirmation modal', function () {
  $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();

  $targetUser = User::factory()->create([
    'name' => 'Dusk Reset Password User',
    'email' => 'dusk-reset-user@example.com',
    'password' => 'old-secret-pass',
  ]);

  /** @var DuskTestCase $this */
  $this->browse(function (Browser $browser) use ($admin, $targetUser) {
    $browser->loginAs($admin)
      ->visit('/admin/erabiltzaileak')
      ->waitFor('[data-action="reset-user-password-' . $targetUser->id . '"]', 10)
      ->click('[data-action="reset-user-password-' . $targetUser->id . '"]')
      ->waitFor('[data-user-reset-password-modal]', 5)
      ->assertPresent('[data-user-reset-password-modal]')
      ->click('[data-action="confirm-reset-user-password"]')
      ->pause(600)
      ->assertMissing('[data-user-reset-password-modal]');
  });

  expect(Hash::check('123456789', $targetUser->fresh()->password))->toBeTrue();
});
