<?php

/**
 * Validates: Requirements 14.3, 14.6
 */

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\ContactMessage;

test('admin can read a message and it gets marked as read', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();
    $messageBody = 'Dusk test message body.';

    $message = ContactMessage::create([
        'name' => 'Dusk Sender',
        'email' => 'dusk@example.com',
        'subject' => 'Dusk Test Subject',
        'message' => $messageBody,
        'is_read' => false,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $message, $messageBody) {
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

        $browser->waitForText($messageBody, 5)
            ->assertSee($messageBody);

        // Verify marked as read in DB
        expect($message->fresh()->is_read)->toBeTrue();
    });

    $message->delete();
});

test('admin can delete a message with confirmation', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();
    $deleteSubject = 'Delete Test Subject';

    $message = ContactMessage::create([
        'name' => 'Delete Me',
        'email' => 'delete@example.com',
        'subject' => $deleteSubject,
        'message' => 'This message should be deleted.',
        'is_read' => false,
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin, $deleteSubject) {
        $browser->loginAs($admin)
            ->visit('/admin/mensajes')
            ->assertSee($deleteSubject);

        // Click delete button for this message (stop propagation prevents row click)
        $browser->script("
            const rows = document.querySelectorAll('tbody tr');
            for (const row of rows) {
                if (row.textContent.includes('{$deleteSubject}')) {
                    const btn = row.querySelector('button[title=\"Ezabatu\"]');
                    if (btn) btn.click();
                    break;
                }
            }
        ");

        // Wait for confirmation modal
        $browser->waitForText('Ziur zaude', 5)
            ->assertSee('Ziur zaude');

        // Confirm deletion in modal
        $browser->press('Ezabatu');

        $browser->pause(2000)
            ->assertDontSee($deleteSubject);
    });

    // Verify deleted from DB
    expect(ContactMessage::find($message->id))->toBeNull();
});
