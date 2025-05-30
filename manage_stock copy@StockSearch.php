<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropdown Test</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Styling */
        .info-box {
            background: linear-gradient(135deg, rgb(89, 117, 227), rgb(87, 103, 244));
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            margin: 20px auto;
            max-width: 800px;
        }

        .info-box h3 {
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-box .form-control {
            border: 2px solid #fff;
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            appearance: menulist;
            -webkit-appearance: menulist;
            -moz-appearance: menulist;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
        }

        .info-box .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .info-box .form-control:focus {
            background-color: rgba(255, 255, 255, 0.3);
            border-color: #fff;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
            outline: none;
        }

        .info-box button {
            background-color: rgba(245, 151, 117, 0.94);
            border: none;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 20px;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .info-box button:hover {
            background-color: #e56740;
        }

        .info-box a.btn {
            background-color: #6c757d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 10px;
        }

        .info-box a.btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div>
        <!-- Filtering -->
        <div class="info-box">
            <form method="GET" action="manage_stock.php" id="filter">
                <div class="row g-3 align-items-center"> <!-- Use Bootstrap grid and alignment -->
                    <!-- Dropdown -->
                    <div class="col-md-6">
                        <label for="filter_part_number" class="form-label"><strong>Filter by Part Number</strong></label>
                        <select class="form-control" name="filter_part_number" id="filter_part_number">
                            <option value="">Select Part number</option>
                            <?php
                            // Simulate database results
                            $st_pn_fi_re = [
                                ['part_number' => 'PN001'],
                                ['part_number' => 'PN002'],
                                ['part_number' => 'PN003'],
                                ['part_number' => 'PN004'],
                                ['part_number' => 'PN005'],
                            ];
                            foreach ($st_pn_fi_re as $st_pn_fi) {
                                echo "<option value='{$st_pn_fi['part_number']}'>{$st_pn_fi['part_number']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Filter Button -->
                    <div class="col-md-3">
                        <button type="submit" name="filter" class="btn btn-primary w-100">Filter</button>
                    </div>

                    <!-- Reset Button -->
                    <div class="col-md-3">
                        <a href="manage_stock.php" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // JavaScript to expand dropdown on click
        document.querySelector('.form-control[name="filter_part_number"]').addEventListener('click', function() {
            this.size = this.options.length; // Expand the dropdown to show all options
        });

        document.querySelector('.form-control[name="filter_part_number"]').addEventListener('blur', function() {
            this.size = 1; // Collapse the dropdown when focus is lost
        });

        document.querySelector('.form-control[name="filter_part_number"]').addEventListener('change', function() {
            this.size = 1; // Collapse the dropdown after selection
        });
    </script>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>