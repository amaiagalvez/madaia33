<?php

use App\SupportedLocales;
use Illuminate\Support\ViewErrorBag;

it('renders the same toolbar controls for both bilingual editor panes', function () {
    $localeConfigs = [
        SupportedLocales::BASQUE => [
            'field' => 'privacyContentEu',
            'fieldLabel' => 'Lege testua',
            'value' => '<p>Euskarazko edukia</p>',
        ],
        SupportedLocales::SPANISH => [
            'field' => 'privacyContentEs',
            'fieldLabel' => 'Texto legal',
            'value' => '<p>Contenido en castellano</p>',
        ],
    ];

    $view = test()->blade(
        '<x-admin.bilingual-rich-text-tabs
            title="Legal text"
            :locale-configs="$localeConfigs"
        />',
        ['errors' => new ViewErrorBag, 'localeConfigs' => $localeConfigs]
    );

    $html = (string) $view;

    $view->assertSee('data-bilingual-field="privacyContentEu"', false);
    $view->assertSee('data-bilingual-pane="' . SupportedLocales::BASQUE . '"', false);
    $view->assertSee('data-bilingual-pane="' . SupportedLocales::SPANISH . '"', false);

    expect(substr_count($html, 'data-bilingual-tab='))->toBe(2)
        ->and(substr_count($html, __('admin.settings_form.editor_bold')))->toBe(2)
        ->and(substr_count($html, __('admin.settings_form.editor_italic')))->toBe(2)
        ->and(substr_count($html, __('admin.settings_form.editor_underline')))->toBe(2)
        ->and(substr_count($html, __('admin.settings_form.editor_link')))->toBeGreaterThanOrEqual(2)
        ->and(substr_count($html, 'role="toolbar"'))->toBe(2)
        ->and(substr_count($html, 'wire:ignore'))->toBe(2);
});
