<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_info',
        'address',
    ];

    /**
     * Get all projects associated with this client.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
