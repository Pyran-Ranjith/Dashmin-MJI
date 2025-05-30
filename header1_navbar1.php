            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="index.html" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
                </a>

                <!-- <a href="#" class="sidebar-toggler flex-shrink-0 d-none">
                    <i class="fa fa-bars"></i>
                </a> -->

                <?php
// Set default session variable (hidden initially)
// if (!isset($_SESSION['sidebarTogglerHidden'])) {
//     $_SESSION['sidebarTogglerHidden'] = "false"; 

// $sidebarTogglerHidden = $_SESSION['sidebarTogglerHidden'];
?>

<a href="#" class="sidebar-toggler flex-shrink-0">
    <i class="fa fa-bars"></i>
</a>

                <!-- Search bar -->
                <!-- <form class="d-none d-md-flex ms-4">
                    <input class="form-control border-0" type="search" placeholder="Search">
                </form> -->

                <!-- <div class="navbar-nav align-items-center ms-auto"> -->
                <div class="navbar-nav align-items-center ms-auto">

                    <!-- Right Side: User Info -->
                    <!-- <div class="col-6 text-end">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <span class="navbar-text text-white me-2">
                                Welcome, <?= $_SESSION['username'] ?> [<?= $_SESSION['role_id'] ?>]
                                Welcome, <?= $_SESSION['username'] ?>
                            </span>
                            <a class="nav-link d-inline text-white" href="logout.php">Logout</a>
                        <?php endif; ?>
                    </div> -->


                    <?php foreach ($menu_items as $menu_item): ?>
                        <li class="nav-item me-3">
                            <a class="nav-link" href="<?= htmlspecialchars($menu_item['menu_link_']) ?>">
                                <?= htmlspecialchars($menu_item['menu_name_']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>



                    <!-- 1-Hide Dropdown -->
                    <?php if (!empty($hide_items_)): ?>
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Hide
                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach ($hide_items_ as $item_): ?>
                                    <li><a class="dropdown-item" href="<?= htmlspecialchars($item_['menu_link']) ?>"><?= htmlspecialchars($item_['menu_name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>

                        <!-- 6-Inquiry Dropdown -->
                        <?php if (!empty($inquiry_items_)): ?>
                            <li class="nav-item dropdown me-3">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    Inquiry
                                </a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($inquiry_items_ as $item_): ?>
                                        <?php
                                        // String with no first word
                                        $item1_ = implode(' ', array_slice(explode(' ', $item_['menu_name']), 1));
                                        ?>
                                        <li><a class="dropdown-item" href="<?= htmlspecialchars($item_['menu_link']) ?>"><?= htmlspecialchars($item1_) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    
                        <noscript>
                <!-- ------------ MODEL Begin ------------------------------------ -->
                        <!-- 6-Inquiry Dropdown -->
                        <?php if (!empty($inquiry_items_)): ?>
                            <li class="nav-item dropdown me-3">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    Inquiry
                                </a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($inquiry_items_ as $item_): ?>
                                        <?php
                                        // String with no first word
                                        $item1_ = implode(' ', array_slice(explode(' ', $item_['menu_name']), 1));
                                        ?>
                                        <li><a class="dropdown-item" href="<?= htmlspecialchars($item_['menu_link']) ?>"><?= htmlspecialchars($item1_) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>
                        </noscript>
                <!-- ------------ MODEL End ------------------------------------ -->

                    <!-- 2-Reports Dropdown -->
                    <?php if (!empty($reports_items_)): ?>
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Reports
                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach ($reports_items_ as $item_): ?>
                                    <?php
                                    // String with no first word
                                    $item1_ = implode(' ', array_slice(explode(' ', $item_['menu_name']), 1));
                                    ?>
                                    <li><a class="dropdown-item" href="<?= htmlspecialchars($item_['menu_link']) ?>"><?= htmlspecialchars($item1_) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- 3-Manage Dropdown -->
                    <?php if (!empty($manage_items_)): ?>
                        <li class="nav-item dropdown me-3">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <!-- $getLanVal = getLanTag($conn, 'Manage' ?>); -->
                                Manage
                                <?php                                // Example usage
                                // $lan_tag = "Manage";
                                // $translation = getLanguageTranslation($conn, $lan_tag);

                                // if ($translation) {
                                //     // echo "Translation for '$lan_tag' in Sinhala: $translation";
                                //     echo $translation;
                                // } else {
                                //     echo "'--$lan_tag--'.";
                                // }
                                ?>
                                <!-- <?php echo $translation ?> Manage -->




                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach ($manage_items_ as $item_): ?>
                                    <?php
                                    // String with no first word
                                    $item1_ = implode(' ', array_slice(explode(' ', $item_['menu_name']), 1));
                                    ?>
                                    <li><a class="dropdown-item" href="<?= htmlspecialchars($item_['menu_link']) ?>"><?= htmlspecialchars($item1_) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>

                        <!-- Batch Dropdown -->
                        <?php if (!empty($batch_items_)): ?>
                            <style>
                                .dropdown-menu {
                                    min-width: 350px;
                                    /* Adjust width as needed */
                                    white-space: normal;
                                    /* Allows text wrapping */
                                    word-wrap: break-word;
                                    /* Ensures words wrap properly */
                                    overflow: visible;
                                }

                                .dropdown-item {
                                    white-space: normal !important;
                                    overflow-wrap: break-word;
                                    word-break: break-word;
                                    /* Force long words to wrap */
                                    display: block;
                                    max-width: 100%;
                                }
                            </style>

                            <li class="nav-item dropdown me-3">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    Batch
                                </a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($batch_items_ as $item_): ?>
                                        <li>
                                            <a class="dropdown-item text-wrap d-block" href="<?= htmlspecialchars($item_['menu_link']) ?>">
                                                <?php
                                                // String with no first word
                                                $item1_ = implode(' ', array_slice(explode(' ', $item_['menu_name']), 1));
                                                ?>
                                                <?= nl2br(htmlspecialchars($item1_)) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- 5-Maintain Dropdown -->
                        <?php if (!empty($maintain_items_)): ?>
                            <li class="nav-item dropdown me-3">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    Maintain
                                </a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($maintain_items_ as $item_): ?>
                                        <?php
                                        // String with no first word
                                        $item1_ = implode(' ', array_slice(explode(' ', $item_['menu_name']), 1));
                                        ?>
                                        <li><a class="dropdown-item" href="<?= htmlspecialchars($item_['menu_link']) ?>"><?= htmlspecialchars($item1_) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <!-- 6-Inquiry Dropdown WORK FINE -->
                        <!-- <?php if (!empty($inquiry_items_)): ?>
                            <li class="nav-item dropdown me-3">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    Inquiry
                                </a>
                                <ul class="dropdown-menu">
                                    <?php foreach ($inquiry_items_ as $item_): ?>
                                        <?php
                                        // String with no first word
                                        $item1_ = implode(' ', array_slice(explode(' ', $item_['menu_name']), 1));
                                        ?>
                                        <li><a class="dropdown-item" href="<?= htmlspecialchars($item_['menu_link']) ?>"><?= htmlspecialchars($item1_) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endif; ?> -->

                        <!-- 7-Setting_ Dropdown WORKS WELL-->
                        <noscript>
                            <?php if (!empty($setting_items_)): ?>
                                <li class="nav-item dropdown me-3">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                        Setting
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($setting_items_ as $item_): ?>
                                            <?php
                                            // String with no first word
                                            $item1_ = implode(' ', array_slice(explode(' ', $item_['menu_name']), 1));
                                            ?>
                                            <!-- <li><a class="dropdown-item" href="<?= htmlspecialchars($item_['menu_link']) ?>"><?= htmlspecialchars($item1_) ?></a></li> -->
                                            <li><a class="dropdown-item" href="?toggle_carousel=1"><?php echo $carousel_inner_img_visible ? 'Hide' : 'Show'; ?> Carousel</a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                        </noscript>

                        <!-- Message drop down -->
                        <!-- <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-envelope me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Message</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="img/Yashmila-2.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="img/Yashmila-2.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="img/Yashmila-2.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item text-center">See all message</a>
                        </div>
                    </div> -->

                        <!-- Message drop down -->
                        <!-- <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-envelope me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Message</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="img/Yashmila-2.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="img/Yashmila-2.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle" src="img/Yashmila-2.jpg" alt="" style="width: 40px; height: 40px;">
                                    <div class="ms-2">
                                        <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                                        <small>15 minutes ago</small>
                                    </div>
                                </div>
                            </a>
                            <hr class="dropdown-divider">
                            <a href="#" class="dropdown-item text-center">See all message</a>
                        </div>
                    </div> -->

                        <!-- Language drop down -->
                        <noscript>
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                    <!-- <i class="fa fa-language me-lg-2"></i> -->
                                    <span class="d-none d-lg-inline-flex">Language</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                                    <a href="set_language.php?lang=english" class="dropdown-item">
                                        <!-- <a href="#" class="dropdown-item" onclick="setLanguage('english')">English</a> -->
                                        <h6 class="fw-normal mb-0">English</h6>
                                        <!-- <small>15 minutes ago</small> -->
                                    </a>
                                    <hr class="dropdown-divider">
                                    <a href="set_language.php?lang=sinhala" class="dropdown-item">
                                        <h6 class="fw-normal mb-0">Sinhala</h6>
                                        <!-- <small>15 minutes ago</small> -->
                                    </a>
                                </div>
                            </div>
                        </noscript>


                        <!-- Profile drop down -->
                        <!-- <?php if (isset($_SESSION['user_id'])) : ?>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                <img class="rounded-circle me-lg-2" src="img/<?php echo $_SESSION['user_img'] ?>" alt="" style="width: 40px; height: 40px;">
                                <span class="d-none d-lg-inline-flex"><?php echo $_SESSION['username'] ?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                                <a href="logout.php" class="dropdown-item">Log Out</a>
                            </div>
                        </div>
                    <?php endif; ?> -->

                        <!-- Profile drop down -->
                        <?php if (isset($_SESSION['user_id'])) : ?>
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                    <img class="rounded-circle me-lg-2" src="img/<?php echo $_SESSION['user_img'] ?>" alt="img/<?php echo $_SESSION['user_img'] ?>" style="width: 40px; height: 40px;">
                                    <span class="d-none d-lg-inline-flex"><?php echo $_SESSION['username'] ?></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                                    <div class="dropdown dropend">
                                        <!-- <a href="#" class="dropdown-item dropdown-toggle" data-bs-toggle="dropdown">Language</a> -->
                                        <!-- <div class="dropdown-menu bg-light border-0 rounded-0 rounded-bottom m-0">
                                        <a href="#" class="dropdown-item">English</a>
                                        <a href="#" class="dropdown-item">Sinhala</a>
                                    </div> -->
                                    </div>
                                    <a href="logout.php" class="dropdown-item">Log Out</a>
                                </div>
                            </div>
                        <?php endif; ?>



                </div>
            </nav>
            <!-- Navbar End -->

            <?php
            // Language
            function getLanTag($conn, $lan_tag)
            {
                $lan_tag = "Manage";
                $sql = "
                SELECT * FROM language 
                WHERE lan_tag = :lan_tag 
                ";
                $language = $conn->prepare($sql);
                $language->execute(['lan_tag' => $lan_tag]);
                $row = $language->fetch(PDO::FETCH_ASSOC);
                $lan_sihhala = $row['lan_sihhala'];
                return $row['lan_sihhala'];
            }
            ?>