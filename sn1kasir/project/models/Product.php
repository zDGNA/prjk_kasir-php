<?php

require_once '../config/Database.php';

class Product {
    private $connection;
    private $table_name = "products";

    public $id;
    public $category_id;
    public $name;
    public $description;
    public $barcode;
    public $price;
    public $cost_price;
    public $stock;
    public $min_stock;
    public $unit;
    public $image;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->connection = $database->connect();
    }

    // Create product
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET category_id=:category_id, name=:name, description=:description, 
                      barcode=:barcode, price=:price, cost_price=:cost_price, 
                      stock=:stock, min_stock=:min_stock, unit=:unit, 
                      image=:image, status=:status";

        $stmt = $this->connection->prepare($query);

        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->barcode = htmlspecialchars(strip_tags($this->barcode));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->cost_price = htmlspecialchars(strip_tags($this->cost_price));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->min_stock = htmlspecialchars(strip_tags($this->min_stock));
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":barcode", $this->barcode);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":cost_price", $this->cost_price);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":min_stock", $this->min_stock);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all products with category
    public function read() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  ORDER BY p.name ASC";
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single product
    public function readOne() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id LIMIT 0,1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->category_id = $row['category_id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->barcode = $row['barcode'];
            $this->price = $row['price'];
            $this->cost_price = $row['cost_price'];
            $this->stock = $row['stock'];
            $this->min_stock = $row['min_stock'];
            $this->unit = $row['unit'];
            $this->image = $row['image'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Update product
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET category_id=:category_id, name=:name, description=:description, 
                      barcode=:barcode, price=:price, cost_price=:cost_price, 
                      stock=:stock, min_stock=:min_stock, unit=:unit, 
                      image=:image, status=:status 
                  WHERE id=:id";

        $stmt = $this->connection->prepare($query);

        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->barcode = htmlspecialchars(strip_tags($this->barcode));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->cost_price = htmlspecialchars(strip_tags($this->cost_price));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->min_stock = htmlspecialchars(strip_tags($this->min_stock));
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':barcode', $this->barcode);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':cost_price', $this->cost_price);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':min_stock', $this->min_stock);
        $stmt->bindParam(':unit', $this->unit);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete product
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Search products by name or barcode
    public function search($keyword) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE (p.name LIKE :keyword OR p.barcode LIKE :keyword) 
                  AND p.status = 'active'
                  ORDER BY p.name ASC";
        
        $stmt = $this->connection->prepare($query);
        $keyword = htmlspecialchars(strip_tags($keyword));
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt;
    }

    // Update stock
    public function updateStock($product_id, $quantity) {
        $query = "UPDATE " . $this->table_name . " 
                SET stock = stock - :quantity 
                WHERE id = :id";

        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $product_id);

        return $stmt->execute();
    }


    // Get low stock products
    public function getLowStockProducts() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.stock <= p.min_stock AND p.status = 'active'
                  ORDER BY p.stock ASC";
        
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}

?>