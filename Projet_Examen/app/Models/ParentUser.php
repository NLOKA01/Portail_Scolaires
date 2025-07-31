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
class ParentUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profession',
        'nombre_enfants'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(Eleve::class, 'parent_id');
    }
}
