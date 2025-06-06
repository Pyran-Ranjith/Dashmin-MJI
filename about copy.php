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

  <!-- Begin Section -->
  <section id="hero" class="hero section dark-background">

    <!-- YOUR CONTENT -->
    <!-- <img src="prev/assets/img/outline.jpg" alt="" data-aos="fade-in"> -->
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>
    <!-- Spinner End -->

    <!-- Header Start -->
    <div class="container-fluid bg-primary py-5 mb-5 page-header">
      <div class="container py-5">
        <div class="row justify-content-center">
          <div class="col-lg-10 text-center">
            <h1 class="display-3 text-white animated slideInDown">About Us</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a class="text-white" href="#">Home</a></li>
                <li class="breadcrumb-item"><a class="text-white" href="#">Pages</a></li>
                <li class="breadcrumb-item text-white active" aria-current="page">About</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
    <!-- Header End -->

    <!-- Service Start -->
    <NOSCRIPT>
      <div class="container-xxl py-5">
        <div class="container">
          <div class="row g-4">
            <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
              <div class="service-item text-center pt-3">
                <div class="p-4">
                  <i class="fa fa-3x fa-graduation-cap text-primary mb-4"></i>
                  <h5 class="mb-3">Skilled Instructors</h5>
                  <p>Diam elitr kasd sed at elitr sed ipsum justo dolor sed clita amet diam</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.3s">
              <div class="service-item text-center pt-3">
                <div class="p-4">
                  <i class="fa fa-3x fa-globe text-primary mb-4"></i>
                  <h5 class="mb-3">Online Classes</h5>
                  <p>Diam elitr kasd sed at elitr sed ipsum justo dolor sed clita amet diam</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.5s">
              <div class="service-item text-center pt-3">
                <div class="p-4">
                  <i class="fa fa-3x fa-home text-primary mb-4"></i>
                  <h5 class="mb-3">Home Projects</h5>
                  <p>Diam elitr kasd sed at elitr sed ipsum justo dolor sed clita amet diam</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.7s">
              <div class="service-item text-center pt-3">
                <div class="p-4">
                  <i class="fa fa-3x fa-book-open text-primary mb-4"></i>
                  <h5 class="mb-3">Book Library</h5>
                  <p>Diam elitr kasd sed at elitr sed ipsum justo dolor sed clita amet diam</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </NOSCRIPT>
    <!-- Service End -->

    <!-- About Start -->
    <NOSCRIPT>
      <div class="container-xxl py-5">
        <div class="container">
          <div class="row g-5">
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
              <div class="position-relative h-100">
                <img class="img-fluid position-absolute w-100 h-100" src="img/about.jpg" alt="" style="object-fit: cover;">
              </div>
            </div>
            <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
              <h6 class="section-title bg-white text-start text-primary pe-3">About Us</h6>
              <h1 class="mb-4">Welcome to eLEARNING</h1>
              <p class="mb-4">Tempor erat elitr rebum at clita. Diam dolor diam ipsum sit. Aliqu diam amet diam et eos. Clita erat ipsum et lorem et sit.</p>
              <p class="mb-4">Tempor erat elitr rebum at clita. Diam dolor diam ipsum sit. Aliqu diam amet diam et eos. Clita erat ipsum et lorem et sit, sed stet lorem sit clita duo justo magna dolore erat amet</p>
              <div class="row gy-2 gx-4 mb-4">
                <div class="col-sm-6">
                  <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Skilled Instructors</p>
                </div>
                <div class="col-sm-6">
                  <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Online Classes</p>
                </div>
                <div class="col-sm-6">
                  <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>International Certificate</p>
                </div>
                <div class="col-sm-6">
                  <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Skilled Instructors</p>
                </div>
                <div class="col-sm-6">
                  <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Online Classes</p>
                </div>
                <div class="col-sm-6">
                  <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>International Certificate</p>
                </div>
              </div>
              <a class="btn btn-primary py-3 px-5 mt-2" href="">Read More</a>
            </div>
          </div>
        </div>
      </div>
    </NOSCRIPT>
    <!-- About End -->

    <!-- Team Start -->
    <NOSCRIPT>
      <div class="container-xxl py-5">
        <div class="container">
          <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
            <h6 class="section-title bg-white text-center text-primary px-3">Instructors</h6>
            <h1 class="mb-5">Expert Instructors</h1>
          </div>
          <div class="row g-4">
            <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
              <div class="team-item bg-light">
                <div class="overflow-hidden">
                  <img class="img-fluid" src="img/team-1.jpg" alt="">
                </div>
                <div class="position-relative d-flex justify-content-center" style="margin-top: -23px;">
                  <div class="bg-light d-flex justify-content-center pt-2 px-1">
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-instagram"></i></a>
                  </div>
                </div>
                <div class="text-center p-4">
                  <h5 class="mb-0">Instructor Name</h5>
                  <small>Designation</small>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
              <div class="team-item bg-light">
                <div class="overflow-hidden">
                  <img class="img-fluid" src="img/team-2.jpg" alt="">
                </div>
                <div class="position-relative d-flex justify-content-center" style="margin-top: -23px;">
                  <div class="bg-light d-flex justify-content-center pt-2 px-1">
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-instagram"></i></a>
                  </div>
                </div>
                <div class="text-center p-4">
                  <h5 class="mb-0">Instructor Name</h5>
                  <small>Designation</small>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
              <div class="team-item bg-light">
                <div class="overflow-hidden">
                  <img class="img-fluid" src="img/team-3.jpg" alt="">
                </div>
                <div class="position-relative d-flex justify-content-center" style="margin-top: -23px;">
                  <div class="bg-light d-flex justify-content-center pt-2 px-1">
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-instagram"></i></a>
                  </div>
                </div>
                <div class="text-center p-4">
                  <h5 class="mb-0">Instructor Name</h5>
                  <small>Designation</small>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
              <div class="team-item bg-light">
                <div class="overflow-hidden">
                  <img class="img-fluid" src="img/team-4.jpg" alt="">
                </div>
                <div class="position-relative d-flex justify-content-center" style="margin-top: -23px;">
                  <div class="bg-light d-flex justify-content-center pt-2 px-1">
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-sm-square btn-primary mx-1" href=""><i class="fab fa-instagram"></i></a>
                  </div>
                </div>
                <div class="text-center p-4">
                  <h5 class="mb-0">Instructor Name</h5>
                  <small>Designation</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </NOSCRIPT>
    <!-- Team End -->

    <!-- Footer Start -->
    <?php
    include 'footer.php';
    ?>
    <!-- Footer End -->
  </section>
  <!-- End Section -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

</main>



<?php
include 'footer.php'; // Include footer
?>