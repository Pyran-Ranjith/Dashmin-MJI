<?php
ob_start();
session_start();
require_once('header.php');
if (isset($_GET['topic'])) {
    $topic = $_GET['topic'];
}

$previous_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.back()';
?>
<main class="main">
    <!-- Back button -->
    <a href="<?php echo $previous_page; ?>" class="btn btn-primary">Go Back</a>
    <!-- Topic -->
    <h1 class="text-center mt-5"><?php echo $topic ?></h1>

    <!-- Ouuline Section -->
    <section id="hero" class="hero section dark-background">

        <img src="prev/assets/img/outline.jpg" alt="" data-aos="fade-in">

        <!-- <div class="container">
    <h2 data-aos="fade-up" data-aos-delay="100">අද ඉගෙනීම,<br>හෙට නායකත්වය</h2>
    <p data-aos="fade-up" data-aos-delay="200">මම OL/AL සිසුන් සඳහා දක්ෂ ගුරුවරයෙක්</p>
    <div class="d-flex mt-4" data-aos="fade-up" data-aos-delay="300">
      <a href="courses.html" class="btn-get-started">Get Started</a>
    </div>
  </div> -->

    </section><!-- /Ouuline Section -->

</main>



<?php
include 'footer.php'; // Include footer
?>