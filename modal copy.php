<?php
function showModal($msg) {
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
    <div class="modal fade" id="globalModal" tabindex="-1" aria-labelledby="globalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="globalModalLabel">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php echo htmlspecialchars($msg); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="javascript:history.back()" class="btn btn-primary">Go Back</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Script to Auto Show Modal on Page Load -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var globalModal = new bootstrap.Modal(document.getElementById('globalModal'));
        globalModal.show();

        // Fix frozen menu issue after closing modal
        document.getElementById('globalModal').addEventListener('hidden.bs.modal', function () {
            document.body.classList.remove('modal-open'); // Remove modal-open class
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove()); // Remove leftover backdrop
        });
    });
    </script>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    </body>
    </html>
    <?php
}
?>
