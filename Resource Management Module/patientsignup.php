<?php
include("include/connection.php");

if(isset($_POST['create'])){
    $fname = $_POST['fname'];
    $sname = $_POST['sname'];
    $uname = $_POST['uname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];
    $state = $_POST['state'];
    $pass = $_POST['pass'];
    $con_pass = $_POST['con_pass'];


    $error = array();

    if(empty($fname)){
        $error['fname'] = "Enter your Firstname";
    }else if(empty($sname)){
        $error['sname'] = "Enter your Surname";
    }else if(empty($uname)){
        $error['uname'] = "Enter your Username";
    }else if(empty($email)){
        $error['email'] = "Enter your Email"; 
    }else if(empty($phone)){
        $error['phone'] = "Enter your Phone Number";
    }else if($gender == ""){
        $error['gender'] = "Select your Gender";
    }else if($state == ""){
        $error['state'] = "Select your State"; 
    }else if (empty($pass)){
        $error['pass'] = "Enter your Password";
    }else if($con_pass != $pass){
        $error['con_pass'] = "Both passwords do not match";
    }

    if(count($error)==0){
        $query = "INSERT INTO patient(firstname, surname, username, email, phone, gender, state, password, date_reg, profile) VALUES('$fname', '$sname', '$uname', '$email', '$phone', '$gender', '$state', '$pass', NOW(), 'patient.jpg')";

        $res = mysqli_query($connect, $query);
        if($res){
            header("Location: patientlogin.php");
            exit();
        }else{
            echo"<script>alert('Failed to create account.')</script>";
        }
    }   
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Signup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        /* Background with gradient overlay for improved contrast */
        body {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('img/back.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        /* Navbar customization (if used in header) */
        .navbar-custom { background-color: #17a2b8; }
        .navbar-brand { color: #fff; font-weight: 700; }
        /* Card styling with soft rounded corners and subtle shadow */
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border: none;
        }
        .card-header {
            background: rgba(255,255,255,0.95);
            color: #333;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            padding: 15px;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        /* Form label styling */
        .form-label {
            font-weight: 600;
        }
        /* Custom form control styling */
        .form-control, .form-select {
            border-radius: 50px;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
        }
        /* Button styling */
        .btn-success {
            border-radius: 50px;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            transition: background 0.3s ease;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        /* Margin and padding adjustments for the container */
        .container-fluid {
            padding-top: 50px;
            padding-bottom: 50px;
        }
        /* Center the form on the page */
        .form-container {
            margin-top: 30px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include("include/header.php"); ?>
    <div class="container-fluid form-container">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-6">
                <div class="bg-light p-4 rounded">
                    <h5 class="text-center my-3">Create Account</h5>
                    <form method="post">
                        <div class="form-group mb-3">
                            <label>Firstname</label>
                            <input type="text" name="fname" class="form-control" value="<?php echo isset($fname) ? $fname : ''; ?>" autocomplete="off" placeholder="Enter your Firstname" required>
                            <?php if(isset($error['fname'])) echo "<small class='text-danger'>".$error['fname']."</small>"; ?>
                        </div>
                        <div class="form-group mb-3">
                            <label>Surname</label>
                            <input type="text" name="sname" class="form-control" value="<?php echo isset($sname) ? $sname : ''; ?>" autocomplete="off" placeholder="Enter your Surname" required>
                            <?php if(isset($error['sname'])) echo "<small class='text-danger'>".$error['sname']."</small>"; ?>
                        </div>
                        <div class="form-group mb-3">
                            <label>Username</label>
                            <input type="text" name="uname" class="form-control" value="<?php echo isset($uname) ? $uname : ''; ?>" autocomplete="off" placeholder="Enter your Username" required>
                            <?php if(isset($error['uname'])) echo "<small class='text-danger'>".$error['uname']."</small>"; ?>
                        </div>
                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="text" name="email" class="form-control" value="<?php echo isset($email) ? $email : ''; ?>" autocomplete="off" placeholder="Enter your Email" required>
                            <?php if(isset($error['email'])) echo "<small class='text-danger'>".$error['email']."</small>"; ?>
                        </div>
                        <div class="form-group mb-3">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo isset($phone) ? $phone : ''; ?>" autocomplete="off" placeholder="Enter your Phone Number" required>
                            <?php if(isset($error['phone'])) echo "<small class='text-danger'>".$error['phone']."</small>"; ?>
                        </div>
                        <div class="form-group mb-3">
                            <label>Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo isset($gender) && $gender == 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo isset($gender) && $gender == 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo isset($gender) && $gender == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <?php if(isset($error['gender'])) echo "<small class='text-danger'>".$error['gender']."</small>"; ?>
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
                            <?php if(isset($error['state'])) echo "<small class='text-danger'>".$error['state']."</small>"; ?>
                        </div>
                        <div class="form-group mb-3">
                            <label>Password</label>
                            <input type="password" name="pass" class="form-control" autocomplete="off" placeholder="Enter your Password" required>
                            <?php if(isset($error['pass'])) echo "<small class='text-danger'>".$error['pass']."</small>"; ?>
                        </div>
                        <div class="form-group mb-3">
                            <label>Confirm Password</label>
                            <input type="password" name="con_pass" class="form-control" autocomplete="off" placeholder="Confirm your Password" required>
                            <?php if(isset($error['con_pass'])) echo "<small class='text-danger'>".$error['con_pass']."</small>"; ?>
                        </div>
                        <input type="submit" name="create" value="Create Account" class="btn btn-success w-100 mt-2">
                        <p class="text-center mt-2">Already have an account? <a href="patientlogin.php">Click Here</a></p>
                    </form>        
                </div>
            </div>
        </div>
    </div>
</body>
</html>
