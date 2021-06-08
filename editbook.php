<?php
////////////////////////////////////
///     Edit Single Book Page    ///
////////////////////////////////////
session_start();
include("database-connect.php"); // Connecting to the database
$messageForUser=null;   // Holds messages to be shown for the admin
$messageColor = null;   // Holds message Color
if(!isset($_SESSION['email'])||!isset($_COOKIE['email'])){ // Making sure the user is signed in
    $warning="You need to login as an admin to access this page";
    header("Location: index.php?warning=".$warning);
    exit();
}elseif($_SESSION['email']!=$_COOKIE['email']){ // Making sure the user didn't alter the cookies
    $warning="Please confirm your identity";
    header("Location: index.php?warning=".$warning);
    exit();
}elseif ($_SESSION['role']!="admin"){ // Making sure the user is an admin
    $warning="You need to login as an admin to access this page";
    header("Location: index.php?warning=".$warning);
    exit();
}elseif (isset($_POST['editthebook'])){ // If an editing request was sent to server
    $myQuery = "select * from books where ISBN LIKE ".$_POST['isbn'].";";
    $result=mysqli_query($connect,$myQuery);
    if(mysqli_num_rows($result)>0){
        if(isset($_POST['bookName'])){
            if(!empty($_POST['bookName'])){
                $myQuery = "update books set name='".$_POST['bookName']."' where ISBN LIKE ".$_POST['isbn'].";";
                mysqli_query($connect,$myQuery);
            }
        }
        if(isset($_POST['author'])){
            if(!empty($_POST['author'])){
                $myQuery = "update books set author='".$_POST['author']."' where ISBN LIKE ".$_POST['isbn'].";";
                mysqli_query($connect,$myQuery);
            }
        }
        if(isset($_POST['pubYear'])){
            if(!empty($_POST['pubYear'])){
                $myQuery = "update books set pubYear='".$_POST['pubYear']."' where ISBN LIKE ".$_POST['isbn'].";";
                mysqli_query($connect,$myQuery);
            }
        }
        if(isset($_POST['copies'])){
            if(!empty($_POST['copies'])){
                $myQuery = "update books set copies='".$_POST['copies']."' where ISBN LIKE ".$_POST['isbn'].";";
                mysqli_query($connect,$myQuery);
            }
        }
        if(isset($_POST['bdesc'])){
            if(!empty($_POST['bdesc'])){
                $myQuery = "update books set bdesc='".$_POST['bdesc']."' where ISBN LIKE ".$_POST['isbn'].";";
                mysqli_query($connect,$myQuery);
            }
        }
        $myQuery = "select * from books where ISBN LIKE ".$_POST['isbn'].";";
        $result=mysqli_query($connect,$myQuery);
        $row=mysqli_fetch_assoc($result);
    }else{
        echo "Can't edit: Book not found";
        exit();
    }

} elseif (isset($_GET['isbn'])){ // Shows Book details to be edit
    $myQuery = "select * from books where ISBN LIKE ".$_GET['isbn'].";";
    $result=mysqli_query($connect,$myQuery);
    $row=null;
    if(mysqli_num_rows($result)>0){
        $row=mysqli_fetch_assoc($result);
    }else{
        echo "Can't edit: Book not found";
        exit();
    }
}else{
    echo "Can't edit: Book not found";
    exit();
}


?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Edit <?php echo $row['name']?> - University Library</title>
    <link type="image/png" href="css/images/physics.png" rel="icon">
    <link type="text/css" href="fonts/fonts.css" rel="stylesheet">
    <link type="text/css" href="css/style2.css" rel="stylesheet">
    <meta charset="UTF-8">
    <!-- Client side input validation using javascript -->
    <script language="JavaScript">
        function validateForm() {
            var author=document.forms["editbook"]["author"].value;
            var year=document.forms["editbook"]["pubYear"].value;
            var copies=document.forms["editbook"]["copies"].value;

            var letters = /^[A-Za-z ]+$/;
            // Validate author name
            if(!author.match(letters) && !author==""){
                alert("Author Name: Please enter letters only.")
                return false;
            }
            letters = /^[0-9]+$/;
            // Validate publish year
            if((!year.match(letters) || year.length>4) && !year==""){
                alert("Publish Year: Please enter 4 numbers only.")
                return false;
            }
            // Validate copies
            if(!copies.match(letters) &&!copies==""){
                alert("Copies: Please enter positive numbers only.")
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
        <h1><a href="adminmain.php">University Library</a></h1>
    </div>
    <!-- Menu Options-->
    <div id="header-c2">
        <a href="addbook.php">Add Book</a>
        <a href="profile.php">View Profile</a>
        <a href="index.php?logout=1">logout</a>
    </div>
</header>
<div id="addbook-container">
    <h2>Editing "<?php echo $row['name']?>"</h2>
    <form id = "addbook" name="editbook" action="" onsubmit="return validateForm()" method="post">
        <?php
        if(isset($messageForUser)){
            echo"<p id='message-".$messageColor."'>".$messageForUser."</p>";
        }
        ?>
        <input type="hidden" name="isbn" value="<?php echo $row['ISBN']?>">
        <label>Book Name</label><br>
        <input type="text" name="bookName" value="<?php echo $row['name']?>"><br>
        <label>Author Name</label><br>
        <input type="text" name="author" value="<?php echo $row['author']?>"><br>
        <label>Publish Year</label><br>
        <input type="text" name="pubYear" value="<?php echo $row['pubYear']?>"><br>
        <label>Copies available: </label>
        <input type="number" name="copies" value="<?php echo $row['copies']?>"><br>
        <label>Book Description:</label><br>
        <textarea name="bdesc" style="width: 100%" rows="15"><?php echo $row['bdesc']?></textarea><br>
        <input type="submit" name="editthebook" value="Edit Book">
    </form>
</div>
</body>
</html>
<?php
mysqli_close($connect);
?>
