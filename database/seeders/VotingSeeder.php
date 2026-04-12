<?php

namespace Database\Seeders;

use App\Models\Voting;
use App\Models\Location;
use App\Models\VotingBallot;
use App\Models\VotingOption;
use App\Models\VotingLocation;
use App\Models\VotingSelection;
use Illuminate\Database\Seeder;
use App\Models\VotingOptionTotal;

class VotingSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedVoting(
            attributes: [
                'name_eu' => 'Auzo bileraren eguna',
                'name_es' => 'Dia de la reunion vecinal',
                'question_eu' => 'Noiz egitea nahi duzu hurrengo auzo bilera?',
                'question_es' => 'Cuando prefieres celebrar la proxima reunion vecinal?',
                'starts_at' => today()->subDays(2),
                'ends_at' => today()->addDays(7),
                'is_published' => true,
                'is_anonymous' => false,
            ],
            options: [
                ['label_eu' => 'Osteguna 19:00etan', 'label_es' => 'Jueves a las 19:00'],
                ['label_eu' => 'Ostirala 19:30ean', 'label_es' => 'Viernes a las 19:30'],
                ['label_eu' => 'Larunbata 11:00etan', 'label_es' => 'Sabado a las 11:00'],
            ],
            locationCodes: ['33-A', '33-B', '33-C'],
        );

        $this->seedVoting(
            attributes: [
                'name_eu' => 'Sarrera margotzea',
                'name_es' => 'Pintura del portal',
                'question_eu' => 'Aurten sarrera nagusia margotu nahi duzu?',
                'question_es' => 'Quieres pintar el portal principal este ano?',
                'starts_at' => today()->subDay(),
                'ends_at' => today()->addDays(5),
                'is_published' => true,
                'is_anonymous' => true,
            ],
            options: [
                ['label_eu' => 'Bai', 'label_es' => 'Si'],
                ['label_eu' => 'Ez', 'label_es' => 'No'],
            ],
            locationCodes: [],
        );

        $this->seedVoting(
            attributes: [
                'name_eu' => 'Lorategiko hobekuntzak',
                'name_es' => 'Mejoras del jardin',
                'question_eu' => 'Zein hobekuntza lehenetsi behar dugu datorren hiruhilekoan?',
                'question_es' => 'Que mejora debemos priorizar el proximo trimestre?',
                'starts_at' => today()->addDays(3),
                'ends_at' => today()->addDays(15),
                'is_published' => true,
                'is_anonymous' => false,
            ],
            options: [
                ['label_eu' => 'Argiteria berria', 'label_es' => 'Nueva iluminacion'],
                ['label_eu' => 'Bankuak berritzea', 'label_es' => 'Renovar bancos'],
                ['label_eu' => 'Haurren eremua handitzea', 'label_es' => 'Ampliar zona infantil'],
                ['label_eu' => 'Ez inbertitu oraindik', 'label_es' => 'No invertir de momento'],
            ],
            locationCodes: ['33-D', '33-E'],
        );

        $this->seedVoting(
            attributes: [
                'name_eu' => 'Ate automatiko berria (zirriborroa)',
                'name_es' => 'Nueva puerta automatica (borrador)',
                'question_eu' => 'Proposamen hau argitaratu aurretik balorazio teknikoa osatu behar da.',
                'question_es' => 'Esta propuesta requiere valoracion tecnica antes de publicarse.',
                'starts_at' => today()->subDays(4),
                'ends_at' => today()->addDays(2),
                'is_published' => false,
                'is_anonymous' => false,
            ],
            options: [
                ['label_eu' => 'Aukera A', 'label_es' => 'Opcion A'],
                ['label_eu' => 'Aukera B', 'label_es' => 'Opcion B'],
                ['label_eu' => 'Aukera C', 'label_es' => 'Opcion C'],
            ],
            locationCodes: [],
        );

        $this->seedVoting(
            attributes: [
                'name_eu' => 'Igogailua berriztea - 2026',
                'name_es' => 'Renovacion del ascensor - 2026',
                'question_eu' => 'Zein enpresa kontratatzen dugu igogailua berrizteko?',
                'question_es' => 'Que empresa contratamos para renovar el ascensor?',
                'starts_at' => today()->subMonths(2),
                'ends_at' => today()->subMonth()->subDays(5),
                'is_published' => true,
                'is_anonymous' => false,
            ],
            options: [
                ['label_eu' => 'Otis', 'label_es' => 'Otis'],
                ['label_eu' => 'Kone', 'label_es' => 'Kone'],
                ['label_eu' => 'Schindler', 'label_es' => 'Schindler'],
            ],
            locationCodes: [],
        );
    }

    /**
     * @param  array{name_eu:string,name_es:string,question_eu:string,question_es:string,starts_at:mixed,ends_at:mixed,is_published:bool,is_anonymous:bool}  $attributes
     * @param  array<int, array{label_eu:string,label_es:string}>  $options
     * @param  array<int, string>  $locationCodes
     */
    private function seedVoting(array $attributes, array $options, array $locationCodes): void
    {
        $voting = Voting::query()->updateOrCreate(
            ['name_eu' => $attributes['name_eu']],
            $attributes,
        );

        VotingSelection::query()->where('voting_id', $voting->id)->forceDelete();
        VotingBallot::query()->where('voting_id', $voting->id)->forceDelete();
        VotingOptionTotal::query()->where('voting_id', $voting->id)->forceDelete();
        VotingLocation::query()->where('voting_id', $voting->id)->forceDelete();
        VotingOption::query()->where('voting_id', $voting->id)->forceDelete();

        foreach (array_values($options) as $index => $option) {
            $voting->options()->create([
                'label_eu' => $option['label_eu'],
                'label_es' => $option['label_es'],
                'position' => $index + 1,
            ]);
        }

        $locationIds = Location::query()
            ->whereIn('code', $locationCodes)
            ->pluck('id');

        foreach ($locationIds as $locationId) {
            $voting->locations()->create([
                'location_id' => $locationId,
            ]);
        }
    }
}
