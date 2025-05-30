<?php
ob_start();

class stocks
{
    // private database object\
    private $db;

    //constructor to initialize private variable to the database connection
    function __construct($db)
    {
        $this->db = $db;
    }

    public function addStocks($part_number, $description, $category_id, $model_id, $quantity, $cost, $selling_price, $image)
    {
        try {
            // define sql statement to be executed
            $sql = "
        INSERT INTO spare_parts (part_number, description, category_id, model_id, quantity_in_stock, cost, selling_price, image) 
        VALUES (:part_number, :description, :category_id, :model_id, :quantity_in_stock, :cost, :selling_price, :image)
            ";

            //prepare the sql statement for execution
            $stmt = $this->db->prepare($sql);

            // bind all placeholders to the actual values
            $stmt->bindparam(':part_number', $part_number);
            $stmt->bindparam(':description', $description);
            $stmt->bindparam(':category_id', $category_id);
            $stmt->bindparam(':model_id', $model_id);
            $stmt->bindparam(':quantity_in_stock', $quantity);
            $stmt->bindparam(':cost', $cost);
            $stmt->bindparam(':selling_price', $selling_price);
            $stmt->bindparam(':image', $image);

            // for debugging, manually construct the query string with values
            $debug_sql = str_replace(
                [':part_number', ':description', ':category_id', ':model_id', ':quantity_in_stock', ':cost', ':selling_price', ':image'],
                ["'$part_number'", "'$description'", "'$category_id'", "'$model_id'", "'$quantity'", "'$cost'", "'$selling_price'", "'$image'"],
                $sql
            );

            // echo the constructed SQL query with actual values
            // echo $debug_sql;

            // execute statement
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getStocksCategories()
    {
        try {
            $query = "SELECT * FROM vehicle_categories";
            $result = $this->db->query($query);
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getStocksSuppliers()
    {
        try {
            $query = "SELECT * FROM suppliers";
            $result = $this->db->query($query);
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getStocksModels()
    {
        try {
            $query = "SELECT * FROM vehicle_models";
            $result = $this->db->query($query);
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getStocksCategoriesModels()
    {
        try {
            $sql = "
        SELECT
            * 
        FROM 
            stocks
        JOIN vehicle_categories ON spare_parts.category_id = vehicle_categories.id
        JOIN vehicle_models ON spare_parts.model_id = vehicle_models.id
                    ";
            $result = $this->db->query($sql);
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
            // echo _alert_link($e->getMessage(), "danger", "", "Press following link to ", "Go back.");
            return false;
        }
    }

// Fetch stocks, categories, and suppliers

    public function getStocksCategoriesuppliers()
    {
        try {
            $sql = "
            SELECT sp.*, vc.category_name, s.supplier_name, vm.model_name
            FROM spare_parts  sp
            JOIN vehicle_categories vc ON sp.category_id = vc.id 
            JOIN suppliers s ON sp.supplier_id = s.id                    
            JOIN vehicle_models vm ON sp.model_id = vm.id
        ";
            echo $sql;
            $result = $this->db->query($sql);
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
            // echo _alert_link($e->getMessage(), "danger", "", "Press following link to ", "Go back.");
            return false;
        }
    }



    // public function getStocksFilltered($start_date, $end_date)
    // {
    //     try {
    //             $sql = "
    //             SELECT
    //                 * 
    //                 FROM parts p
    //                 WHERE 1=1";
    //             ";
    //                 // Apply filters
    //                 if (!empty($start_date)) {
    //                     $sql .= " AND p.sale_date >= '$start_date' ";
    //                 }
    //                 if (!empty($end_date)) {
    //                     $sql .= " AND p.sale_date <= '$end_date' ";
    //                 }
    //         $result = $this->db->query($sql);
    //         return $result;
    //     } catch (PDOException $e) {
    //         echo $e->getMessage();
    //         return false;
    //     }
    // }

    public function getStocksFilltered($start_date, $end_date)
    {
        try {
            $query = "SELECT p.id, p.part_number, p.description, c.category_name, p.stock_quantity, p.cost, p.selling_price, DATE_FORMAT(p.created_at, '%d-%m-%Y')
            FROM stocks p
        JOIN categories c ON p.category_id = c.id
            WHERE 1=1";
            // Apply filters
            if (!empty($start_date)) {
                $query .= " AND DATE(p.created_at) >= '$start_date' ";
            }
            if (!empty($end_date)) {
                $query .= " AND DATE(p.created_at) <= '$end_date' ";
            }
            // echo $query;
            $result = $this->db->query($query);
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
            // echo _alert_link($e->getMessage(), "danger", "", "Press following link to ", "Go back.");
            return false;
        }
    }
}

