<?php
// Function to fetch language translation
function getLanguageTranslation($conn, $lan_tag)
{
    $lan_set = ($_SESSION['language'] == 'sinhala') ? 'lan_sinhala' : 'lan_english';
    $sql = "
        SELECT * 
        FROM language 
        WHERE lan_tag = :lan_tag
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['lan_tag' => $lan_tag]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return the translation if found, otherwise return null
    return $row ? $row[$lan_set] : null;
}

// // Example usage
// $lan_tag = "Manage";
// $translation = getLanguageTranslation($conn, $lan_tag);

// if ($translation) {
//     echo "Translation for '$lan_tag' in Sinhala: $translation";
// } else {
//     echo "No translation found for '$lan_tag'.";
// }
