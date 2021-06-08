<?php
////////////////////////////////////
///         Sign-up Page         ///
////////////////////////////////////
session_start();
if(isset($_SESSION['email'])){ // If user is signed in
    header("Location: index.php");
    exit();
}
include ("database-connect.php"); // Connecting to the database
// Error message holders
$messageForUser=null;
$messageColor=null;
$errPassword=null;
$errEmail=null;
$errRole=null;
$errFName=null;
$errLName=null;
if(isset($_POST['create'])){ // If a request was sent to create and account
    if(!(empty($_POST['fname'])||empty($_POST['lname'])||empty($_POST['email'])||empty($_POST['pass1'])||empty($_POST['pass2'])||empty($_POST['role']))){
        // Validate First Name : It should contain only letters and whitespaces
        if(!preg_match("/^[a-zA-Z ]*$/",$_POST['fname'])){
            $errFName= "<strong>Error:</strong> First name must have letters and whitespaces <i>only</i>";
        }
        // Validating Last Name : It should contain only letters and whitespaces
        if(!preg_match("/^[a-zA-Z ]*$/",$_POST['lname'])){
            $errLName= "<strong>Error:</strong> Last name must have letters and whitespaces <i>only</i>";
        }
        // Validating Password : It should be at least 8 characters and contain at least one letter and one number
        if($_POST['pass1']!=$_POST['pass2']){ // Password must equal confirmation password
            $errPassword= "<strong>Error:</strong> Passwords don't match";
        }elseif (strlen($_POST['pass1'])<8) { // Password must be at least 8 characters
            $errPassword = "<strong>Error:</strong> Password must be at least 8 characters";
        }elseif ((!preg_match("#[A-Z]+#",$_POST['pass1']) && !preg_match("#[a-z]+#",$_POST['pass1'])) || !preg_match("#[0-9]+#",$_POST['pass1'])){ // Password must contain at least 1 number and 1 letter
            $errPassword = "<strong>Error:</strong> Password must contain at least 1 number and 1 letter";
        }
        // Validating Email : Must match email format
        if(!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$_POST['email'])){
            $errEmail= "<strong>Error:</strong> Invalid email";
        }else{
            $myQuery="select email from users where email like '".$_POST['email']."'";
            $result=mysqli_query($connect,$myQuery);
            $formated_result=mysqli_fetch_assoc($result);
            if(!empty($formated_result)){
                $errEmail= "<strong>Error:</strong> An account already exists with email (".$_POST['email']."), <a href='index.php'>Login?</a>";
            }
            mysqli_free_result($result);
        }
        // Validating Role : Only admin and student is allowed
        if($_POST['role']!="admin" && $_POST['role']!="student"){
            $errRole="<strong>Error:</strong> Can't assign role";
        }
    }else{ // If a bad/damaged/incomplete request was sent
        $messageForUser="<strong>Error:</strong> Please make sure you entered all required data";
        $messageColor="red";
    }
    // Creating account if valid information was provided
    if($messageForUser==null & $errRole==null && $errEmail==null && $errFName==null && $errLName == null && $errPassword==null){ // (in short) if no validation errors
        $myQuery="insert into users(email,fname,lname,pass,role)values ('".$_POST['email']."','".$_POST['fname']."','".$_POST['lname']."','".$_POST['pass1']."','".$_POST['role']."')";
        if(mysqli_query($connect,$myQuery)){
            $messageForUser="Account created successfully, you can now <a href='index.php'>login</a> to your account.";
            $messageColor="green";
        }else{
            $messageForUser="Couldn't create account please contact admin: ".mysqli_error($connect);
            $messageColor="red";
        }
    }
    // Closing database connection
    mysqli_close($connect);
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Create Account - University Library</title>
    <link type="image/png" href="css/images/physics.png" rel="icon">
    <link type="text/css" href="css/style1.css" rel="stylesheet">
    <link type="text/css" href="fonts/fonts.css" rel="stylesheet">
    <meta charset="UTF-8">
</head>
<body>
<div id="sign-up">
    <h2>Create Account</h2>
    <?php
    // Display messages if needed
    if(isset($messageForUser)){
        echo"<p id='message-".$messageColor."'>".$messageForUser."</p>";
    }
    ?>
    <form id="create-login-form" action="" method="post">
        <label>First Name</label><br>
        <?php
        if(isset($errFName)){
            echo"<p id='message-red'>".$errFName."</p>";
        }
        ?>
        <input type="text" name="fname" required=""><br>
        <label>Last Name</label><br>
        <?php
        if(isset($errLName)){
            echo"<p id='message-red'>".$errLName."</p>";
        }
        ?>
        <input type="text" name="lname" required=""><br>
        <label>Email</label><br>
        <?php
        if(isset($errEmail)){
            echo"<p id='message-red'>".$errEmail."</p>";
        }
        ?>
        <input type="email" name="email" required=""><br>
        <label>Password</label><br>
        <?php
        if(isset($errPassword)){
            echo"<p id='message-red'>".$errPassword."</p>";
        }
        ?>
        <input type="password" name="pass1" required=""><br>
        <p style="color:grey;font-size: 12px;margin-top: -9px">Password must be at least 8 characters and contain at least 1 number and 1 letter.</p>
        <label>Confirm Password</label><br>
        <input type="password" name="pass2" required=""><br>
        <label>Role:</label>
        <?php
        if(isset($errRole)){
            echo"<p id='message-red'>".$errPassword."</p>";
        }
        ?>
        <input type="radio" name="role" value="student" required="" checked>
        <label for="admin">Student</label>
        <input type="radio" name="role" value="admin" required="">
        <label for="admin">Admin</label><br>
        <input type="submit" name="create" value="Create Account">
    </form>
    <p style="font-size: 12px">Already have an account? <a href="index.php">Login</a></p>
</div>
<footer>
    <p>Website by <a href="http://mahmoudhossam.tech">Mahmoud Hossam Atef</a></p>
</footer>
</body>
</html>
