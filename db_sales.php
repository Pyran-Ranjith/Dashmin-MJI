<?php
ob_start();

class sales
{
    // private database object\
    private $db;

    //constructor to initialize private variable to the database connection
    function __construct($db)
    {
        $this->db = $db;
    }

    public function getSalesSpare_partsFilltered($start_date, $end_date)
    {
        try {
            // SELECT sa.id as sale_id, st.description, sa.quantity_sold, sa.total_price, DATE_FORMAT(sa.sale_date, '%d-%m-%Y')
            // SELECT sa.*, st.description, DATE_FORMAT(sa.sale_date, '%d-%m-%Y')
            $query = "
            SELECT sa.id as sale_id, st.description, sa.quantity_sold, sa.total_price, DATE_FORMAT(sa.sale_date, '%d-%m-%Y')
            FROM sales sa
            LEFT JOIN stocks st ON sa.stock_id = st.id
            WHERE 1=1";
            // Apply filters
            if (!empty($start_date)) {
                $query .= " AND DATE(sa.sale_date) >= '$start_date' ";
            }
            if (!empty($end_date)) {
                $query .= " AND DATE(sa.sale_date) <= '$end_date' ";
            }
            $result = $this->db->query($query);
            return $result;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}
