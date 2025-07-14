<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Slip verify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit&display=swap');
        * { font-family: "Kanit", sans-serif; }
        .cr { background-color: #DE3163; max-width: 600px; }
        .response-box { background-color: #fff; text-align: left; }
    </style>
</head>
<body>
    <div class="container text-center my-3 p-3 rounded cr">
        <h4 class="text-white">Slip Verify Image Upload API Example</h4>
        <form method="POST" action="process.php" enctype="multipart/form-data">
            <div class="form-group my-2">
                <label for="slip_image" class="mb-2 bg-warning p-2 rounded">
                    <i class="ri-file-image-line"></i> รูปสลิปที่มี QRCode เท่านั้น*
                </label>
                <input class="form-control" type="file" accept="image/*" name="slip_image" id="slip_image" required />
            </div>
            <button class="btn btn-primary w-100 mb-2">
                <i class="ri-contract-line"></i> ตรวจสอบข้อมูล
            </button>
        </form>

        <?php if (!empty($_SESSION['uploaded_image'])): ?>
            <img src="<?= htmlspecialchars($_SESSION['uploaded_image']) ?>" height="550px" width="250px" class="my-2 rounded" />
        <?php endif; ?>

        <?php if (!empty($_SESSION['responseData'])): ?>
            <script>
                Swal.fire({
                    title: 'ผลการตรวจสอบสลิป',
                    html: <?= json_encode(nl2br($_SESSION['responseData'])) ?>,
                    icon: <?= strpos($_SESSION['responseData'], '✅') !== false ? "'success'" : (strpos($_SESSION['responseData'], '⚠️') !== false ? "'warning'" : "'error'") ?>,
                    confirmButtonText: 'ตกลง'
                });
            </script>
        <?php endif; ?>
        <?php
        unset($_SESSION['uploaded_image']);
        unset($_SESSION['responseData']);
        ?>

        <?php
            if (isset($_SESSION['api_response_raw'])):
                $prettyJson = json_encode(json_decode($_SESSION['api_response_raw']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            ?>
                <div class="response-box p-3 mt-3 rounded">
                    <h5><i class="ri-code-s-slash-line"></i> API JSON Response</h5>
                    <pre style="max-height: 400px; overflow: auto; background: #f8f9fa; padding: 1rem; border-radius: .5rem;"><?= htmlspecialchars($prettyJson) ?></pre>
                </div>
            <?php
                unset($_SESSION['api_response_raw']);
            endif;
        ?>
    </div>
</body>
</html>
