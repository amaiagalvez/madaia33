<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Voting;

class VotingPdfBuilder
{
  /**
   * @return array<string, mixed>
   */
  public function build(string $type): array
  {
    $settings = Setting::stringValues([
      'front_site_name',
      'votings_pdf_delegated_text_eu',
      'votings_pdf_delegated_text_es',
      'votings_pdf_in_person_text_eu',
      'votings_pdf_in_person_text_es',
    ]);

    $siteName = trim((string) ($settings['front_site_name'] ?? ''));

    if ($siteName === '') {
      $siteName = (string) config('app.name', 'Madaia 33');
    }

    $textPrefix = $type === 'in_person' ? 'votings_pdf_in_person_text' : 'votings_pdf_delegated_text';

    $votings = Voting::query()
      ->publishedOpen()
      ->with('options')
      ->orderBy('starts_at')
      ->get()
      ->map(static function (Voting $voting): array {
        return [
          'name_eu' => $voting->name_eu,
          'name_es' => (string) ($voting->name_es ?? ''),
          'question_eu' => $voting->question_eu,
          'question_es' => (string) ($voting->question_es ?? ''),
          'options' => $voting->options
            ->map(static function ($option): array {
              return [
                'label_eu' => $option->label_eu,
                'label_es' => (string) ($option->label_es ?? ''),
              ];
            })
            ->values()
            ->all(),
        ];
      })
      ->values()
      ->all();

    return [
      'documentType' => $type,
      'siteName' => $siteName,
      'leftHeader' => $siteName . ' Jabeen Erkidegoa',
      'rightHeader' => 'Comunidad de Propietarios/a ' . $siteName,
      'introEuHtml' => (string) ($settings[$textPrefix . '_eu'] ?? ''),
      'introEsHtml' => (string) ($settings[$textPrefix . '_es'] ?? ''),
      'faviconDataUri' => $this->faviconDataUri(),
      'votings' => $votings,
    ];
  }

  private function faviconDataUri(): string
  {
    $svgPath = public_path('favicon.svg');

    if (is_file($svgPath)) {
      $content = file_get_contents($svgPath);

      if (is_string($content) && $content !== '') {
        return 'data:image/svg+xml;base64,' . base64_encode($content);
      }
    }

    $icoPath = public_path('favicon.ico');

    if (is_file($icoPath)) {
      $content = file_get_contents($icoPath);

      if (is_string($content) && $content !== '') {
        return 'data:image/x-icon;base64,' . base64_encode($content);
      }
    }

    return '';
  }
}
