<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static static|Builder create(array $attributes = [])
 * @method static static|Builder firstOrCreate(array $attributes, array $values = [])
 * @method static static|Builder find($id, $columns = ['*'])
 * @method static static|Builder findOrFail($id, $columns = ['*'])
 * @method static static|Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static static|Builder whereIn($column, $values, $boolean = 'and', $not = false)
 */
class Matiere extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'niveau'
    ];

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classe::class, 'classe_matiere')
            ->withPivot('coefficient');
    }

    public function enseignants(): BelongsToMany
    {
        return $this->belongsToMany(Enseignant::class, 'enseignant_matiere');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
