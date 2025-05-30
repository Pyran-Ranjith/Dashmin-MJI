<?php
ob_start();

class supplier
{
    // private database object\
    private $db;

    //constructor to initialize private variable to the database connection
    function __construct($db)
    {
        $this->db = $db;
    }

    public function getSupplierPurchasesFilltered($start_date, $end_date)
    {
        try {
            // $query = "SELECT sp.id, su.supplier_name, st.part_number, sp.id, DATE_FORMAT(sp.purchase_date, '%d-%m-%Y') 
            $query = "SELECT sp.id as supplier_id, su.supplier_name, st.part_number, sp.id, DATE_FORMAT(sp.purchase_date, '%d-%m-%Y') 
FROM supplier_purchases sp
        LEFT JOIN stocks st ON sp.part_id = st.id
        JOIN suppliers su ON sp.supplier_id = su.id
        WHERE 1=1";
// Apply filters
if (!empty($start_date)) {
    $query .= " AND DATE(sp.purchase_date) >= '$start_date' ";
}
if (!empty($end_date)) {
    $query .= " AND DATE(sp.purchase_date) <= '$end_date' ";
}
echo $query;

            $result = $this->db->query($query);
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
            // echo _alert_link($e->getMessage(), "danger", "", "Press following link to ", "Go back.");
            return false;
        }
    }

    public function getSupplierFilltered($start_date, $end_date)
    {
        try {
            // $query = "SELECT sp.id, su.supplier_name, st.part_number, sp.id, DATE_FORMAT(sp.purchase_date, '%d-%m-%Y') 
            $query = "SELECT sp.id as supplier_id, su.supplier_name, st.part_number, sp.id, DATE_FORMAT(sp.purchase_date, '%d-%m-%Y') 
FROM supplier_purchases sp
        LEFT JOIN stocks st ON sp.part_id = st.id
        JOIN suppliers su ON sp.supplier_id = su.id
        ";
            // -- WHERE 1=1";
// Apply filters
if (!empty($start_date)) {
    $query .= " AND DATE(sp.date_supplied) >= '$start_date' ";
}
if (!empty($end_date)) {
    $query .= " AND DATE(sp.date_supplied) <= '$end_date' ";
}
echo $query;

            $result = $this->db->query($query);
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
            // echo _alert_link($e->getMessage(), "danger", "", "Press following link to ", "Go back.");
            return false;
        }
    }
}
