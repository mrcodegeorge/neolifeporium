<?php

namespace App\Models;

use App\Enums\RoleType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'status',
        'preferred_channel',
        'last_login_at',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function farmerProfile(): HasOne
    {
        return $this->hasOne(FarmerProfile::class);
    }

    public function vendorProfile(): HasOne
    {
        return $this->hasOne(VendorProfile::class);
    }

    public function agronomistProfile(): HasOne
    {
        return $this->hasOne(AgronomistProfile::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'farmer_id');
    }

    public function advisorySessions(): HasMany
    {
        return $this->hasMany(Booking::class, 'agronomist_id');
    }

    public function expertSpecializations(): HasMany
    {
        return $this->hasMany(ExpertSpecialization::class, 'expert_id');
    }

    public function expertReviews(): HasMany
    {
        return $this->hasMany(ExpertReview::class, 'expert_id');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function hasRole(string|RoleType $role): bool
    {
        $value = $role instanceof RoleType ? $role->value : $role;

        return $this->roles->contains(fn (Role $ownedRole) => $ownedRole->slug === $value);
    }

    public function hasAnyRole(array $roles): bool
    {
        return collect($roles)->contains(fn (string $role) => $this->hasRole($role));
    }
}
