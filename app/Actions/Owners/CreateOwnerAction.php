<?php

namespace App\Actions\Owners;

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Setting;
use App\Models\Property;
use Illuminate\Support\Str;
use App\Mail\OwnerWelcomeMail;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class CreateOwnerAction
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): Owner
    {
        $password = Str::password(16);

        $result = DB::transaction(function () use ($data, $password): array {
            $user = User::create([
                'name' => $data['coprop1_dni'],
                'email' => $data['coprop1_email'],
                'password' => Hash::make($password),
                'is_active' => true,
            ]);

            $owner = Owner::create([
                'user_id' => $user->id,
                'coprop1_name' => $data['coprop1_name'],
                'coprop1_dni' => $data['coprop1_dni'],
                'coprop1_phone' => $data['coprop1_phone'] ?? null,
                'coprop1_email' => $data['coprop1_email'],
                'coprop2_name' => $data['coprop2_name'] ?? null,
                'coprop2_dni' => $data['coprop2_dni'] ?? null,
                'coprop2_phone' => $data['coprop2_phone'] ?? null,
                'coprop2_email' => $data['coprop2_email'] ?? null,
            ]);

            $assignments = $data['assignments'] ?? [];

            foreach ($assignments as $assignment) {
                $propertyId = (int) $assignment['property_id'];
                $endDate = $assignment['end_date'] ?? null;

                if ($endDate === null) {
                    $hasActiveAssignment = PropertyAssignment::query()
                        ->where('property_id', $propertyId)
                        ->whereNull('end_date')
                        ->lockForUpdate()
                        ->exists();

                    if ($hasActiveAssignment) {
                        throw ValidationException::withMessages([
                            'assignments' => __('La propiedad ya tiene una propietaria activa. Cierra la asignación anterior antes de asignar una nueva.'),
                        ]);
                    }
                }

                PropertyAssignment::create([
                    'property_id' => $propertyId,
                    'owner_id' => $owner->id,
                    'start_date' => $assignment['start_date'],
                    'end_date' => $endDate,
                    'admin_validated' => false,
                    'owner_validated' => false,
                ]);
            }

            return [
                'owner' => $owner,
                'user' => $user,
            ];
        });

        /** @var Owner $owner */
        $owner = $result['owner'];
        /** @var User $user */
        $user = $result['user'];

        $user->assignRole(Role::PROPERTY_OWNER);

        $settings = Setting::stringValues([
            'from_address',
            'from_name',
            'owners_welcome_subject_eu',
            'owners_welcome_subject_es',
            'owners_welcome_text_eu',
            'owners_welcome_text_es',
        ]);

        $subject = (string) (Setting::localizedStringFrom(
            $settings,
            'owners_welcome_subject',
            __('admin.owners.email.default_subject'),
        ) ?? __('admin.owners.email.default_subject'));

        $bodyTemplate = (string) (Setting::localizedStringFrom(
            $settings,
            'owners_welcome_text',
            __('admin.owners.email.default_body'),
        ) ?? __('admin.owners.email.default_body'));

        $bodyHtml = str_replace('##info##', $this->buildAssignmentsInfoHtml($data), $bodyTemplate);

        $resetToken = Password::createToken($user);
        $resetUrl = route('password.reset', [
            'token' => $resetToken,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new OwnerWelcomeMail(
            $settings['from_address'] ?? null,
            $settings['from_name'] ?? null,
            $subject,
            $bodyHtml,
            $resetUrl,
        ));

        return $owner;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildAssignmentsInfoHtml(array $data): string
    {
        /** @var array<int, array{property_id: int|string, start_date?: string, end_date?: string|null}> $assignments */
        $assignments = $data['assignments'] ?? [];

        if ($assignments === []) {
            return '<p>' . e(__('admin.owners.email.no_properties')) . '</p>';
        }

        $propertyIds = collect($assignments)
            ->pluck('property_id')
            ->map(static fn(int|string $propertyId): int => (int) $propertyId)
            ->unique()
            ->values()
            ->all();

        $properties = Property::query()
            ->with('location:id,code,type')
            ->whereIn('id', $propertyIds)
            ->get()
            ->keyBy('id');

        $items = collect($assignments)
            ->map(function (array $assignment) use ($properties): ?string {
                $property = $properties->get((int) $assignment['property_id']);

                if ($property === null || $property->location === null) {
                    return null;
                }

                $label = $property->location->code . ' ' . $property->name;

                return '<li>' . e($label) . '</li>';
            })
            ->filter()
            ->values();

        if ($items->isEmpty()) {
            return '<p>' . e(__('admin.owners.email.no_properties')) . '</p>';
        }

        return '<ul>' . $items->implode('') . '</ul>';
    }
}
