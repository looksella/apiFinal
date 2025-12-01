<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Models\StoreProduct as Product;

class ProductReviewController extends Controller
{
    /**
     * Controlador API para manejar reseñas de productos.
     * Comentarios en español.
     */
    public function store(Request $req, $productId)
    {
        $product = Product::findOrFail($productId);

        $user = $req->user();
        if (! $user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $data = $req->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:2000',
        ]);

        // Evitar reseñas duplicadas por el mismo usuario
        $exists = ProductReview::where('store_product_id', $product->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'El usuario ya ha comentado este producto'], 409);
        }

        $data['store_product_id'] = $product->id;
        $data['user_id'] = $user->id;

        $review = ProductReview::create($data);

        return response()->json($review, 201);
    }

    public function update(Request $req, $reviewId)
    {
        $review = ProductReview::findOrFail($reviewId);
        $user = $req->user();

        if (! $user || $user->id !== $review->user_id) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $data = $req->validate([
            'rating' => 'sometimes|required|integer|between:1,5',
            'comment' => 'sometimes|nullable|string|max:2000',
        ]);

        $review->update($data);
        return response()->json($review);
    }

    public function destroy(Request $req, $reviewId)
    {
        $review = ProductReview::findOrFail($reviewId);
        $user = $req->user();

        if (! $user || $user->id !== $review->user_id) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $review->delete();
        return response()->json(null, 204);
    }
}
