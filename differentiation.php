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
  <!-- <a href="<?php echo $previous_page; ?>" class="btn btn-primary mb-2">Go Back</a> -->
  <!-- Topic -->
  <!-- <h1 class="text-center mt-5"><?php echo $topic ?></h1> -->

  <!-- Begin Section -->
  <section id="hero" class="hero section dark-background">

    <!-- YOUR CONTENT -->
    <!-- <img src="prev/assets/img/outline.jpg" alt="" data-aos="fade-in"> -->
    <!-- Spinner Start -->
    <!-- <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div> -->
    <!-- Spinner End -->

    <!-- Header Start -->
    <div class="container-fluid bg-primary py-5 mb-5 page-header">
      <div class="container py-5">
        <div class="row justify-content-center">
          <div class="col-lg-10 text-center">
            <div class="overflow-hidden">
              <img class="img-fluid" src="differentiation.png" alt="differentiation.png">
            </div>
            <h1 class="display-3 text-white animated slideInDown">Differentiation</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb justify-content-center">
                <li class="breadcrumb-item"><a class="text-white" href="index1.php">Home</a></li>
                <!-- <li class="breadcrumb-item"><a class="text-white" href="#">Pages</a></li>
                <li class="breadcrumb-item text-white active" aria-current="page">About</li> -->
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
    <!-- Header End -->
    <h4 class="display-5 text-black animated slideInDown text-center">What makes us stand out?</h4>

    <!-- Service Start -->
    <!-- <NOSCRIPT> -->
      <div class="container-xxl py-5">
        <div class="container">
          <div class="row g-4">
            <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
              <div class="service-item text-center pt-3">
                <div class="p-4">
                  <i class="fa fa-3x fa-graduation-cap text-primary mb-4"></i>
                  <h5 class="mb-3">Experience</h5>
                  <p>We differentiate ourselves from our competitors through the experience and assistance we deliver to customers. The key theme in our business is to serve all our customers equally, whether they are big or small. We believe that our customers and their needs and expectations lead us, and we are committed to provide them with the best assistance.</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.3s">
              <div class="service-item text-center pt-3">
                <div class="p-4">
                  <i class="fa fa-3x fa-globe text-primary mb-4"></i>
                  <h5 class="mb-3">Support</h5>
                  <p>The support and after-sales services we provide to our customers is the cornerstone of our success, and that has made us win many awards at TOKICO Awards Nights held in overseas countries in consecutive years.</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.5s">
              <div class="service-item text-center pt-3">
                <div class="p-4">
                  <i class="fa fa-3x fa-home text-primary mb-4"></i>
                  <h5 class="mb-3">Dedicated Service</h5>
                  <p>Being the one and only dedicated dealer of genuine shock absorbers, we are able to provide our customers with a wide range of branded quality products all under one roof, with the best price range.</p>
                </div>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.7s">
              <div class="service-item text-center pt-3">
                <div class="p-4">
                  <i class="fa fa-3x fa-book-open text-primary mb-4"></i>
                  <h5 class="mb-3">Recommendation</h5>
                  <p>Throughout the last 25 years, we have built up our growing customer base not merely through advertising, but also by word of mouth of our contented customers, who continue to recommend our honest and efficient service.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <!-- </NOSCRIPT> -->
    <!-- Service End -->

    <!-- About Start -->
    <!-- <NOSCRIPT> -->
    <div class="container-xxl py-5">
      <div class="container">
        <div class="row g-5">
          <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
            <div class="position-relative h-100">
              <img class="img-fluid position-absolute w-100 h-100" src="about-us4.jpg" alt="about-us4.jpg" style="object-fit: cover;">
            </div>
          </div>

          <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
            <h6 class="section-title bg-white text-start text-primary pe-3">Differentiation</h6>
            <h1 class="mb-4">Our Company</h1>
            <p class="mb-4">Over the last 2 decades Namaratne Motor Company has committed itself to provide quality shock absorbers of the world’s leading brands – KYB and TOKICO to the Sri Lankan market.
              Located in Panchikawatta, Maradhana, Sri Lanka, we exclusively sell shock absorbers & is probably the only dealer in Sri Lanka who is specialized in Shock absorbers of all leading brands with the widest range available under one roof.
              Over 25 years of technical experience in shock absorbers have helped us to secure our number one position as the best dealer of branded, high quality shock absorbers in Sri Lanka. With our experience, we also provide free expert advice and consultation services to our customers.
              For many years we have stood out as THE dealer of shock absorbers in Sri Lanka, because we choose to deliver the best personalized service to our customers, since we firmly believe that our customers are responsible for our company’s reason for existence.</p>
              <NOSCRIPT>
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
            </NOSCRIPT>
            <a class="btn btn-primary py-3 px-5 mt-2" href="">Read More</a>
          </div>
          
        </div>
      </div>
    </div>
    <!-- </NOSCRIPT> -->
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
                  <img class="img-fluid" src="team-1.jpg" alt="team-1.jpg">
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
                  <img class="img-fluid" src="team-2.jpg" alt="team-2.jpg">
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
                  <img class="img-fluid" src="team-3.jpg" alt="team-3.jpg">
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
                  <img class="img-fluid" src="team-4.jpg" alt="team-4.jpg">
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

    <!-- Trainers Index Section -->
    <section id="trainers-index" class="section trainers-index">

      <div class="container">

        <div class="row">

          <div class="col-lg-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
            <div class="member">
              <img src="about-us1.jpg" class="img-fluid" alt="about-us1.jpg">
              <NOSCRIPT>
              <div class="member-content">
                <h4>Walter White</h4>
                <span>Web Development</span>
                <p>
                  Magni qui quod omnis unde et eos fuga et exercitationem. Odio veritatis perspiciatis quaerat qui aut aut aut
                </p>
                <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href=""><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
              </NOSCRIPT>
              </div>
          </div><!-- End Team Member -->

          <div class="col-lg-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
            <div class="member">
              <img src="about-us3.jpg" class="img-fluid" alt="about-us3.jpg">
              <NOSCRIPT>
              <div class="member-content">
                <h4>Sarah Jhinson</h4>
                <span>Marketing</span>
                <p>
                  Repellat fugiat adipisci nemo illum nesciunt voluptas repellendus. In architecto rerum rerum temporibus
                </p>
                <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href=""><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
              </NOSCRIPT>
            </div>
          </div><!-- End Team Member -->

          <div class="col-lg-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
            <div class="member">
              <img src="about-us4.jpg" class="img-fluid" alt="about-us4.jpg">
              <NOSCRIPT>
              <div class="member-content">
                <h4>William Anderson</h4>
                <span>Content</span>
                <p>
                  Voluptas necessitatibus occaecati quia. Earum totam consequuntur qui porro et laborum toro des clara
                </p>
                <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href=""><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""><i class="bi bi-linkedin"></i></a>
                </div>
              </div>
              </NOSCRIPT>
            </div>
          </div><!-- End Team Member -->

        </div>

      </div>

    </section><!-- /Trainers Index Section -->

    <!-- Footer Start -->
    <?php
    include 'footer1.php';
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