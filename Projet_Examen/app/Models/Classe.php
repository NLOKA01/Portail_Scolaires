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
 */
class Classe extends Model
{
    use HasFactory;

    protected $fillable = [
        'niveau',
        'nom',
        'capacite',
        'annee_scolaire',
        'description'
    ];

    public function eleves(): HasMany
    {
        return $this->hasMany(Eleve::class);
    }

    public function matieres(): BelongsToMany
    {
        return $this->belongsToMany(Matiere::class, 'classe_matiere')
            ->withPivot('coefficient');
    }

    public function enseignants(): BelongsToMany
    {
        return $this->belongsToMany(Enseignant::class, 'enseignant_classe');
    }

    public function bulletins(): HasMany
    {
        return $this->hasMany(Bulletin::class);
    }
}
