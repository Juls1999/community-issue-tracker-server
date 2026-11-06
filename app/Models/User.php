<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;


#[Fillable(['first_name', 'middle_name', 'last_name', 'phone_number', 'email', 'password', 'barangay_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function submittedIssues(): HasMany
    {
        return $this->hasMany(Issue::class, 'user_id');
    }

    public function assignedIssues(): HasMany
    {
        return $this->hasMany(Issue::class, 'assigned_to');
    }


    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => collect([
                $this->first_name,
                $this->middle_name,
                $this->last_name,
            ])->filter()->implode(' ')
        );
    }

    protected function isVerified(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->email_verified_at !== null
        );
    }
}
