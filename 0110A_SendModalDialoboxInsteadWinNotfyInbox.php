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
    <!-- Trigger Button -->
    <div class="container mt-5">
        <button class="btn btn-primary" onclick="showNotification()">Show Notification</button>
    </div>

    <!-- Modal (Notification with Input Box) -->
    <div class="modal fade" id="notifyModal" tabindex="-1" aria-labelledby="notifyModalLabel" aria-hidden="true">
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
        // Show the notification modal
        function showNotification() {
            const modal = new bootstrap.Modal(document.getElementById('notifyModal'));
            modal.show();
        }

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
