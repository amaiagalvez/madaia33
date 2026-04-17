<?php

namespace App\Actions\Owners;

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Setting;
use App\Models\Property;
use App\SupportedLocales;
use Illuminate\Support\Str;
use App\Mail\OwnerWelcomeMail;
use App\Models\PropertyAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Support\Messaging\CampaignTrackingUrlBuilder;
use App\Actions\Campaigns\RecordDirectMessageRecipientAction;

class CreateOwnerAction
{
    public function __construct(
        private readonly ?RecordDirectMessageRecipientAction $recordDirectMessageRecipientAction = null,
        private readonly ?CampaignTrackingUrlBuilder $trackingUrlBuilder = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): Owner
    {
        $password = Str::password(16);

        $result = DB::transaction(fn(): array => $this->createOwnerWithAssignments($data, $password));

        /** @var Owner $owner */
        $owner = $result['owner'];
        /** @var User $user */
        $user = $result['user'];

        $user->assignRole(Role::PROPERTY_OWNER);

        $this->sendWelcomeMailToOwner($owner, $data);

        return $owner;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{owner: Owner, user: User}
     */
    private function createOwnerWithAssignments(array $data, string $password): array
    {
        $user = $this->createUser($data, $password);
        $owner = $this->createOwner($user, $data);

        $this->createAssignments($owner, $data['assignments'] ?? []);

        return [
            'owner' => $owner,
            'user' => $user,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createUser(array $data, string $password): User
    {
        return User::create([
            'name' => $data['coprop1_name'],
            'email' => $data['coprop1_email'],
            'password' => Hash::make($password),
            'is_active' => true,
            'language' => $data['language'] ?? SupportedLocales::default(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createOwner(User $user, array $data): Owner
    {
        $ownerId = $data['owner_id'] ?? null;

        if ($ownerId !== null && Owner::withTrashed()->whereKey((int) $ownerId)->exists()) {
            throw ValidationException::withMessages([
                'ownerId' => __('validation.unique', ['attribute' => __('admin.owners.form.id')]),
            ]);
        }

        $owner = new Owner([
            'user_id' => $user->id,
            'coprop1_name' => $data['coprop1_name'],
            'coprop1_surname' => $data['coprop1_surname'] ?? null,
            'coprop1_dni' => $data['coprop1_dni'],
            'coprop1_phone' => $data['coprop1_phone'] ?? null,
            'coprop1_has_whatsapp' => (bool) ($data['coprop1_has_whatsapp'] ?? false),
            'coprop1_email' => $data['coprop1_email'],
            'language' => $data['language'] ?? SupportedLocales::default(),
            'welcome' => false,
            'coprop2_name' => $data['coprop2_name'] ?? null,
            'coprop2_surname' => $data['coprop2_surname'] ?? null,
            'coprop2_dni' => $data['coprop2_dni'] ?? null,
            'coprop2_phone' => $data['coprop2_phone'] ?? null,
            'coprop2_has_whatsapp' => (bool) ($data['coprop2_has_whatsapp'] ?? false),
            'coprop2_email' => $data['coprop2_email'] ?? null,
        ]);

        if ($ownerId !== null) {
            $owner->id = (int) $ownerId;
        }

        $owner->save();

        return $owner;
    }

    /**
     * @param  array<int, array{property_id: int|string, start_date: string, end_date?: string|null}>  $assignments
     */
    private function createAssignments(Owner $owner, array $assignments): void
    {
        foreach ($assignments as $assignment) {
            $propertyId = (int) $assignment['property_id'];
            $endDate = $assignment['end_date'] ?? null;

            $this->ensurePropertyCanBeAssigned($propertyId, $endDate);

            PropertyAssignment::create([
                'property_id' => $propertyId,
                'owner_id' => $owner->id,
                'start_date' => $assignment['start_date'],
                'end_date' => $endDate,
                'admin_validated' => false,
                'owner_validated' => false,
            ]);
        }
    }

    private function ensurePropertyCanBeAssigned(int $propertyId, ?string $endDate): void
    {
        if ($endDate !== null) {
            return;
        }

        $hasActiveAssignment = PropertyAssignment::query()
            ->where('property_id', $propertyId)
            ->whereNull('end_date')
            ->lockForUpdate()
            ->exists();

        if (! $hasActiveAssignment) {
            return;
        }

        throw ValidationException::withMessages([
            'assignments' => __('La propiedad ya tiene una propietaria activa. Cierra la asignación anterior antes de asignar una nueva.'),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    public function sendWelcomeMailToOwner(Owner $owner, ?array $data = null): bool
    {
        $owner->loadMissing(['user', 'assignments.property.location']);

        $user = $owner->user;

        if (! $user instanceof User) {
            return false;
        }

        if (! is_string($user->email) || trim($user->email) === '') {
            return false;
        }

        $this->sendWelcomeMail($owner, $user, $data);

        $owner->forceFill(['welcome' => true])->saveQuietly();

        return true;
    }

    /**
     * @param  array<string, mixed>|null  $data
     *
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    private function sendWelcomeMail(Owner $owner, User $user, ?array $data = null): void
    {
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

        $bodyHtml = str_replace(
            ['##izena##', '##info##'],
            [(string) (($data['coprop1_name'] ?? null) ?: $owner->coprop1_name ?: $user->name), $this->buildAssignmentsInfoHtml($owner, $data)],
            $bodyTemplate,
        );

        $resetToken = Password::createToken($user);
        $resetUrl = route('password.reset', [
            'token' => $resetToken,
            'email' => $user->email,
        ]);

        $recipient = $this->recordDirectMessageRecipientAction()->execute(
            owner: $owner,
            contact: $user->email,
            subject: $subject,
            body: $bodyHtml,
            sentByUserId: Auth::id(),
        );

        $trackedBodyHtml = $this->trackingUrlBuilder()->withTrackedLinks($bodyHtml, $recipient->tracking_token);
        $trackedResetUrl = $this->trackingUrlBuilder()->trackedClickUrl($recipient->tracking_token, $resetUrl);
        $trackingPixelUrl = $this->trackingUrlBuilder()->openPixelUrl($recipient->tracking_token);

        Mail::to($user->email)->send(new OwnerWelcomeMail(
            $settings['from_address'] ?? null,
            $settings['from_name'] ?? null,
            $subject,
            $trackedBodyHtml,
            $resetUrl,
            $trackingPixelUrl,
            $trackedResetUrl,
        ));
    }

    private function recordDirectMessageRecipientAction(): RecordDirectMessageRecipientAction
    {
        return $this->recordDirectMessageRecipientAction ?? app(RecordDirectMessageRecipientAction::class);
    }

    private function trackingUrlBuilder(): CampaignTrackingUrlBuilder
    {
        return $this->trackingUrlBuilder ?? app(CampaignTrackingUrlBuilder::class);
    }

    /**
     * @param  array<string, mixed>|null  $data
     */
    private function buildAssignmentsInfoHtml(Owner $owner, ?array $data = null): string
    {
        /** @var array<int, array{property_id: int|string, start_date?: string, end_date?: string|null}> $assignments */
        $assignments = $data['assignments'] ?? [];

        $items = $assignments !== []
            ? $this->buildAssignmentItemsFromPayload($assignments)
            : $this->buildAssignmentItemsFromOwner($owner);

        if ($items === []) {
            return '<p>' . e(__('admin.owners.email.no_properties')) . '</p>';
        }

        return '<ul>' . implode('', $items) . '</ul>';
    }

    /**
     * @param  array<int, array{property_id: int|string, start_date?: string, end_date?: string|null}>  $assignments
     * @return array<int, string>
     */
    private function buildAssignmentItemsFromPayload(array $assignments): array
    {
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

        return collect($assignments)
            ->map(fn(array $assignment): ?string => $this->assignmentItemFromProperty($properties->get((int) $assignment['property_id'])))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function buildAssignmentItemsFromOwner(Owner $owner): array
    {
        return $owner->assignments
            ->map(fn(PropertyAssignment $assignment): ?string => $this->assignmentItemFromProperty($assignment->property))
            ->filter()
            ->values()
            ->all();
    }

    private function assignmentItemFromProperty(?Property $property): ?string
    {
        if ($property === null || $property->location === null) {
            return null;
        }

        $label = $property->location->code . ' ' . $property->name;

        return '<li>' . e($label) . '</li>';
    }
}
