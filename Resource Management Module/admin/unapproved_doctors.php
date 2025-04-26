<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Unapproved Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .bg-success {
            background: linear-gradient(45deg, #28a745, #218838);
        }
        .bg-info {
            background: linear-gradient(45deg, #17a2b8, #117a8b);
        }
        .bg-warning {
            background: linear-gradient(45deg, #ffc107, #e0a800);
        }
        .bg-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
        }
        .bg-primary {
            background: linear-gradient(45deg, #007bff, #0069d9);
        }
        .bg-secondary {
            background: linear-gradient(45deg, #6c757d, #5a6268);
        }
        .dashboard-card {
            color: white;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, filter 0.3s ease;
            margin-bottom: 20px;
        }
        .dashboard-card:hover {
            transform: scale(1.03);
            filter: brightness(1.1);
        }
        .dashboard-card i {
            font-size: 50px;
        }
    </style>
</head>
<body>
    <?php
    include("../include/header.php");
    ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 bg-dark p-3">
                <?php 
                include("sidenav.php"); 
                ?>
            </div>
            <div class="col-md-10 p-4">
                <h5 class="text-center">Unapproved Request</h5>
                <div id="show"></div>
            </div>
        </div>
    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script type="text/javascript">
      $(document).ready(function() {


        show();

        function show() {
          $.ajax({
            url: "ajax_unapproved_request.php",
            method: "POST",
            success: function(data) {
              $("#show").html(data);
            }
          });
        }


        $(document).on('click', '.approve', function() {
          var id = $(this).attr('id');

          $.ajax({
            url: "ajax_approve_request.php", 
            type: "POST",
            data: {id:id},
            success: function(data) {

              show();
            }
          });
        });

        $(document).on('click', '.reject', function() {
          var id = $(this).attr('id');

          $.ajax({
            url: "ajax_reject_request.php", 
            type: "POST",
            data: {id:id},
            success: function(data) {

              show();
            }
          });
        });
      });
    </script>
</body>
</html>
