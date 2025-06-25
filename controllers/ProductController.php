<?php

require_once '../models/Product.php';
require_once '../controllers/AuthController.php';

/**
 * Controller untuk mengelola produk
 * Menangani CRUD produk, pencarian, dan manajemen stok
 */
class ProductController {
    private $product;
    private $auth;

    /**
     * Constructor - inisialisasi model dan cek autentikasi
     */
    public function __construct() {
        $this->product = new Product();
        $this->auth = new AuthController();
        $this->auth->requireLogin();  // Pastikan user sudah login
    }

    /**
     * Mendapatkan semua produk dengan informasi kategori
     * @return array Daftar semua produk
     */
    public function index() {
        $stmt = $this->product->read();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $products;
    }

    /**
     * Mendapatkan detail produk berdasarkan ID
     * @param int $id ID produk
     * @return array|null Data produk atau null jika tidak ditemukan
     */
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

    /**
     * Membuat produk baru
     * @param array $data Data produk yang akan disimpan
     * @return array Hasil operasi (success/error dengan message)
     */
    public function store($data) {
        // Set semua data produk dari input
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

        // Simpan ke database
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

    /**
     * Update produk yang sudah ada
     * @param int $id ID produk yang akan diupdate
     * @param array $data Data baru produk
     * @return array Hasil operasi (success/error dengan message)
     */
    public function update($id, $data) {
        // Set ID dan semua data produk
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

        // Update ke database
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

    /**
     * Hapus produk
     * @param int $id ID produk yang akan dihapus
     * @return array Hasil operasi (success/error dengan message)
     */
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

    /**
     * Mencari produk berdasarkan nama atau barcode
     * @param string $keyword Kata kunci pencarian
     * @return array Hasil pencarian produk
     */
    public function search($keyword) {
        $stmt = $this->product->search($keyword);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $products;
    }

    /**
     * Mendapatkan produk dengan stok rendah (di bawah minimum)
     * @return array Daftar produk dengan stok rendah
     */
    public function getLowStock() {
        $stmt = $this->product->getLowStockProducts();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $products;
    }
}

?>