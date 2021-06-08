<?php
////////////////////////////////////
///       Single Book Page       ///
////////////////////////////////////
session_start();
$row=null;
$goodMessage="";
$badMessage="";
include("database-connect.php");
if (!isset($_SESSION['email']) || !isset($_COOKIE['email'])) { // Making sure the user is signed in
    $warning="You need to login to access this page";
    header("Location: index.php?warning=".$warning);
    exit();
} elseif ($_SESSION['email'] != $_COOKIE['email']) { // Making sure the user didn't alter the cookies
    $warning="Please confirm your identity";
    header("Location: index.php?warning=".$warning);
    exit();
}elseif(isset($_GET['bookID'])){ // Book ID should be sent to display book details
    $myQuery="select * from books where ISBN like ".$_GET['bookID'].";";
    $result=mysqli_query($connect,$myQuery) or die("Couldn't retrieve book information: ".mysqli_error($connect));
    if(mysqli_num_rows($result)>0){ // If book was found
        $row=mysqli_fetch_assoc($result);
        // Supporting further needed actions
        if(isset($_GET['borrow'])){ // Borrowing the book
            if($row['copies']-$row['borrowed']<1){ // If no copies available
                $badMessage.="Can't Borrow Book: No available copies<br>";
            }else{ // There is enough copies
                // Checking if the student already borrowed this book before and didn't return it
                $myQuery="select * from borrowed_books where ISBN like ".$_GET['bookID']." AND user_email LIKE '".$_SESSION['email']."';";
                if(mysqli_num_rows(mysqli_query($connect,$myQuery))>0){ // Student already borrowed the book
                    $badMessage.="Can't Borrow Book: You already borrowed a copy<br>";
                }else{ // Student can borrow the book
                    $date = new DateTime(date("Y-m-d"));
                    $date->add(new DateInterval('P10D'));
                    // Adding borrow to borrowed_books table
                    $myQuery="INSERT INTO borrowed_books(ISBN,user_email,borrow_date,return_date)VALUES(".$row['ISBN'].",'".$_SESSION['email']."','".date("Y-m-d")."','".$date->format('Y-m-d')."')";
                    if(mysqli_query($connect,$myQuery)){
                        $goodMessage.="You have borrwed the book successfully, you should return it by ".$date->format('Y-M-d')."<br>";
                        $myQuery="update books set borrowed=".($row['borrowed']+1)." WHERE ISBN =".$row['ISBN'].";";
                        mysqli_query($connect,$myQuery);
                    }else{
                        $badMessage.="Can't Borrow Book: Error occured while borrowing, please contact admin<br>";
                    }

                }
            }
            $myQuery="select * from books where ISBN like ".$_GET['bookID'].";";
            $result=mysqli_query($connect,$myQuery) or die("Couldn't retrieve book information: ".mysqli_error($connect));
            $row=mysqli_fetch_assoc($result);
        }elseif(isset($_GET['return'])){ // Returning the book
            $myQuery="select * from borrowed_books where user_email like '".$_SESSION['email']."' AND ISBN=".$row['ISBN'].";";
            if(mysqli_num_rows(mysqli_query($connect,$myQuery))>0){
                $myQuery="DELETE FROM borrowed_books WHERE user_email like '".$_SESSION['email']."' AND ISBN=".$row['ISBN'].";";
                if(mysqli_query($connect,$myQuery)){
                    $goodMessage.="Book returned successfully<br>";
                    $myQuery="update books set borrowed=".($row['borrowed']-1)." WHERE ISBN =".$row['ISBN'].";";
                    mysqli_query($connect,$myQuery);
                }else{
                    $badMessage.="Can't Return: Please contact the admin<br>";
                }
            }else{
                $badMessage.="Can't Return: You haven't borrowed this book before<br>";
            }
            $myQuery="select * from books where ISBN like ".$_GET['bookID'].";";
            $result=mysqli_query($connect,$myQuery) or die("Couldn't retrieve book information: ".mysqli_error($connect));
            $row=mysqli_fetch_assoc($result);
        }elseif (isset($_GET['extend'])){ // Extending borrow period for the book
            $myQuery="select return_date from borrowed_books where user_email like '".$_SESSION['email']."' AND ISBN=".$row['ISBN'].";";
            $result=mysqli_query($connect,$myQuery);
            if(mysqli_num_rows($result)>0){
                $selected=mysqli_fetch_assoc($result);
                $old_date=new DateTime($selected['return_date']);
                $old_date->add(new DateInterval('P5D'));
                $myQuery="UPDATE borrowed_books set return_date='".$old_date->format('Y-m-d')."' where user_email like '".$_SESSION['email']."' AND ISBN=".$row['ISBN'].";";
                if(mysqli_query($connect,$myQuery)){
                    $goodMessage.="You extended the borrow period successfully, you should return the book on ".$old_date->format('Y-M-d')."<br>";
                }else{
                    $badMessage.="Can't Extend: Please contact admin<br>";
                }
            }else{
                $badMessage.="Can't Extend: You haven't borrowed this book before<br>";
            }
        }

    }else{
        echo "Book not found";
        exit();
    }
}else{ // Redirect to home page if book ID wasn't sent
    header("Location: index.php");
    exit();
}
$bname=$row['name'];
$author=$row['author'];
$pubYear=$row['pubYear'];
$bdesc=$row['bdesc'];
$borrowed=$row['borrowed'];
$copies=$row['copies'];
$isbn=$row['ISBN'];
?>
<!DOCTYPE HTML>
<html>
<head>
    <link type="image/png" href="css/images/physics.png" rel="icon">
    <link type="text/css" href="fonts/fonts.css" rel="stylesheet">
    <link type="text/css" href="css/style2.css" rel="stylesheet">
    <meta charset="UTF-8">
    <title><?php echo $bname;?> - University Library</title>
</head>
<body>
<header>
    <!-- Logo and Icon -->
    <div id="header-c1">
        <img src="css/images/physics.png">
        <h1><a href="<?php
            if($_SESSION['role']=="admin"){
                echo "adminmain.php";
            }else{
                echo "studentmain.php";
            }
            ?>">University Library</a></h1>
    </div>
    <!-- Menu Options-->
    <div id="header-c2">
        <?php
        if($_SESSION['role']=="admin"){
            echo "<a href=\"addbook.php\">Add Book</a>";
        }
        ?>
        <a href="profile.php">View Profile</a>
        <a href="index.php?logout=1">logout</a>
    </div>
</header>
<!-- Displaying book details -->
<div id="full-book-view">
    <div style="display: block"><a href="index.php"><img src="css/images/go-back.png" alt="Go Back" style="height: 30px"></a></div>
    <h1><?php echo $bname;?></h1>
    <?php
    if($_SESSION['role']=="admin"){
        echo "<a href='editbook.php?isbn=".$isbn."'>Edit Book Details</a>";
    }
    if($goodMessage!=""){
        echo "<div id='message-green'>".$goodMessage."</div>";
    }
    if($badMessage!=""){
        echo "<div id='message-red'>".$badMessage."</div>";
    }
    ?>

    <h3>Author: <?php echo $author;?></h3>
    <h3>Publish Year: <?php echo $pubYear;?></h3>
    <h3>Number of copies available: <?php echo $copies-$borrowed;?></h3>
    <?php
    if($_SESSION['role']=="admin"){
        echo "<h3>Borrowed Copies: ".$borrowed."</h3>";
        echo "<h3>Total Copies: ".$copies."</h3>";
    }
    ?>
    <h3>Description:</h3>
    <p><?php echo $bdesc;?></p>
    <?php
    if($_SESSION['role']=="student"){ // if the viewer was a student
        $myQuery="select borrow_date,return_date from books inner join borrowed_books on books.ISBN = borrowed_books.ISBN  where user_email like '".$_SESSION['email']."' AND borrowed_books.ISBN=".$row['ISBN'].";";
        $result=mysqli_query($connect,$myQuery) or die("Error message: ".mysqli_error($connect));
        $row=mysqli_fetch_assoc($result);
        if(mysqli_num_rows($result) == 0){
            if($copies-$borrowed<1){
                echo "<a class='student-buttons-disabled'>Not Available</a>";
            }else{
                echo "<a class='student-buttons' href='?bookID=".$isbn."&borrow=1'>Borrow Book</a>";
            }
        }else{ // if the book was borrowed
            echo "<h4>Borrowed on: " . $row['borrow_date'] . "</h4>";
            echo "<h4>Return on: " . $row['return_date'] . "</h4>";
            echo "<a class='student-buttons' href='?bookID=".$isbn."&return=1'>Return</a>";
            echo "<a class='student-buttons' href='?bookID=".$isbn."&extend=1'>Extend Borrow Period</a>";
        }

    }
    ?>
</div>
</body>
</html>
<?php
// Free result from memory and closing database connection
mysqli_free_result($result);
mysqli_close($connect);
?>
