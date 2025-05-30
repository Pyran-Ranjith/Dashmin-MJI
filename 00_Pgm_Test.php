<?php
session_start();

// Initialize the carousel visibility state if not set
if (!isset($_SESSION['carousel_inner_img_visible'])) {
    $_SESSION['carousel_inner_img_visible'] = true;
}

// Toggle visibility when the menu item is clicked
if (isset($_GET['toggle_carousel'])) {
    $_SESSION['carousel_inner_img_visible'] = !$_SESSION['carousel_inner_img_visible'];
    // Redirect to the same page to avoid form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Define the list of images for the carousel
$images = [
    './images_cab-1536x480.jpg',
    './images_nmc-award.jpg',
    // './image3.jpg',
    // './image4.jpg'
];

// Initialize the current image index if not set
if (!isset($_SESSION['current_image_index'])) {
    $_SESSION['current_image_index'] = 0;
}

$current_image = $images[$_SESSION['current_image_index']];
$carousel_inner_img_visible = $_SESSION['carousel_inner_img_visible'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Carousel</title>
    <style>
        .carousel {
            display: <?php echo $carousel_inner_img_visible ? 'block' : 'none'; ?>;
            width: 100%;
            height: 200px;
            background-color: #f0f0f0;
            text-align: center;
            line-height: 200px;
            font-size: 24px;
        }
        .carousel img {
            max-width: 100%;
            max-height: 100%;
        }
        .menu {
            list-style-type: none;
            padding: 0;
        }
        .menu li {
            display: inline;
            margin-right: 10px;
        }
        .menu li a {
            text-decoration: none;
            color: blue;
        }
        .menu li a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <ul class="menu">
        <li><a href="?toggle_carousel=1"><?php echo $carousel_inner_img_visible ? 'Hide' : 'Show'; ?> Carousel</a></li>
    </ul>

    <div class="carousel">
        <img id="carousel-image" src="<?php echo $current_image; ?>" alt="Carousel Image">
    </div>

    <script>
        // JavaScript to handle the carousel transition
        const images = <?php echo json_encode($images); ?>; // Pass PHP array to JavaScript
        let currentIndex = <?php echo $_SESSION['current_image_index']; ?>;

        function updateCarousel() {
            currentIndex = (currentIndex + 1) % images.length; // Cycle through images
            document.getElementById('carousel-image').src = images[currentIndex];

            // Optionally, send the updated index to the server to keep it in sync
            fetch('update_index.php?index=' + currentIndex)
                .then(response => response.text())
                .then(data => console.log(data))
                .catch(error => console.error('Error updating index:', error));
        }

        // Set the interval for the carousel (e.g., 3 seconds)
        setInterval(updateCarousel, 3000); // Change 3000 to adjust speed (in milliseconds)
    </script>
</body>
</html>