<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    /** @use HasFactory<\Database\Factories\BarangayFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'municipality',
        'province',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean'
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
}
