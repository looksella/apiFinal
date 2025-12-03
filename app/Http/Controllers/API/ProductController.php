<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreProduct as Product;

class ProductController extends Controller
{
    /**
     * Controlador API para manejar productos.
     * Todos los comentarios están en español.
     */
    public function index(Request $request)
    {
        $query = Product::query()->withCount('reviews');

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->query('is_active'));
        }

        if ($q = $request->query('q')) {
            $query->where('name', 'like', "%{$q}%");
        }

        $perPage = (int) $request->query('per_page', 15);
        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($products);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $user = $req->user();
        if ($user) {
            $data['user_id'] = $user->id;
        }

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    public function show($id)
    {
        $product = Product::with('reviews.user')->findOrFail($id);
        return response()->json($product);
    }

        public function best()
    {
        $products = Product::with('reviews.user')->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No se encontraron productos'], 404);
        }

        $product = $products->sortByDesc(fn ($p) => $p->average_rating)->first();

        return response()->json([
            'product' => $product,
            'average_rating' => $product->average_rating,
        ]);
    }

    public function update(Request $req, $id)
    {
        $product = Product::findOrFail($id);

        // Opcional: verificación de autorización (propietario)
        if ($req->user() && $product->user_id && $req->user()->id !== $product->user_id) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $data = $req->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $product->update($data);
        return response()->json($product);
    }

    public function destroy(Request $req, $id)
    {
        $product = Product::findOrFail($id);

        if ($req->user() && $product->user_id && $req->user()->id !== $product->user_id) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $product->delete();
        return response()->json(null, 204);
    }
}
