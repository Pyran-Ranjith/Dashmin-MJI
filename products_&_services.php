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
                            <img class="img-fluid" src="Products_&_Services_NMC_files.png" alt="Products_&_Services_NMC_files.png">
                        </div>
                        <h1 class="display-3 text-white animated slideInDown">Products & Services</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center">
                                <li class="breadcrumb-item"><a class="text-white" href="index1.php">Home</a></li>
                                <!-- <li class="breadcrumb-item"><a class="text-white" href="#">Pages</a></li>
                                <li class="breadcrumb-item text-white active" aria-current="page">Contact</li> -->
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- Header End -->

        <!-- Products & Services Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="row g-5">

                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
                        <div class="position-relative h-100">
                            <img class="img-fluid position-absolute w-100 h-100" src="kyb1.jpg" alt="kyb1.jpg" style="object-fit: cover;">
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                        <h6 class="section-title bg-white text-start text-primary pe-3">About Us</h6>
                        <h1 class="mb-4">KYB - SHOCK ABSORBERS: PREMIUM</h1>
                        <h5 class="mb-4">Oil shock absorbers, struts and cartridges.</h5>
                        <p class="mb-4">
                            Because it is factory assembled and factory exact, your customers can expect its built-in performance features to consistently deliver better-than-new road control and riding comfort.
                            Premium is specially designed to compensate for the accumlated wear and tear on other parts of the suspension system. So it will provide a better ride and better steering.
                            Economical replacement for OE
                            Three stage dual valving
                            Multi lip oil seal
                            Teflon coated piston valve
                            Hard chromed piston rod.
                        </p>
                        <!-- <NOSCRIPT> -->
                            <div class="row gy-2 gx-4 mb-4">
                                <div class="col-sm-6">
                                    <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Economical replacement for OE</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Three stage dual valving</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Multi lip oil seal</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Teflon coated piston valve</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Hard chromed piston rod</p>
                                </div>
                            </div>
                        <!-- </NOSCRIPT> -->
                        <a class="btn btn-primary py-3 px-5 mt-2" href="">Read More</a>
                    </div>

                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
                        <div class="position-relative h-100">
                            <img class="img-fluid position-absolute w-100 h-100" src="EXCEL-G.jpg" alt="EXCEL-G.jpg" style="object-fit: cover;">
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                        <h6 class="section-title bg-white text-start text-primary pe-3">About Us</h6>
                        <h1 class="mb-4">KYB - SHOCK ABSORBERS: EXCEL-G</h1>
                        <h5 class="mb-4">Twin tube gas shock absorbers, struts and cartridges.</h5>
                        <p class="mb-4">
                            Patented valving plus pressurized nitrogen gas account for riding comfort and,
                            at the same time, dramatically reduce the aeration or foaming that commonly occur
                            in shock absorber and cause its performance to start fading, even after only a
                            few minutes in operation.
                        </p>
                        <!-- <NOSCRIPT> -->
                        <div class="row gy-2 gx-4 mb-4">
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Restores OE performance</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>One way anti-foaming valve reduces foaming and performance fade</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Teflon coated piston valv</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Seamless inner cylinder and eye ring, no leaks</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Bonded bushings and sleeves</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>International Certificate</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Hard chromed piston rod</p>
                            </div>
                        </div>
                        <!-- </NOSCRIPT> -->
                        <a class="btn btn-primary py-3 px-5 mt-2" href="">Read More</a>
                    </div>
                    <!-- <h3> ------------------------------------------------------------------------ </h3> -->

                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
                        <div class="position-relative h-100">
                            <img class="img-fluid position-absolute w-100 h-100" src="gas-a-just.jpg" alt="gas-a-just.jpg" style="object-fit: cover;">
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                        <h6 class="section-title bg-white text-start text-primary pe-3">About Us</h6>
                        <h1 class="mb-4">KYB - SHOCK ABSORBERS: GAS-A-JUST</h1>
                        <h5 class="mb-4">Monotube high pressure gas shock absorbers.</h5>
                        <p class="mb-4">
                        </p>
                        <!-- <NOSCRIPT> -->
                        <div class="row gy-2 gx-4 mb-4">
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Restores OE performance</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Increases performance over OE twin tube</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Completely eliminates foaming and performance fade</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Up to 30% more damping than twin tube</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Single, seamless working cylinder, heavy duty guage</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Phenolic piston seal provides consistent performance</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Additional floating piston separates oil and compresses nitrogen gas to add
                                    velocity sensitive performance</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Seamless mounts and bonded bushings adds strength and eliminates noise
                                </p>
                            </div>
                        </div>
                        <!-- </NOSCRIPT> -->
                        <a class="btn btn-primary py-3 px-5 mt-2" href="">Read More</a>
                    </div>
                    <!-- <h3> ----ULTRA-SR---------------------------------------------------------------- </h3> -->

                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
                        <div class="position-relative h-100">
                            <img class="img-fluid position-absolute w-100 h-100" src="ultra-pic1.jpg" alt="ultra-pic1.jpg" style="object-fit: cover;">
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                        <h6 class="section-title bg-white text-start text-primary pe-3">About Us</h6>
                        <h1 class="mb-4">KYB - SHOCK ABSORBERS: ULTRA-SR</h1>
                        <h5 class="mb-4">Monotube high pressure gas shock absorbers, struts and cartridges.</h5>
                        <p class="mb-4">
                        Ultra SR is specially designed for excellent driving stability with maximum safety for the ambitious sports driver with high demands on road handling. Gas filled MacPherson.
                        </p>
                        <!-- <NOSCRIPT> -->
                        <div class="row gy-2 gx-4 mb-4">
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>For the sports driver</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Excellent driving stability with maximum safety</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Harder damping force enables stable road holding and steering</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>High performance when used with steel belt or low profile tyres Completelye</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Eliminates foaming and performance fade</p>
                            </div>
                        </div>
                        <!-- </NOSCRIPT> -->
                        <a class="btn btn-primary py-3 px-5 mt-2" href="">Read More</a>
                    </div>
                    <!-- <h3> ------------------------------------------------------------------------ </h3> -->
                    <!-- <h3> ----AGX---------------------------------------------------------------- </h3> -->

                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.1s" style="min-height: 400px;">
                        <div class="position-relative h-100">
                            <img class="img-fluid position-absolute w-100 h-100" src="AGX.jpg" alt="AGX.jpg" style="object-fit: cover;">
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeInUp" data-wow-delay="0.3s">
                        <h6 class="section-title bg-white text-start text-primary pe-3">About Us</h6>
                        <h1 class="mb-4">KYB - SHOCK ABSORBERS: AGX</h1>
                        <h5 class="mb-4">Adjustable twin tube gas shock absorbers, struts and cartridges</h5>
                        <p class="mb-4">
                        KYB AGX Adjustable Gas Shocks allow drivers to adjust damping to match specific driving conditions.
                        </p>
                        <!-- <NOSCRIPT> -->
                        <div class="row gy-2 gx-4 mb-4">
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Manually adjustable multi stage damping, 4 ways or 8</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>No need to lift vehicle to adjust, no need for special tools</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Settings available for all driving conditions, soft to hard</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Unique, progressive valving</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Sintered iron piston provides greater strength and improved durability</p>
                            </div>
                            <div class="col-sm-6">
                                <p class="mb-0"><i class="fa fa-arrow-right text-primary me-2"></i>Seamless cylinders and eye ring</p>
                            </div>
                        </div>
                        <!-- </NOSCRIPT> -->
                        <a class="btn btn-primary py-3 px-5 mt-2" href="">Read More</a>
                    </div>
                    <!-- <h3> ------------------------------------------------------------------------ </h3> -->

    <!-- Trainers Index Section -->
    <section id="trainers-index" class="section trainers-index">

      <div class="container">

        <div class="row">

          <div class="col-lg-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
            <div class="member">
              <img src="flyer1.jpg" class="img-fluid" alt="flyer1.jpg">
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
              <img src="flyer3.jpg" class="img-fluid" alt="flyer3.jpg">
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
          </div>

          <div class="col-lg-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
            <div class="member">
              <img src="flyer2.jpg" class="img-fluid" alt="flyer2.jpg">
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

          <div class="col-lg-4 col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
            <div class="member">
              <img src="flyer4.jpg" class="img-fluid" alt="flyer4.jpg">
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
                    <!-- <h3> ------------------------------------------------------------------------ </h3> -->
                </div>
            </div>
        </div>

        <!-- Products & Services End -->

        <NOSCRIPT>
            <div class="container-xxl py-5">
                <div class="container">
                    <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                        <h6 class="section-title bg-white text-center text-primary px-3">Contact Us</h6>
                        <h1 class="mb-5">Contact For Any Query</h1>
                    </div>
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                            <h5>Get In Touch</h5>
                            <p class="mb-4">The contact form is currently inactive. Get a functional and working contact form with Ajax & PHP in a few minutes. Just copy and paste the files, add a little code and you're done. <a href="https://htmlcodex.com/contact-form">Download Now</a>.</p>
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary" style="width: 50px; height: 50px;">
                                    <i class="fa fa-map-marker-alt text-white"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="text-primary">Head Office</h5>
                                    <p class="mb-0">No: 113, Panchikawatta Road,
                                        Colombo 10,
                                        Sri Lanka.</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary" style="width: 50px; height: 50px;">
                                    <i class="fa fa-phone-alt text-white"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="text-primary">Telephone</h5>
                                    <p class="mb-0"><strong>Hotline</strong> : + 94 772303936</p>
                                    <p class="mb-0"><strong>Tel</strong> : +94 112335650</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary" style="width: 50px; height: 50px;">
                                    <i class="fa fa-envelope-open text-white"></i>
                                </div>
                                <div class="ms-3">
                                    <h5 class="text-primary">Email</h5>
                                    <p class="mb-0">info@namaratnemotors.com</p>
                                    <p class="mb-0"><strong>QA</strong>: qna@namaratnemotors.com</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                            <iframe class="position-relative rounded w-100 h-100"
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3001156.4288297426!2d-78.01371936852176!3d42.72876761954724!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4ccc4bf0f123a5a9%3A0xddcfc6c1de189567!2sNew%20York%2C%20USA!5e0!3m2!1sen!2sbd!4v1603794290143!5m2!1sen!2sbd"
                                frameborder="0" style="min-height: 300px; border:0;" allowfullscreen="" aria-hidden="false"
                                tabindex="0"></iframe>
                        </div>
                        <div class="col-lg-4 col-md-12 wow fadeInUp" data-wow-delay="0.5s">
                            <form>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="name" placeholder="Your Name">
                                            <label for="name">Your Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" placeholder="Your Email">
                                            <label for="email">Your Email</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="subject" placeholder="Subject">
                                            <label for="subject">Subject</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" placeholder="Leave a message here" id="message" style="height: 150px"></textarea>
                                            <label for="message">Message</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-primary w-100 py-3" type="submit">Send Message</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </NOSCRIPT>

        <!-- Contact End -->

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