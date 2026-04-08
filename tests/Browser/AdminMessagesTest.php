<?php

/**
 * Validates: Requirements 14.3, 14.6
 */

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\ContactMessage;

test('admin can read a message and it gets marked as read', function () {
    $admin = User::where('email', 'admin@madaia33.eus')->firstOrFail();

    $message = ContactMessage::create([
        'name' => 'Dusk Sender',
        'email' => 'dusk@example.com',
        'subject' => 'Dusk Test Subject',
        'message' => 'Dusk test message body.',
        'is_read' => false,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $message) {
        $browser->loginAs($admin)
            ->visit('/admin/mensajes')
            ->assertSee('Dusk Sender')
            ->assertSee('Dusk Test Subject');

        // Click the row to open message — should auto-mark as read
        $browser->script("
            const rows = document.querySelectorAll('tbody tr');
            for (const row of rows) {
                if (row.textContent.includes('Dusk Test Subject')) {
                    row.click();
                    break;
                }
            }
        ");

        $browser->waitForText('Dusk test message body.', 5)
            ->assertSee('Dusk test message body.');

        // Verify marked as read in DB
        expect($message->fresh()->is_read)->toBeTrue();
    });

    $message->delete();
});

test('admin can delete a message with confirmation', function () {
    $admin = User::where('email', 'admin@madaia33.eus')->firstOrFail();

    $message = ContactMessage::create([
        'name' => 'Delete Me',
        'email' => 'delete@example.com',
        'subject' => 'Delete Test Subject',
        'message' => 'This message should be deleted.',
        'is_read' => false,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin) {
        $browser->loginAs($admin)
            ->visit('/admin/mensajes')
            ->assertSee('Delete Test Subject');

        // Click delete button for this message (stop propagation prevents row click)
        $browser->script("
            const rows = document.querySelectorAll('tbody tr');
            for (const row of rows) {
                if (row.textContent.includes('Delete Test Subject')) {
                    const btn = row.querySelector('button.text-red-600');
                    if (btn) btn.click();
                    break;
                }
            }
        ");

        // Wait for confirmation modal
        $browser->waitForText('Ziur zaude', 5)
            ->assertSee('Ziur zaude');

        // Confirm deletion in modal — click the red delete button in the modal
        $browser->script("
            const modal = document.querySelector('[role=\"dialog\"]');
            if (modal) {
                const btn = modal.querySelector('button.bg-red-600');
                if (btn) btn.click();
            }
        ");

        $browser->pause(1500)
            ->assertDontSee('Delete Test Subject');
    });

    // Verify deleted from DB
    expect(ContactMessage::find($message->id))->toBeNull();
});
