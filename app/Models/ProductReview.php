<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model {
    protected $table = 'product_reviews';
    protected $fillable = ['store_product_id', 'user_id', 'rating', 'comment'];
    
    public function product() {
        return $this->belongsTo(StoreProduct::class, 'store_product_id');
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
