Then what should be the updated sql script instead of:
--
-- Triggers `racks`
--
DELIMITER $$
CREATE TRIGGER `set_rack_location_code_insert` BEFORE INSERT ON `racks` FOR EACH ROW BEGIN
    SET NEW.location_code = CONCAT(
        'F', NEW.floor, 
        '-R', NEW.rack_number, 
        '-RW', NEW.row_number, 
        '-C', NEW.column_number, 
        '-', 
        UPPER(LEFT(NEW.side, 1))
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `set_rack_location_code_update` BEFORE UPDATE ON `racks` FOR EACH ROW BEGIN
    SET NEW.location_code = CONCAT(
        'F', NEW.floor, 
        '-R', NEW.rack_number, 
        '-RW', NEW.row_number, 
        '-C', NEW.column_number, 
        '-', 
        UPPER(LEFT(NEW.side, 1))
    );
END
$$
DELIMITER ;
Since you can't create triggers on InfiniteFree, here's the updated SQL script that removes the trigger dependency and implements the same functionality through alternative methods:

Option 1: Using a Generated Column (Recommended if supported)
sql
-- First, modify your racks table to add a generated column
ALTER TABLE `racks` 
ADD COLUMN `location_code` VARCHAR(50) GENERATED ALWAYS AS (
    CONCAT(
        'F', `floor`, 
        '-R', `rack_number`, 
        '-RW', `row_number`, 
        '-C', `column_number`, 
        '-', 
        UPPER(LEFT(`side`, 1))
    )
) STORED;
Option 2: Manual Column Update (If generated columns aren't supported)
sql
-- First, ensure your table has the location_code column
ALTER TABLE `racks` 
ADD COLUMN `location_code` VARCHAR(50) NULL;

-- Then update all existing records
UPDATE `racks` 
SET `location_code` = CONCAT(
    'F', `floor`, 
    '-R', `rack_number`, 
    '-RW', `row_number`, 
    '-C', `column_number`, 
    '-', 
    UPPER(LEFT(`side`, 1))
);
PHP Implementation Alternative
Since InfiniteFree doesn't support triggers, you should handle this in your application code. Here's how to modify your PHP:

php
// Helper function to generate location code
function generateRackLocationCode($floor, $rack_number, $row_number, $column_number, $side) {
    return sprintf('F%d-R%d-RW%d-C%d-%s',
        $floor,
        $rack_number,
        $row_number,
        $column_number,
        strtoupper(substr($side, 0, 1))
    );
}

// When inserting a new rack:
$locationCode = generateRackLocationCode(
    $floor, 
    $rack_number, 
    $row_number, 
    $column_number, 
    $side
);

$stmt = $conn->prepare("
    INSERT INTO `racks` 
    (`floor`, `rack_number`, `row_number`, `column_number`, `side`, `location_code`, ...)
    VALUES (?, ?, ?, ?, ?, ?, ...)
");
$stmt->execute([$floor, $rack_number, $row_number, $column_number, $side, $locationCode, ...]);

// When updating a rack:
$locationCode = generateRackLocationCode(
    $newFloor, 
    $newRackNumber, 
    $newRowNumber, 
    $newColumnNumber, 
    $newSide
);

$stmt = $conn->prepare("
    UPDATE `racks` 
    SET 
        `floor` = ?,
        `rack_number` = ?,
        `row_number` = ?,
        `column_number` = ?,
        `side` = ?,
        `location_code` = ?
    WHERE `id` = ?
");
$stmt->execute([$newFloor, $newRackNumber, $newRowNumber, $newColumnNumber, $newSide, $locationCode, $rackId]);
Complete SQL Without Triggers
Here's how your SQL script should look without triggers:

sql
-- Table structure for `racks`
CREATE TABLE IF NOT EXISTS `racks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floor` int(11) NOT NULL,
  `rack_number` int(11) NOT NULL,
  `row_number` int(11) NOT NULL,
  `column_number` int(11) NOT NULL,
  `side` varchar(10) NOT NULL COMMENT 'left/right',
  `location_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Generate initial location codes for existing data
UPDATE `racks` 
SET `location_code` = CONCAT(
    'F', `floor`, 
    '-R', `rack_number`, 
    '-RW', `row_number`, 
    '-C', `column_number`, 
    '-', 
    UPPER(LEFT(`side`, 1))
);
This approach gives you the same functionality while working within InfiniteFree's restrictions. The location codes will be generated either:

Automatically via the generated column (if supported), or

Manually through your PHP application code during inserts/updates

