<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueCategory extends Model
{
    /** @use HasFactory<\Database\Factories\IssueCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean'
        ];
    }

    public function issues()
    {
        return $this->hasMany(Issue::class);
    }
}
