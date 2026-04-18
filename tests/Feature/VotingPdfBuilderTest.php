<?php

use App\Models\Voting;
use App\Models\VotingOption;
use App\Services\VotingPdfBuilder;

it('builds delegated voting pdf payload using settings and active votings', function () {
    createSetting('front_site_name', 'Madaia 33');
    createSetting('votings_pdf_delegated_text_eu', '<p>Delegatua EU</p>');
    createSetting('votings_pdf_delegated_text_es', '<p>Delegado ES</p>');
    createSetting('votings_explanation_text_eu', '<p>Bozketen azalpena EU</p>');
    createSetting('votings_explanation_text_es', '<p>Explicacion votaciones ES</p>');

    $voting = Voting::factory()->current()->create([
        'name_eu' => 'Izena EU',
        'name_es' => 'Nombre ES',
        'question_eu' => 'Galdera EU',
        'question_es' => 'Pregunta ES',
    ]);

    VotingOption::factory()->create([
        'voting_id' => $voting->id,
        'label_eu' => 'Aukera EU',
        'label_es' => 'Opcion ES',
        'position' => 1,
    ]);

    $payload = app(VotingPdfBuilder::class)->build('delegated');

    expect($payload['siteName'])->toBe('Madaia 33')
        ->and($payload['leftHeader'])->toBe('Madaia 33 Jabeen Erkidegoa')
        ->and($payload['rightHeader'])->toBe('Comunidad de Propietarios/a Madaia 33')
        ->and($payload['introEuHtml'])->toBe('<p>Delegatua EU</p>')
        ->and($payload['introEsHtml'])->toBe('<p>Delegado ES</p>')
        ->and($payload['votingsExplanationEuHtml'])->toBe('<p>Bozketen azalpena EU</p>')
        ->and($payload['votingsExplanationEsHtml'])->toBe('<p>Explicacion votaciones ES</p>')
        ->and($payload['votings'])->toHaveCount(1)
        ->and($payload['votings'][0]['question_eu'])->toBe('Galdera EU')
        ->and($payload['votings'][0]['question_es'])->toBe('Pregunta ES')
        ->and($payload['votings'][0]['options'][0]['label_eu'])->toBe('Aukera EU')
        ->and($payload['votings'][0]['options'][0]['label_es'])->toBe('Opcion ES');
});

it('builds in-person intro text from in-person settings keys', function () {
    createSetting('votings_pdf_in_person_text_eu', '<p>Presentzial EU</p>');
    createSetting('votings_pdf_in_person_text_es', '<p>Presencial ES</p>');
    createSetting('votings_explanation_text_eu', '<p>Bozketen azalpena EU</p>');
    createSetting('votings_explanation_text_es', '<p>Explicacion votaciones ES</p>');

    $payload = app(VotingPdfBuilder::class)->build('in_person');

    expect($payload['documentType'])->toBe('in_person')
        ->and($payload['introEuHtml'])->toBe('<p>Presentzial EU</p>')
        ->and($payload['introEsHtml'])->toBe('<p>Presencial ES</p>')
        ->and($payload['votingsExplanationEuHtml'])->toBe('<p>Bozketen azalpena EU</p>')
        ->and($payload['votingsExplanationEsHtml'])->toBe('<p>Explicacion votaciones ES</p>');
});

it('filters payload votings by selected voting ids', function () {
    $firstVoting = Voting::factory()->current()->create([
        'name_eu' => 'Lehen bozketa',
    ]);
    $secondVoting = Voting::factory()->current()->create([
        'name_eu' => 'Bigarren bozketa',
    ]);

    VotingOption::factory()->create([
        'voting_id' => $firstVoting->id,
        'position' => 1,
    ]);
    VotingOption::factory()->create([
        'voting_id' => $secondVoting->id,
        'position' => 1,
    ]);

    $payload = app(VotingPdfBuilder::class)->build('delegated', [$secondVoting->id]);

    expect($payload['votings'])->toHaveCount(1)
        ->and($payload['votings'][0]['name_eu'])->toBe('Bigarren bozketa');
});

it('includes selected closed voting ids in payload', function () {
    $closedVoting = Voting::factory()->future()->unpublished()->create([
        'name_eu' => 'Itxitako bozketa',
    ]);

    VotingOption::factory()->create([
        'voting_id' => $closedVoting->id,
        'position' => 1,
    ]);

    $payload = app(VotingPdfBuilder::class)->build('delegated', [$closedVoting->id]);

    expect($payload['votings'])->toHaveCount(1)
        ->and($payload['votings'][0]['name_eu'])->toBe('Itxitako bozketa');
});
