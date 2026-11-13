<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'last_login',
        'img',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = ['avatar'];

    public function scopeType(Builder $builder, string $type = 'user')
    {
        $builder->where('type', $type);
    }

    public function getLastLoginHumanAttribute()
    {
        if ($this->last_login) {
            return $this->last_login->diffForHumans();
        }

        return null;
    }

    public function getAvatarAttribute()
    {
        $path = asset('images/avatar.png');

        if (! is_null($this->attributes['img'])) {
            $path = asset('storage/'.$this->attributes['img']);
        }

        return $path;
    }
}
