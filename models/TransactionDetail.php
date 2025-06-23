<?php

require_once '../config/Database.php';

class TransactionDetail {
    private $connection;
    private $table_name = "transaction_details";

    public $id;
    public $transaction_id;
    public $product_id;
    public $quantity;
    public $unit_price;
    public $total_price;
    public $created_at;

    public function __construct($db = null) {
        if ($db) {
            $this->connection = $db;
        } else {
            $database = new Database();
            $this->connection = $database->connect();
        }
    }

    // Create transaction detail
    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      SET transaction_id=:transaction_id, product_id=:product_id, 
                          quantity=:quantity, unit_price=:unit_price, total_price=:total_price";

            $stmt = $this->connection->prepare($query);

            $this->transaction_id = intval($this->transaction_id);
            $this->product_id = intval($this->product_id);
            $this->quantity = intval($this->quantity);
            $this->unit_price = floatval($this->unit_price);
            $this->total_price = floatval($this->total_price);

            $stmt->bindParam(":transaction_id", $this->transaction_id);
            $stmt->bindParam(":product_id", $this->product_id);
            $stmt->bindParam(":quantity", $this->quantity);
            $stmt->bindParam(":unit_price", $this->unit_price);
            $stmt->bindParam(":total_price", $this->total_price);

            if($stmt->execute()) {
                return true;
            }
            
            // Log error for debugging
            error_log("TransactionDetail create error: " . print_r($stmt->errorInfo(), true));
            return false;
            
        } catch (Exception $e) {
            error_log("TransactionDetail create exception: " . $e->getMessage());
            return false;
        }
    }

    // Read transaction details by transaction ID
    public function readByTransaction($transaction_id) {
        $query = "SELECT td.*, p.name as product_name, p.unit 
                  FROM " . $this->table_name . " td
                  LEFT JOIN products p ON td.product_id = p.id
                  WHERE td.transaction_id = :transaction_id
                  ORDER BY td.id ASC";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':transaction_id', $transaction_id);
        $stmt->execute();
        return $stmt;
    }

    // Delete transaction detail
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

    // Delete all details by transaction ID
    public function deleteByTransaction($transaction_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE transaction_id = :transaction_id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':transaction_id', $transaction_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get best selling products
    public function getBestSellingProducts($limit = 10) {
        $query = "SELECT p.name, p.price, SUM(td.quantity) as total_sold, 
                         SUM(td.total_price) as total_revenue
                  FROM " . $this->table_name . " td
                  LEFT JOIN products p ON td.product_id = p.id
                  LEFT JOIN transactions t ON td.transaction_id = t.id
                  WHERE t.status = 'completed'
                  GROUP BY td.product_id, p.name, p.price
                  ORDER BY total_sold DESC
                  LIMIT :limit";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function getLastError() {
        return $this->connection->errorInfo()[2];
    }
}

?>