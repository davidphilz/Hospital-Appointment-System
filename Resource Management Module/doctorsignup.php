<?php
include("include/connection.php");

$show = "";

if(isset($_POST['submit'])) {
    $firstname = trim($_POST['fname']);
    $surname = trim($_POST['sname']);
    $username = trim($_POST['uname']);
    $email = trim($_POST['email']);
    $gender = trim($_POST['gender']);
    $phone = trim($_POST['phone']);
    $state = trim($_POST['state']);
    $password = trim($_POST['pass']);
    $confirm_password = trim($_POST['con_pass']);

    $error = array();

    if(empty($firstname)) {
        $error['submit'] = "Enter Firstname";
    } elseif(empty($surname)) {
        $error['submit'] = "Enter Surname";
    } elseif(empty($username)) {
        $error['submit'] = "Enter Username";
    } elseif(empty($email)) {
        $error['submit'] = "Enter Email";
    } elseif(empty($gender)) {
        $error['submit'] = "Select Gender";
    } elseif(empty($phone)) {
        $error['submit'] = "Enter Phone Number";
    } elseif(empty($state)) {
        $error['submit'] = "Select State";
    } elseif(empty($password)) {
        $error['submit'] = "Enter Password";
    } elseif($confirm_password != $password) {
        $error['submit'] = "Both passwords do not match";
    }

    if(count($error) == 0) {
        $stmt = $connect->prepare("INSERT INTO doctors (firstname, surname, username, email, gender, phone, state, password, salary, data_reg, status, profile) VALUES (?, ?, ?, ?, ?, ?, ?, ?, '0', NOW(), 'pending', 'doctor.jpg')");
        if($stmt) {
            $stmt->bind_param("ssssssss", $firstname, $surname, $username, $email, $gender, $phone, $state, $password);
            if($stmt->execute()){
                header("Location: doctorlogin.php");
                exit();
            } else {
                $error['submit'] = "Failed to register. Please try again.";
            }
            $stmt->close();
        } else {
            $error['submit'] = "Database error: Unable to prepare statement.";
        }
    }

    if(isset($error['submit'])) {
        $s = $error['submit'];
        $show = "<h5 class='text-center alert alert-danger'>$s</h5>";
    } else {
        $show = "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Doctor Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('img/background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        /* Header customization if needed */
        .navbar {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        /* Card styling for a modern glass effect */
        .register-card {
            background: rgba(255,255,255,0.9);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
        }
        h5 {
            font-size: 28px;
            color: #333;
            font-weight: bold;
        }
        label {
            font-weight: 600;
        }
        .form-control {
            border-radius: 50px;
            box-shadow: none;
            border: 1px solid #ced4da;
        }
        .form-select {
            border-radius: 50px;
            box-shadow: none;
            border: 1px solid #ced4da;
        }
        .btn-success {
            border-radius: 50px;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
        }
        p {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    
    <?php include("include/header.php"); ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 register-card my-3">
                <h5 class="text-center mb-4">Doctors Register</h5>

                <div>
                    <?php echo $show; ?>
                </div>

                <form method="post">
                    <div class="form-group mb-3">
                        <label>Firstname</label>
                        <input type="text" name="fname" class="form-control" autocomplete="off" placeholder="Enter Firstname" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Surname</label>
                        <input type="text" name="sname" class="form-control" autocomplete="off" placeholder="Enter Surname" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Username</label>
                        <input type="text" name="uname" class="form-control" autocomplete="off" placeholder="Enter Username" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" autocomplete="off" placeholder="Enter Email Address" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Select Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" autocomplete="off" placeholder="Enter Phone Number" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Select State</label>
                        <select name="state" class="form-control" required>
                            <option value="">Select State</option>
                            <option value="abia">Abia</option>
                            <option value="adamawa">Adamawa</option>
                            <option value="akwaibara">Akwa Ibom</option>
                            <option value="anambra">Anambra</option>
                            <option value="bauchi">Bauchi</option>
                            <option value="bayelsa">Bayelsa</option>
                            <option value="benue">Benue</option>
                            <option value="borno">Borno</option>
                            <option value="cross-river">Cross River</option>
                            <option value="delta">Delta</option>
                            <option value="ebonyi">Ebonyi</option>
                            <option value="edo">Edo</option>
                            <option value="ekiti">Ekiti</option>
                            <option value="enugu">Enugu</option>
                            <option value="gombe">Gombe</option>
                            <option value="imo">Imo</option>
                            <option value="jigawa">Jigawa</option>
                            <option value="kaduna">Kaduna</option>
                            <option value="kano">Kano</option>
                            <option value="kebbi">Kebbi</option>
                            <option value="kogi">Kogi</option>
                            <option value="kwara">Kwara</option>
                            <option value="lagos">Lagos</option>
                            <option value="nasarawa">Nasarawa</option>
                            <option value="niger">Niger</option>
                            <option value="ogun">Ogun</option>
                            <option value="ondo">Ondo</option>
                            <option value="osun">Osun</option>
                            <option value="oyo">Oyo</option>
                            <option value="plateau">Plateau</option>
                            <option value="rivers">Rivers</option>
                            <option value="sokoto">Sokoto</option>
                            <option value="taraba">Taraba</option>
                            <option value="yobe">Yobe</option>
                            <option value="zamfara">Zamfara</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label>Password</label>
                        <input type="password" name="pass" class="form-control" autocomplete="off" placeholder="Enter Password" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Confirm Password</label>
                        <input type="password" name="con_pass" class="form-control" autocomplete="off" placeholder="Enter Confirm Password" required>
                    </div>

                    <input type="submit" name="submit" value="Register" class="btn btn-success w-100">
                    <p class="mt-2">Already have an account? <a href="doctorlogin.php">Click Here</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
