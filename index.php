<?php
////////////////////////////////////
///         Login Page           ///
////////////////////////////////////
session_start();
$messageForUser=null;
$messageColor=null;
if(isset($_GET['logout'])){ // If a request to logout
    session_destroy();
    session_start();
    setcookie('name',null,time());
    setcookie('email',null,time());
    $messageForUser="You logged out successfully!";
    $messageColor="green";
}elseif (isset($_GET['warning'])){ // Display a waring, usually used by other pages
    session_destroy();
    session_start();
    setcookie('name',null,time());
    setcookie('email',null,time());
    $messageForUser=$_GET['warning'];
    $messageColor="red";
}
elseif (isset($_SESSION['email'])){ // If the user logged in already
    if(isset($_SESSION['role'])){
        if($_SESSION['role']=="admin"){
            header("Location: adminmain.php");
            exit();
        }elseif ($_SESSION['role']=="student"){
            header("Location: studentmain.php");
            exit();
        }else{
            $warning="An error occurred, please login again.";
            header("Location: index.php?warning=".$warning);
            exit();
        }
    }else{
        $warning="An error occurred, please login again.";
        header("Location: index.php?warning=".$warning);
        exit();
    }
}
include ("database-connect.php"); // Connecting to the database
if(isset($_POST["login"])){ // Request to login
    if(!empty($_POST['useremail'])&&!empty($_POST['password'])){
        // Checking email and password
        $myQuery="select pass,fname,role from users where email like '".$_POST['useremail']."';";
        $result=mysqli_query($connect,$myQuery);
        $answer=mysqli_fetch_assoc($result);
        if(isset($answer)){ // If the user was found
            if(strcmp($answer['pass'],$_POST['password'])==0){ // If password is correct
                $_SESSION['email']=$_POST['useremail'];
                $_SESSION['name']=$answer['fname'];
                $_SESSION['role']=$answer['role'];
                setcookie('name',$answer['fname'],time()+(3600*24));
                setcookie('email',$_POST['useremail'],time()+(3600*24));
                if($answer['role']=="student"){
                    header("Location: studentmain.php");
                }else{
                    header("Location: adminmain.php");
                }
                exit();
            }else{
                $messageForUser="<strong>Error:</strong> Incorrect email or password";
                $messageColor="red";
            }
        }else{ // If no user was found associated with the provided email
            $messageForUser="<strong>Error:</strong> Incorrect email or password";
            $messageColor="red";
        }
        mysqli_free_result($result);
    }else{ // If bad request
        $messageForUser="<strong>Error:</strong> Incorrect email or password";
        $messageColor="red";
    }
}
?>
<html>
<head>
    <title>Login - University Library</title>
    <link type="image/png" href="css/images/physics.png" rel="icon">
    <link type="text/css" href="css/style1.css" rel="stylesheet">
    <link type="text/css" href="fonts/fonts.css" rel="stylesheet">
    <meta charset="UTF-8">
</head>
<body>
<div id="login">
    <h2>Login</h2>
    <?php
    // View messages if needed
    if(isset($messageForUser)){
            echo"<p id='message-".$messageColor."'>".$messageForUser."</p>";
    }
    ?>
    <!-- Login Form -->
    <form id="create-login-form" action="" method="post">
        <label>Email</label><br>
        <input type="email" name="useremail" required=""><br>
        <label>Password</label><br>
        <input type="password" name="password" required=""><br>
        <input type="submit" name="login" value="Login">
    </form>
    <p style="font-size: 12px">Don't have an account yet? <a href="sign-up.php">Sign up now!</a></p>
</div>
<footer>
    <p>Website by <a href="http://mahmoudhossam.tech">Mahmoud Hossam Atef</a></p>
</footer>
</body>
</html>

<?php
// Closing database connection
mysqli_close($connect)
?>


