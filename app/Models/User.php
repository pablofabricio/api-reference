<?php

namespace App\Models;

use App\Enums\ChannelVisibility;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'description',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function channelsOwned()
    {
        return $this->hasMany(Channel::class, 'created_by');
    }

    public function channelMemberships()
    {
        return $this->hasMany(ChannelMember::class);
    }

    public function visibleOwnedChannelsForViewer(?int $viewerId): Collection
    {
        $query = $this->channelsOwned();

        $isOwnProfile = $viewerId && $viewerId === (int) $this->id;
        
        if (! $isOwnProfile) {
            if (! $viewerId) {
                $query->where('visibility', ChannelVisibility::PUBLIC->value);
            } else {
                $query->where(function ($q) use ($viewerId) {
                    $q->where('visibility', ChannelVisibility::PUBLIC->value)
                      ->orWhereHas('members', function ($memberQuery) use ($viewerId) {
                          $memberQuery->where('user_id', $viewerId);
                      });
                });
            }
        }

        return $query->select(['id', 'name', 'description', 'created_by', 'visibility', 'created_at', 'updated_at'])
            ->withCount('members')
            ->orderByDesc('id')
            ->get();
    }

    public function profilePayload(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'email' => (string) $this->email,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'description' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
