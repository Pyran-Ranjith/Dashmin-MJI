<?php
ob_start();
session_start();
require_once('header.php');
if (isset($_GET['topic'])) {
    $topic = $_GET['topic'];
}
if (isset($_GET['part_id'])) {
    $part_id = $_GET['part_id'];
}
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
}

$previous_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.back()';
//Check for subscription
// $user_id1 = 12;
$sql = "
SELECT su.*, st.part_number as part_number_ , COUNT(*) AS num_rows FROM subscription su
LEFT JOIN stocks st ON stock_id = st.id
WHERE su.user_id = :user_id 
-- AND st.part_number = 'ols_sin_lan'
";
$subscription = $conn->prepare($sql);
$subscription->execute(['user_id' => $user_id]);
$row = $subscription->fetch(PDO::FETCH_ASSOC);
$num_rows = $row['num_rows'];
?>
<main class="main">
    <a href="<?php echo $previous_page; ?>" class="btn btn-primary">Go Back</a>
    <h1 class="text-center mt-5"><?php echo $topic ?></h1>
    <?php
// Check if the user is subscribed (replace this with your actual condition)
$user_subscribed = false;

// Check if the user is subscribed
if ($num_rows == 0) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> User not subscribed for this course.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
} else {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>***</strong> User subscribed for this course.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}
?>

</main>

<?php
include 'footer.php'; // Include footer
?>