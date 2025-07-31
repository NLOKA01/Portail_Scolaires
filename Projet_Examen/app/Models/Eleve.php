<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static static|Builder create(array $attributes = [])
 * @method static static|Builder firstOrCreate(array $attributes, array $values = [])
 * @method static static|Builder find($id, $columns = ['*'])
 * @method static static|Builder findOrFail($id, $columns = ['*'])
 * @method static static|Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Eleve extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date_naissance',
        'lieu_naissance',
        'sexe',
        'numero_matricule',
        'classe_id',
        'parent_id'
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentUser::class, 'parent_id');
    }

    public function bulletins(): HasMany
    {
        return $this->hasMany(Bulletin::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DocumentEleve::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }
}
