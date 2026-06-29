<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int|null $owner_id
 * @property int|null $category_id
 * @property string $sku
 * @property string $title
 * @property string|null $image
 * @property string|null $description
 * @property float|null $price
 * @property float $cost
 * @property string $type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int $Laminado
 * @property int $LaminadoComp
 * @property int $Estructura
 * @property int $EstructuraSilla
 * @property int $EstructuraMiniMamp
 * @property int $TelaBase
 * @property int $TelaComp
 * @property int $Paneles
 * @property int $Vinilpiel
 * @property int $Acusticos
 * @property int $Acrilicos
 * @property string|null $family
 * @property string|null $ensamble_setto
 * @property float|null $width
 * @property float|null $height
 * @property float|null $depth
 * @property float|null $melamina_density
 * @property string|null $Medidas
 */
class Product extends Model
{
    use HasFactory;

    protected $casts = [
        'price' => 'float',
        'width' => 'float',
        'height' => 'float',
        'depth' => 'float',
        'weight' => 'float',
        'melamina_density' => 'float',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct();

        $this->table = config('database.connections.cotizador.database') . '.products';
    }

    public function category() : HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
