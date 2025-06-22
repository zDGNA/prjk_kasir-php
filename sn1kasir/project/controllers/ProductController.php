<?php

require_once '../models/Product.php';
require_once '../controllers/AuthController.php';

class ProductController {
    private $product;
    private $auth;

    public function __construct() {
        $this->product = new Product();
        $this->auth = new AuthController();
        $this->auth->requireLogin();
    }

    public function index() {
        $stmt = $this->product->read();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $products;
    }

    public function show($id) {
        $this->product->id = $id;
        if($this->product->readOne()) {
            return [
                'id' => $this->product->id,
                'category_id' => $this->product->category_id,
                'name' => $this->product->name,
                'description' => $this->product->description,
                'barcode' => $this->product->barcode,
                'price' => $this->product->price,
                'cost_price' => $this->product->cost_price,
                'stock' => $this->product->stock,
                'min_stock' => $this->product->min_stock,
                'unit' => $this->product->unit,
                'image' => $this->product->image,
                'status' => $this->product->status,
                'created_at' => $this->product->created_at,
                'updated_at' => $this->product->updated_at
            ];
        }
        return null;
    }

    public function store($data) {
        $this->product->category_id = $data['category_id'];
        $this->product->name = $data['name'];
        $this->product->description = $data['description'] ?? '';
        $this->product->barcode = $data['barcode'] ?? '';
        $this->product->price = $data['price'];
        $this->product->cost_price = $data['cost_price'];
        $this->product->stock = $data['stock'];
        $this->product->min_stock = $data['min_stock'] ?? 0;
        $this->product->unit = $data['unit'];
        $this->product->image = $data['image'] ?? '';
        $this->product->status = $data['status'] ?? 'active';

        if($this->product->create()) {
            return [
                'success' => true,
                'message' => 'Product created successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to create product'
        ];
    }

    public function update($id, $data) {
        $this->product->id = $id;
        $this->product->category_id = $data['category_id'];
        $this->product->name = $data['name'];
        $this->product->description = $data['description'] ?? '';
        $this->product->barcode = $data['barcode'] ?? '';
        $this->product->price = $data['price'];
        $this->product->cost_price = $data['cost_price'];
        $this->product->stock = $data['stock'];
        $this->product->min_stock = $data['min_stock'] ?? 0;
        $this->product->unit = $data['unit'];
        $this->product->image = $data['image'] ?? '';
        $this->product->status = $data['status'] ?? 'active';

        if($this->product->update()) {
            return [
                'success' => true,
                'message' => 'Product updated successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to update product'
        ];
    }

    public function destroy($id) {
        $this->product->id = $id;
        if($this->product->delete()) {
            return [
                'success' => true,
                'message' => 'Product deleted successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to delete product'
        ];
    }

    public function search($keyword) {
        $stmt = $this->product->search($keyword);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $products;
    }

    public function getLowStock() {
        $stmt = $this->product->getLowStockProducts();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $products;
    }
}

?>