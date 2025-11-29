<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreProduct extends Model {
    protected $table = 'store_products';
    protected $fillable = ['name', 'description', 'price', 'stock', 'is_active', 'user_id'];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function reviews() {
        return $this->hasMany(ProductReview::class, 'store_product_id');
    }
    
    public function getAverageRatingAttribute() {
        return $this->reviews()->avg('rating') ?? 0;
    }
}
