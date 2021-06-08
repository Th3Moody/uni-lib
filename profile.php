<?php
////////////////////////////////////
///      User Profile Page       ///
////////////////////////////////////
session_start();
$badMessage = "";   // To Display error messages
$goodMessage = "";  // To Display correctness messages

include("database-connect.php"); // Connecting to the database
if (!isset($_SESSION['email']) || !isset($_COOKIE['email'])) { // Making sure the user is signed in
    $warning = "You need to login to access this page";
    header("Location: index.php?warning=" . $warning);
    exit();
} elseif ($_SESSION['email'] != $_COOKIE['email']) { // Making sure the user didn't alter the cookies
    $warning = "Please confirm your identity";
    header("Location: index.php?warning=" . $warning);
    exit();
} elseif (isset($_POST['updateProfile'])&&isset($_POST['fname'])&&isset($_POST['lname'])&&isset($_POST['pass1'])&&isset($_POST['pass2'])&&isset($_POST['oldpassword'])&&isset($_POST['email'])) { // If a request was sent to edit user profile
    // Server Validation
    // Validate First Name : It should contain only letters and whitespaces
    if (preg_match("/^[a-zA-Z ]*$/", $_POST['fname']) && !empty($_POST['fname'])) {
        $myQuery = "update users set fname='" . $_POST['fname'] . "' where email like '" . $_SESSION['email'] . "';";
        if (mysqli_query($connect, $myQuery)) {
            $goodMessage .= "First Name updated successfully<br>";
            $_SESSION['name']=$_POST['fname'];
            setcookie('name',$_POST['fname'],time()+(3600*24));
        } else {
            $badMessage .= "Couldn't update first name<br>";
        }
    } elseif (!empty($_POST['fname'])) {
        $badMessage .= "Couldn't update first name<br>";
    }

    // Validate Last Name : It should contain only letters and whitespaces
    if (preg_match("/^[a-zA-Z ]*$/", $_POST['lname']) && !empty($_POST['lname'])) {
        $myQuery = "update users set lname='" . $_POST['lname'] . "' where email like '" . $_SESSION['email'] . "';";
        if (mysqli_query($connect, $myQuery)) {
            $goodMessage .= "Last Name updated successfully<br>";
        } else {
            $badMessage .= "Couldn't update Last name<br>";
        }
    } elseif (!empty($_POST['lname'])) {
        $badMessage .= "Couldn't update Last name<br>";
    }

    // Validating Password : It should be at least 8 characters and contain at least one letter and one number
    if (!empty($_POST['oldpassword']) && !empty($_POST['pass1']) && !empty($_POST['pass2'])) {
        if ($_POST['pass1'] != $_POST['pass2']) { // Password must equal confirmation password
            $badMessage .= "<strong>Error:</strong> Passwords don't match<br>";
        } elseif (strlen($_POST['pass1']) < 8) { // Password must be at least 8 characters
            $badMessage .= "<strong>Error:</strong> Password must be at least 8 characters<br>";
        } elseif ((!preg_match("#[A-Z]+#", $_POST['pass1']) && !preg_match("#[a-z]+#", $_POST['pass1'])) || !preg_match("#[0-9]+#", $_POST['pass1'])) { // Password must contain at least 1 number and 1 letter
            $badMessage .= "<strong>Error:</strong> Password must contain at least 1 number and 1 letter<br>";
        } else {
            $myQuery = "select pass from users where email like '" . $_POST['email'] . "'";
            $result = mysqli_query($connect, $myQuery);
            $formated_result = mysqli_fetch_assoc($result);
            if (!empty($formated_result)) {
                if (strcmp($formated_result['pass'], $_POST['oldpassword']) == 0) { // If the given old password matches the original password
                    $myQuery = "update users set pass='" . $_POST['pass1'] . "' where email like '" . $_SESSION['email'] . "';";
                    if (mysqli_query($connect, $myQuery)) {
                        $goodMessage .= "Password updated successfully<br>";
                    } else {
                        $badMessage .= "Couldn't update password<br>";
                    }
                }
            } else { // If the given old password wasn't correct
                $badMessage .= "Didn't update password, you provided wrong old password<br>";
            }
        }
    } elseif (!empty($_POST['pass1']) || !empty($_POST['pass2'])) {
        $badMessage .= "<strong>Error:</strong> You must provide old password<br>";
    }

    // Validating Email : Must match email format
    if (preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $_POST['email']) && !empty($_POST['email'])) {
        $myQuery = "select email from users where email like '" . $_POST['email'] . "'";
        $result = mysqli_query($connect, $myQuery);
        $formated_result = mysqli_fetch_assoc($result);
        if (!empty($formated_result)) { // If provided email is already associated with another account
            if(!$_POST['email'] === $_SESSION['email']){
                $badMessage .= "Didn't change email, an account already exists with email (" . $_POST['email'] . ")<br>";
            }
        } else { // Changing email
            $myQuery = "update users set email='" . $_POST['email'] . "' where email like '" . $_SESSION['email'] . "';";
            if (mysqli_query($connect, $myQuery)) { // Updating sessions and cookies
                $goodMessage .= "Email changed successfully<br>";
                $_SESSION['email']=$_POST['email'];
                setcookie('email',$_POST['email'],time()+(3600*24));
            } else {
                $badMessage .= "Couldn't change email <br>".mysqli_error($connect);
            }
        }
        mysqli_free_result($result);
    }


}
// Query to retrieve user data
$myQuery = "select * from users where email like '" . $_SESSION['email'] . "';";
$result = mysqli_query($connect, $myQuery) or die("Couldn't fetch user data: " . mysqli_error($connect));
$row = mysqli_fetch_assoc($result);
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>My Profile - University Library</title>
    <link type="image/png" href="css/images/physics.png" rel="icon">
    <link type="text/css" href="fonts/fonts.css" rel="stylesheet">
    <link type="text/css" href="css/style2.css" rel="stylesheet">
    <meta charset="UTF-8">
    <!-- Client side input validation using javascript -->
    <script language="JavaScript">
        // Enable submit button if an input was changed
        function enableSubmit() {
            document.getElementById("submitButton").disabled = false;
        }
        // Validate user input
        function validateForm() {
            // Getting data from the form fields
            var fname = document.forms["updateProfile"]["fname"].value;
            var lname = document.forms["updateProfile"]["lname"].value;
            var email = document.forms["updateProfile"]["email"].value;
            var oldpassowrd = document.forms["updateProfile"]["oldpassword"].value;
            var newpassowrd = document.forms["updateProfile"]["pass1"].value;
            var cnewpassowrd = document.forms["updateProfile"]["pass2"].value;

            var letters = /^[A-Za-z ]+$/;
            // Validate first name
            if (!fname.match(letters) && !fname == "") {
                alert("First Name: Please enter letters only.")
                return false;
            }
            // Validate last name
            if (!lname.match(letters) && !lname == "") {
                alert("Last Name: Please enter letters only.")
                return false;
            }
            // Validating Password
            if (oldpassowrd != "" && newpassowrd != "" && cnewpassowrd != "") {
                if (newpassowrd != cnewpassowrd) {
                    alert("Password: New password and confirmation password don't match")
                    return false;
                } else if (newpassowrd.length < 8) {
                    alert("Password: New password should be at least 8 characters")
                    return false;
                } else if ((!newpassowrd.match(/[A-Z]+/) && !newpassowrd.match(/[a-z]+/)) || !newpassowrd.match(/[0-9]+/)) {
                    alert("Password: At least 1 letter and 1 number is needed")
                    return false;
                }
            } else if (oldpassowrd == "" && (newpassowrd != "" || cnewpassowrd != "")) {
                alert("Password: Enter old password first to change password")
                return false;
            }
            // Validating Email
            if (!email.match(/([\w\-]+\@[\w\-]+\.[\w\-]+)/)) {
                alert("Email: Please enter a valid email")
                return false;
            }
        }
    </script>
</head>
<body>
<header>
    <!-- Logo and Icon -->
    <div id="header-c1">
        <img src="css/images/physics.png">
        <h1><a href="<?php if ($_SESSION['role'] == "admin") {
                echo "adminmain.php";
            } else {
                echo "studentmain.php";
            } ?>">University Library</a></h1>
    </div>
    <!-- Menu Options-->
    <div id="header-c2">
        <?php if ($_SESSION['role'] == "admin") {
            echo "<a href=\"addbook.php\">Add Book</a>";
        } ?>
        <a href="profile.php">View Profile</a>
        <a href="index.php?logout=1">logout</a>
    </div>
</header>
<!-- Displaying form containing user data by default -->
<div id="profile-info">
    <h2>Hi, <?php echo $_COOKIE['name']; ?></h2>
    <p>You are <?php if ($row['role'] == "admin") {
            echo "an Admin";
        } else {
            echo "a Student";
        } ?></p>
    <form action="" method="post" name="updateProfile" class="profilefrom" onsubmit="return validateForm()">
        <?php
        if($goodMessage!=""){
            echo "<div id='message-green'>".$goodMessage."</div>";
        }
        if($badMessage!=""){
            echo "<div id='message-red'>".$badMessage."</div>";
        }
        ?>
        <label>First Name</label>
        <input type="text" name="fname" value="<?php echo $row['fname']; ?>" onchange="enableSubmit()">
        <label>Last Name</label>
        <input type="text" name="lname" value="<?php echo $row['lname']; ?>" onchange="enableSubmit()">
        <label>Email</label>
        <input type="email" name="email" value="<?php echo $row['email']; ?>" onchange="enableSubmit()">
        <hr>
        <label>Change Password</label>
        <input type="password" name="oldpassword" onchange="enableSubmit()" placeholder="Enter old password">
        <input type="password" name="pass1" onchange="enableSubmit()" placeholder="Enter new password">
        <input type="password" name="pass2" onchange="enableSubmit()" placeholder="Confirm new password">
        <input type="submit" id="submitButton" name="updateProfile" value="Update Profile" disabled="disabled">
    </form>
</div>
</body>
</html>
<?php
// Free result memory and closing database connection
mysqli_free_result($result);
mysqli_close($connect);
?>