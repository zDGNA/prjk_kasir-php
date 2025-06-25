<?php

require_once '../models/Category.php';
require_once '../controllers/AuthController.php';

class CategoryController {
    private $category;
    private $auth;

    public function __construct() {
        $this->category = new Category();
        $this->auth = new AuthController();
        $this->auth->requireLogin();
    }

    public function index() {
        $stmt = $this->category->read();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $categories;
    }

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

    public function store($data) {
        $this->category->name = $data['name'];
        $this->category->description = $data['description'] ?? '';
        $this->category->status = $data['status'] ?? 'active';

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

    public function update($id, $data) {
        $this->category->id = $id;
        $this->category->name = $data['name'];
        $this->category->description = $data['description'] ?? '';
        $this->category->status = $data['status'] ?? 'active';

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

    public function getActiveCategories() {
        $stmt = $this->category->getActiveCategories();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $categories;
    }
}

?>