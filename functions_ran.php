<?php
function showModalNotifyJs($title, $msg, $continuePgm)
{
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Modal Message</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body>
        <!-- Bootstrap Modal -->
        <div class="modal fade show" id="globalModal" tabindex="-1" aria-labelledby="globalModalLabel" aria-hidden="true" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="globalModalLabel"><?php echo $title ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeButton"></button>
                    </div>
                    <div class="modal-body">
                        <p><?php echo htmlspecialchars($msg); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="btnClose">Continue</button>
                        <a href="javascript:history.back()" class="btn btn-primary" id="btnBack">Go Back</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Script to Handle Modal Actions -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Handle Close Button Click
                document.getElementById('btnClose').addEventListener('click',
                    function() {
                        // window.location.href = '<?php echo $continuePgm ?>'; // Redirect to continue PHP page after closing
                    }
                );

                // Handle X (close button) Click
                // document.getElementById('closeButton').addEventListener('click', function() {
                // Optionally redirect after closing
                // });
                // <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                // Handle "Go Back" Button Click
                document.getElementById('btnBack').addEventListener('click', function() {
                    window.location.href = 'previous.php'; // Redirect to previous page if required
                });
            });
        </script>
    </body>

    </html>
<?php } ?>


<?php
function showModalNotify($title="Notification", $msg="Message", $continuePgm="#", $modalWidth="sm")
{
    $params = explode('|', $msg);
    $params_sep = implode("<br>", $params);
    
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Modal Message</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body>
        <!-- Bootstrap Modal -->
        <div class="modal fade show" id="globalModal" tabindex="-1" aria-labelledby="globalModalLabel" aria-hidden="true" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-<?php echo $modalWidth ?>">
                <div class="modal-content">
                    <div class="card">
                        <!-- Card Header -->
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo $title ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeButton"></button>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">
                            <p><?php echo $params_sep ?></p>
                        </div>

                        <!-- Card Footer (Modal Footer) -->
                        <div class="card-footer text-end">
                            <button type="button" class="btn btn-primary" id="btnClose">Continue</button>
                            <a href="javascript:history.back()" class="btn btn-secondary" id="btnBack">Go Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Script to Handle Modal Actions -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Handle Close Button Click
                document.getElementById('btnClose').addEventListener('click', function() {
                    window.location.href = '<?php echo $continuePgm ?>'; // Redirect to continue PHP page after closing
                });

                // Handle "Go Back" Button Click
                document.getElementById('btnBack').addEventListener('click', function() {
                    window.location.href = 'previous.php'; // Redirect to previous page if required
                });
            });
        </script>
    </body>

    </html>
<?php } ?>

<?php
function showModalInput($msg, $continuePgm)
{
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Interactive Notification</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </head>

    <body>
        <!-- Modal (Notification with Input Box) -->
        <div class="modal fade show" id="notifyModal" tabindex="-1" aria-labelledby="notifyModalLabel" aria-hidden="true" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notifyModalLabel">Interactive Notification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Please provide your input:</p>
                        <input type="text" id="userInput" class="form-control" placeholder="Type your response here">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="submitResponse()">Submit</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const modal = new bootstrap.Modal(document.getElementById('notifyModal'));
                modal.show();
            });

            // Handle user response submission
            function submitResponse() {
                const userInput = document.getElementById('userInput').value;
                alert(`You entered: ${userInput}`); // Show the user input
                const modal = bootstrap.Modal.getInstance(document.getElementById('notifyModal'));
                modal.hide(); // Close the modal
            }
        </script>
    </body>

    </html>
<?php } ?>

<?php
function getWeightedAverageCost($conn, $part_id) {
    try {
        $stmt = $conn->prepare("SELECT 
                                SUM(quantity * cost) / SUM(quantity) AS avg_cost,
                                SUM(quantity) AS total_quantity
                               FROM fifo_queue1
                               WHERE part_id = ? 
                               AND is_processed = 0");
        $stmt->execute([$part_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'avg_cost' => $result['avg_cost'] ?? 0,
            'total_quantity' => $result['total_quantity'] ?? 0
        ];
    } catch (PDOException $e) {
        error_log("Error calculating weighted average: " . $e->getMessage());
        return [
            'avg_cost' => 0,
            'total_quantity' => 0
        ];
    }
}
?>