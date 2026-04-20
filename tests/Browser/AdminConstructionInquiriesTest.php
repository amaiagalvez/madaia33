<?php

use App\Models\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\Construction;
use App\Models\ConstructionInquiry;

test('admin can view the construction inquiries inbox', function () {
    $admin = User::where('email', 'info@madaia33.eus')->firstOrFail();
    $construction = Construction::factory()->create([
        'title' => 'Atari nagusiko obra',
    ]);
    ConstructionInquiry::factory()->create([
        'construction_id' => $construction->id,
        'name' => 'Ane Bizilaguna',
        'subject' => 'Noiz amaituko da?',
        'message' => 'Atariko obraren amaiera data jakin nahi dut.',
    ]);

    /** @var DuskTestCase $this */
    $this->browse(function (Browser $browser) use ($admin): void {
        $browser->loginAs($admin)
            ->visit('/admin/consultas-obras')
            ->waitFor('[data-construction-inquiry-row]')
            ->assertSee(__('admin.construction_inquiries.menu'))
            ->assertSee('Ane Bizilaguna');
    });
});
