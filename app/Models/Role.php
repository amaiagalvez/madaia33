<?php

namespace App\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory, SoftDeletes;

    public const SUPER_ADMIN = 'superadmin';

    public const GENERAL_ADMIN = 'admin_general';

    public const COMMUNITY_ADMIN = 'admin_comunidad';

    public const PROPERTY_OWNER = 'propietaria';

    public const DELEGATED_VOTE = 'voto_delegado';

    protected $fillable = [
        'name',
    ];

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return [
            self::SUPER_ADMIN,
            self::GENERAL_ADMIN,
            self::COMMUNITY_ADMIN,
            self::PROPERTY_OWNER,
            self::DELEGATED_VOTE,
        ];
    }
}
