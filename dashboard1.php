<?php
ob_start();
session_start();
include 'header.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
?>
<main class="main">


        <!-- Hero Section -->
        <section id="hero" class="hero section dark-background">

            <!-- <img src="./images_other-brands.jpg" alt="" data-aos="fade-in"> -->

            <!-- <div class="container">
    <h2 data-aos="fade-up" data-aos-delay="100">අද ඉගෙනීම,<br>හෙට නායකත්වය</h2>
    <p data-aos="fade-up" data-aos-delay="200">මම OL/AL සිසුන් සඳහා දක්ෂ ගුරුවරයෙක්</p>
    <div class="d-flex mt-4" data-aos="fade-up" data-aos-delay="300">
      <a href="courses.html" class="btn-get-started">Get Started</a>
    </div>
  </div> -->

        </section><!-- /Hero Section -->


        <?php
 
// Ensure only authorized users (admin/staff) can access this page
// if (!isset($_SESSION['user_id'])) {
//     header ("Location: ./login.php");
//     exit;
// } else {
// Fetch data for dashboard
$total_parts = $conn->query("SELECT COUNT(*) FROM stocks")->fetchColumn();
$total_sales = $conn->query("SELECT SUM(total_price) FROM sales")->fetchColumn();
?>

<div class="container">
    <h2>Dashboard</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Total Parts in Stock: <?php echo $total_parts; ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5>Total Sales: Rs.<?php echo number_format($total_sales, 2); ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ?");
// $stmt->execute([$user_id1]);
// $orders = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT * FROM stocks");
$stocks_result = $stmt->fetchAll();
?>



</main>


<?php
include 'footer1.php'; // Include footer
?>