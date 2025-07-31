<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static static|Builder create(array $attributes = [])
 * @method static static|Builder firstOrCreate(array $attributes, array $values = [])
 * @method static static|Builder find($id, $columns = ['*'])
 * @method static static|Builder findOrFail($id, $columns = ['*'])
 * @method static static|Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Enseignant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialite',
        'date_embauche',
        'numero_identifiant'
    ];

    protected $casts = [
        'date_embauche' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matieres(): BelongsToMany
    {
        return $this->belongsToMany(Matiere::class, 'enseignant_matiere');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classe::class, 'enseignant_classe');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
