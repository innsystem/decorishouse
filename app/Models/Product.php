<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'images', 'price', 'price_promotion', 'status'];

    protected $casts = [
        'images' => 'array', // Converte JSON em array
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function affiliateLinks()
    {
        return $this->hasMany(ProductAffiliateLink::class, 'product_id');
    }

    public function affiliateLink()
    {
        return $this->hasOne(ProductAffiliateLink::class);
    }

    public function getAffiliateLinkByIntegration($integrationName)
    {
        // Busca a integração com o nome informado
        $integration = Integration::where('name', $integrationName)->first();

        if (!$integration) {
            return null; // Retorna null se a integração não existir
        }

        // Busca o link de afiliado para este produto na integração correspondente
        return $this->affiliateLinks()
            ->where('integration_id', $integration->id)
            ->value('affiliate_link'); // Pega apenas o campo do link
    }
}
