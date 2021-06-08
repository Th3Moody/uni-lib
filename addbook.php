<?php
////////////////////////////////////
///         Add Book Page        ///
////////////////////////////////////
session_start();
include("database-connect.php"); // Connecting to the database
$messageForUser = null;   // Holds messages to be shown for the admin
$messageColor = null;     // Holds message Color
if (!isset($_SESSION['email']) || !isset($_COOKIE['email'])) {  // Making sure the user is signed in
    $warning = "You need to login as an admin to access this page";
    header("Location: index.php?warning=" . $warning);
    exit();
} elseif ($_SESSION['email'] != $_COOKIE['email']) { // Making sure the user didn't alter the cookies
    $warning = "Please confirm your identity";
    header("Location: index.php?warning=" . $warning);
    exit();
} elseif ($_SESSION['role'] != "admin") { // Making sure the user is an admin
    $warning = "You need to login as an admin to access this page";
    header("Location: index.php?warning=" . $warning);
    exit();
} elseif (isset($_POST['addthebook'])) { // If data was sent to this page using POST method
    // Adding a new Book
    if (isset($_POST['bookName']) && isset($_POST['author']) && isset($_POST['pubYear']) && isset($_POST['copies'])) { // Making sure all needed data was sent
        $name = $_POST['bookName'];
        $author = $_POST['author'];
        $pubYear = $_POST['pubYear'];
        $copies = $_POST['copies'];
        $bdesc = null;
        if (isset($_POST['bdesc'])) { // As description could be empty
            $bdesc = $_POST['bdesc'];
        }
        $addbook = true; // Will change to false if any validation fails
        if (!preg_match("/^[A-Za-z \.]+$/", $author)) { // Validating author name: Should contain dots, letters, and whitespaces only
            $messageForUser = "Author Name: Please enter letters only.";
            $messageColor = "red";
            $addbook = false;
        } elseif (!preg_match("/^[0-9]+$/", $pubYear) || strlen($pubYear) > 4) { // Validating Publication Year: Should be exactly four integers
            $messageForUser = "Publish Year: Please enter 4 numbers only.";
            $messageColor = "red";
            $addbook = false;
        } elseif (!preg_match("/^[0-9]+$/", $copies)) { // Validating copies: Should be an integer
            $messageForUser = "Copies: Please enter positive numbers only.";
            $messageColor = "red";
            $addbook = false;
        }
        if ($addbook) { // If all fields were valid, Add the book to the books table
            $myQuery = "insert into books(name,pubYear,author,bdesc,copies)values('" . $name . "'," . $pubYear . ",'" . $author . "','" . $bdesc . "'," . $copies . ");";
            if (mysqli_query($connect, $myQuery)) {
                $messageForUser = $name . " was added successfully";
                $messageColor = "green";
            } else { // If an error occurred
                $messageForUser = "An error occurred: Please contact the admin";
                $messageColor = "red";
            }
        }
    } else { // If not all the needed data was sent
        $messageForUser = "Please make sure you filled all fields";
        $messageColor = "red";
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Add a Book - University Library</title>
    <link type="image/png" href="css/images/physics.png" rel="icon">
    <link type="text/css" href="fonts/fonts.css" rel="stylesheet">
    <link type="text/css" href="css/style2.css" rel="stylesheet">
    <meta charset="UTF-8">
    <!-- Client side input validation using javascript for adding a new book -->
    <script language="JavaScript">
        function validateForm() {
            // Getting data from the form fields
            var bookName = document.forms["addbook"]["bookName"].value;
            var author = document.forms["addbook"]["author"].value;
            var year = document.forms["addbook"]["pubYear"].value;
            var copies = document.forms["addbook"]["copies"].value;
            // Validate empty fields
            if (bookName == "" || author == "" || year == "" || copies == "") {
                alert("Make sure you filled all required fields!")
                return false;
            }
            var letters = /^[A-Za-z \.]+$/;
            // Validate author name
            if (!author.match(letters)) { // Validating author name: Should contain dots, letters, and whitespaces only
                alert("Author Name: Please enter letters only.")
                return false;
            }
            letters = /^[0-9]+$/;
            // Validate publish year
            if (!year.match(letters) || year.length > 4) { // Validating Publication Year: Should be exactly four integers
                alert("Publish Year: Please enter 4 numbers only.")
                return false;
            }
            // Validate copies
            if (!copies.match(letters)) { // Validating copies: Should be an integer
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
    <h2>Add Book</h2>
    <!-- Form to add the book -->
    <form id="addbook" name="addbook" action="" onsubmit="return validateForm()" method="post">
        <?php
        if (isset($messageForUser)) {
            echo "<p id='message-" . $messageColor . "'>" . $messageForUser . "</p>";
        }
        ?>
        <label>Book Name</label><br>
        <input type="text" name="bookName" required=""><br>
        <label>Author Name</label><br>
        <input type="text" name="author" required=""><br>
        <label>Publish Year</label><br>
        <input type="text" name="pubYear" required=""><br>
        <label>Copies available: </label>
        <input type="number" name="copies" required=""><br>
        <label>Book Description:</label><br>
        <textarea name="bdesc" required="" style="width: 100%">Provide a description</textarea><br>
        <input type="submit" name="addthebook" value="Add Book">
    </form>
</div>
</body>
</html>
<?php
// Closing database connection
mysqli_close($connect);
?>
