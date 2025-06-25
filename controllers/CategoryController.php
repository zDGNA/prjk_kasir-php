<?php

require_once '../models/Category.php';
require_once '../controllers/AuthController.php';

/**
 * Controller untuk mengelola kategori produk
 * Menangani CRUD (Create, Read, Update, Delete) kategori
 */
class CategoryController {
    private $category;
    private $auth;

    /**
     * Constructor - inisialisasi model dan cek autentikasi
     */
    public function __construct() {
        $this->category = new Category();
        $this->auth = new AuthController();
        $this->auth->requireLogin();  // Pastikan user sudah login
    }

    /**
     * Mendapatkan semua kategori
     * @return array Daftar semua kategori
     */
    public function index() {
        $stmt = $this->category->read();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $categories;
    }

    /**
     * Mendapatkan detail kategori berdasarkan ID
     * @param int $id ID kategori
     * @return array|null Data kategori atau null jika tidak ditemukan
     */
    public function show($id) {
        $this->category->id = $id;
        if($this->category->readOne()) {
            return [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'description' => $this->category->description,
                'status' => $this->category->status,
                'created_at' => $this->category->created_at,
                'updated_at' => $this->category->updated_at
            ];
        }
        return null;
    }

    /**
     * Membuat kategori baru
     * @param array $data Data kategori yang akan disimpan
     * @return array Hasil operasi (success/error dengan message)
     */
    public function store($data) {
        // Set data kategori dari input
        $this->category->name = $data['name'];
        $this->category->description = $data['description'] ?? '';
        $this->category->status = $data['status'] ?? 'active';

        // Simpan ke database
        if($this->category->create()) {
            return [
                'success' => true,
                'message' => 'Category created successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to create category'
        ];
    }

    /**
     * Update kategori yang sudah ada
     * @param int $id ID kategori yang akan diupdate
     * @param array $data Data baru kategori
     * @return array Hasil operasi (success/error dengan message)
     */
    public function update($id, $data) {
        // Set ID dan data kategori
        $this->category->id = $id;
        $this->category->name = $data['name'];
        $this->category->description = $data['description'] ?? '';
        $this->category->status = $data['status'] ?? 'active';

        // Update ke database
        if($this->category->update()) {
            return [
                'success' => true,
                'message' => 'Category updated successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to update category'
        ];
    }

    /**
     * Hapus kategori
     * @param int $id ID kategori yang akan dihapus
     * @return array Hasil operasi (success/error dengan message)
     */
    public function destroy($id) {
        $this->category->id = $id;
        if($this->category->delete()) {
            return [
                'success' => true,
                'message' => 'Category deleted successfully'
            ];
        }
        return [
            'success' => false,
            'message' => 'Failed to delete category'
        ];
    }

    /**
     * Mendapatkan kategori yang statusnya aktif
     * @return array Daftar kategori aktif
     */
    public function getActiveCategories() {
        $stmt = $this->category->getActiveCategories();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $categories;
    }
}

?>