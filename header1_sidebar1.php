<?php
$previous_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'javascript:history.back()';

// Fetch menu items from the database (or hardcoded for now)
$menu_items = [
    ['menu_name_' => 'Dashboard', 'menu_link_' => 'index1.php'],
];

$stmt_ = $conn->prepare("
SELECT mo.menu_name, mo.menu_link, mt.tag_name AS mt_tag_name, mo.flag FROM menu_options mo 
LEFT JOIN role_menu_options rmo ON mo.id = rmo.menu_option_id 
JOIN menu_tags mt ON rmo.menu_tag_id = mt.id
WHERE rmo.role_id = :role_id
    -- For testing in Phpadmin
    -- WHERE rmo.role_id = 5 
        AND mo.flag = 'active'
-- and (mo.menu_name LIKE ('%Manage%') ||  mo.menu_name LIKE ('%Dashboard%')) 
    -- and (mt.tag_name LIKE ('%Batch%') ||  mt.tag_name LIKE ('%Batch%')) 
ORDER BY mo.menu_name, rmo.role_id, rmo.roleorder ASC
");
$stmt_->bindParam(':role_id', $_SESSION['role_id'], PDO::PARAM_INT);
$stmt_->execute();
// Fetch all results into the $menu_items array
$menu_items_ = $stmt_->fetchAll(PDO::FETCH_ASSOC);
$menu_items_result_ = $stmt_->fetch(PDO::FETCH_ASSOC);

// Language
// $sql = "
// SELECT * FROM language 
// WHERE lan_tag = :lan_tag 
// ";
// $lan_tag = "Manage";
// $language = $conn->prepare($sql);
// $language->execute(['lan_tag' => $lan_tag]);
// $row = $language->fetch(PDO::FETCH_ASSOC);
// $lan_sihhala = $row['lan_sihhala'];

// Prepare categorized arrays
$manage_items_ = [];
$batch_items_ = [];
$maintain_items_ = [];
$internal_items_ = [];
$reports_items_ = [];
// $inquiry_items_ = [];
$hide_items_ = [];
$inquiry_items_ = [];
$setting_items_ = [];

foreach ($menu_items_ as $item_):
    // foreach ($menu_items_ as $menu_item_) {
    if ($item_['mt_tag_name'] === 'Hide') { // Check if the first word is 'Hide'
        $hide_items_[] = $item_;
    } elseif ($item_['mt_tag_name'] === 'Manage') { // Check if the first word is 'Manage'
        $manage_items_[] = $item_;
    } elseif ($item_['mt_tag_name'] === 'Reports') { // Check if the first word is 'Reports'
        $reports_items_[] = $item_;
    // } elseif ($item_['mt_tag_name'] === 'Inquiry') { // Check if the first word is 'Inquiry'
    //     $inquiry_items_[] = $item_;
   } elseif ($item_['mt_tag_name'] === 'Int') { // Check if the first word is 'Int'
        $int_items_[] = $item_;
    } elseif ($item_['mt_tag_name'] === 'Batch') { // Check if the first word is 'Batch'
        $batch_items_[] = $item_;
    } elseif ($item_['mt_tag_name'] === 'Maintain') { // Check if the first word is 'Maintain'
        $maintain_items_[] = $item_;
    } elseif ($item_['mt_tag_name'] === 'Inquiry') { // Check if the first word is 'Inquiry'
        $inquiry_items_[] = $item_;
    } elseif ($item_['mt_tag_name'] === 'Setting') { // Check if the first word is 'Setting'
        $setting_items_[] = $item_;
    }
endforeach;
?>

    <!-- Sidebar content -->
<!-- Sidebar Start -->
<!-- <div class="sidebar pe-4 pb-3"> -->
<div class="sidebar pe-4 pb-3" style="width: 250px;">
    <nav class="navbar bg-light navbar-light">
        <a href="index.html" class="navbar-brand mx-4 mb-3">
            <!-- <h3 class="text-primary"><i class="fa fa-hashtag me-2"></i><?php echo $company_name ?></h3> -->
            <h3 class="text-primary"><img class="rounded-circle" src="./nmc.jpg" alt="" style="width: 80px; height: 40px;"><?php echo $company_name ?></h3>
            <!-- <p class="text-danger"><?php echo $prj_id_ ?></p> -->
            <div class="ms-3">
                <h6 class="mb-0"><?php echo $prj_id_ ?></h6>
            </div>
        </a>
        <?php if (isset($_SESSION['user_id'])) : ?>
            <div class="d-flex align-items-center ms-4 mb-4">
                <div class="position-relative">
                    <img class="rounded-circle" src="img/<?php echo $_SESSION['user_img'] ?>" alt="" style="width: 40px; height: 40px;">
                    <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                </div>
                <div class="ms-3">
                    <h6 class="mb-0"><?php echo $_SESSION['username'] ?></h6>
                    <span><?php echo $_SESSION['role_name'] ?></span> <!-- Admin -->
                </div>
            </div>
        <?php endif; ?>

        <div class="navbar-nav w-100">

            <!-- Temporary -->
            <NOSCRIPT>
                <a href="Outline.php" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>Outline</a>
                <a href="index.html_" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>Index.html</a>
            </NOSCRIPT>

            <!-- Courses -->
            <div class="navbar-nav w-100">

                <a href="index11.php" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Home</a>
                <a href="dashboard1.php" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                <div class="nav-item dropdown">
                    <!-- Developing Tools -->
                    <?php if ($_SESSION['role_id'] == 5) : ?>

                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-laptop me-2"></i>Developing Tools</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="outline.php?topic=Outline" class="dropdown-item"><i class="far fa-file-alt me-2"></i>Outline</a>
                            <a href="index.html_" class="dropdown-item"><i class="far fa-file-alt me-2"></i>index.html_</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <a href="about.php?topic=About Us" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>About Us</a>
        <a href="contact.php?topic=Contact Us" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Contact Us</a>
        <a href="products_&_services.php?topic=Products & Services" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Products & Services</a>
        <a href="differentiation.php?topic=Differentiation" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Differentiation</a>
        <NOSCRIPT>
            <a href="courses.html_?topic=Courses" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Courses</a>
            <a href="team.html_?topic=Team" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Team</a>
            <a href="testimonial.html_?topic=Testimonial" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Testimonial</a>
        </NOSCRIPT>

        <NOSCRIPT>
            <div class="nav-item dropdown">
                <!-- About Us -->
                <a href="about.html_" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-laptop me-2"></i>About Us</a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="about.php?topic=About Us" class="dropdown-item"><i class="far fa-file-alt me-2"></i>About Us</a>
                </div>
            </div>
        </NOSCRIPT>
</div>
<!-- O/Levels -->
<NOSCRIPT>
    <!-- <a href="index.html" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a> -->
    <div class="nav-item dropdown">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-laptop me-2"></i>O-Levels</a>
        <div class="dropdown-menu bg-transparent border-0">
            <!-- <a href="index.html" class="nav-item nav-link active"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a> -->
            <!-- <a href="subject_content.php?part_id=ols_sin_lan&topic=O/L Sinhala Language" class="dropdown-item"><i class="far fa-file-alt me-2"></i>Sinhala Language</a> -->
            <a href="subject_content.php?part_id=ols_sin_lan&user_id=<?php echo $_SESSION['user_id'] ?>&topic=O/L Sinhala Language" class="dropdown-item"><i class="far fa-file-alt me-2"></i>Sinhala Language</a>
            <a href="subject_content.php?topic=O/L Sinhala Litriture" class="dropdown-item"><i class="far fa-file-alt me-2"></i>Sinhala Litriture</a>
            <!-- <a href="element.html" class="dropdown-item">Other Elements</a> -->
        </div>

    </div>
</NOSCRIPT>
</div>
</div>

<!-- A/Levels     -->
<NOSCRIPT>
    <div class="nav-item dropdown">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-laptop me-2"></i>A-Levels</a>
        <div class="dropdown-menu bg-transparent border-0">
            <a href="subject_content.php?topic=A/L Sinhala Language" class="dropdown-item"><i class="far fa-file-alt me-2"></i>Sinhala Language</a>
            <a href="subject_content.php?topic=A/L Sinhala Litriture" class="dropdown-item"><i class="far fa-file-alt me-2"></i>Sinhala Litriture</a>
            <!-- <a href="404.html" class="dropdown-item">404 Error</a>
                            <a href="blank.html" class="dropdown-item">Blank Page</a> -->
        </div>
    </div>
</NOSCRIPT>

<!-- Primary Education -->
<NOSCRIPT>
    <div class="nav-item dropdown">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-laptop me-2"></i>Primary Education</a>
        <div class="dropdown-menu bg-transparent border-0">
            <a href="subject_content.php?topic=Primary Mathematics" class="dropdown-item"><i class="far fa-file-alt me-2"></i>Mathematics</a>
            <a href="subject_content.php?topic=Primary Sinhala Language" class="dropdown-item"><i class="far fa-file-alt me-2"></i>Sinhala Language</a>
            <a href="subject_content.php?topic=Primary Enviorenment" class="dropdown-item"><i class="far fa-file-alt me-2"></i>Enviorenment</a>
            <a href="subject_content.php?topic=Primary Buddhism" class="dropdown-item"><i class="far fa-file-alt me-2"></i>Buddhism</a>
            <a href="subject_content.php?topic=Primary English Language" class="dropdown-item"><i class="far fa-file-alt me-2"></i>English Language</a>
        </div>
    </div>
</NOSCRIPT>

</div>
</div>


</nav>
</div>
<!-- Sidebar End -->

