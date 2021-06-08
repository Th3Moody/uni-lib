<?php
////////////////////////////////////
///        Admin Home Page       ///
////////////////////////////////////
session_start();
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
}
include("database-connect.php"); // Connecting to the database
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Home - University Library</title>
    <link type="image/png" href="css/images/physics.png" rel="icon">
    <link type="text/css" href="fonts/fonts.css" rel="stylesheet">
    <link type="text/css" href="css/style2.css" rel="stylesheet">
    <meta charset="UTF-8">
    <!-- Filter books by Ajax -->
    <script language="JavaScript">
        // Function to be called when clicking on any filter
        function filter_books() {
            document.getElementById("books-container").innerHTML="<div id='loading'></div>";
            // Creates an HTML object of inputs from class "year-selector"
            var year_selected = document.getElementsByClassName("year-selector");
            var years_to_filter="";
            var first=true;
            // Looping on year_selected html object to prepare years to be sent
            for(var i=0;i<year_selected.length;i++){
                // If the checkbox was checked
                if(year_selected[i].checked){
                    if(first){
                        first=false;
                        years_to_filter+=year_selected[i].value;
                    }else {
                        years_to_filter+=","+year_selected[i].value;
                    }
                }
            }
            // Creates an HTML object of inputs from class "author-selector"
            var author_selected = document.getElementsByClassName("author-selector");
            var author_to_filter="";
            first = true;
            // Looping on author_selected html object to prepare authors to be sent
            for(var i=0;i<author_selected.length;i++){
                // If the checkbox was checked
                if(author_selected[i].checked){
                    if(first){
                        first=false;
                        author_to_filter+=author_selected[i].value;
                    }else {
                        author_to_filter+=","+author_selected[i].value;
                    }
                }
            }
            // I have added this delay so you can see the loading animation :)
            var delayMS=1000; // 1000ms = 1 second
            setTimeout(function () {
                var myrequest= new XMLHttpRequest();
                myrequest.onreadystatechange = function () {
                    if(this.readyState===4 && this.status === 200)
                        document.getElementById("books-container").innerHTML=this.responseText;
                }
                var tosend="ajax-helper.php?action=1&year="+years_to_filter+"&author="+author_to_filter;
                myrequest.open("GET",tosend,true);
                myrequest.send();
            },delayMS)

        }
        //Send reminder email
        function sendemail() {
            alert("An email was sent to successfully.");
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
<!-- View borrowed books -->
<div id="my-books">
    <h2>Books Borrowed by Students</h2>
    <h3>Hi, <?php echo $_COOKIE['name']?></h3>
    <!-- View books borrowed by the students -->
    <div id="borrowed-books">
        <?php
        // Query to  get borrowed books from borrowed_books,users, and books tables
        $myQuery="select books.ISBN,name,borrow_date,return_date,users.fname,users.email from books inner join borrowed_books on books.ISBN = borrowed_books.ISBN inner join users on users.email = borrowed_books.user_email;";
        $result=mysqli_query($connect,$myQuery) or die("Error message: ".mysqli_error($connect));
        if(mysqli_num_rows($result) == 0){
            echo "<p>No books borrowed by Students</p>";
        }else{
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class=\"book\">";
                echo "<h3>" . $row['name'] . "</h3>";
                echo "<h4>Borrowed By: <span style='color: #EB3349'>" . ($row['fname']) . " (".$row['email'].")</span></h4>";
                echo "<h4>Borrowed on: " . $row['borrow_date'] . "</h4>";
                echo "<h4>Return on: " . $row['return_date'] . "</h4>";
                echo "<a href='book.php?bookID=".$row['ISBN']."'>View</a><a onclick='sendemail()'>Send Reminder Email</a></div>";
            }
        }
        ?>
    </div>
</div>
<!-- Where an admin may browse, search and filter books -->
<div id="borrow-book-container">
    <h2>Books Available</h2>
    <!-- Left side: Search and filters -->
    <div id="filters-container">
        <!-- Search Form -->
        <form action="" method="get">
            <h3>Search</h3>
            <input type="text" name="sQuery" required="" placeholder="Enter Book name or ISBN">
            <input type="submit" name="search" value="Search">
        </form>
        <!-- Year Filter -->
        <h3>Publish Year</h3>
        <?php
        // Displaying available years
        $myQuery = "select distinct pubYear from books order by pubYear;";
        $result = mysqli_query($connect, $myQuery);
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<label onclick='filter_books()'><input type='checkbox' class='year-selector' value='" . $row['pubYear'] . "'>&nbsp;&nbsp;" . $row['pubYear'] . "</label><br>";
        }
        ?>
        <!-- Author Filter -->
        <h3>Author</h3>
        <?php
        // Displaying available authors
        $myQuery = "select distinct author from books order by author;";
        $result = mysqli_query($connect, $myQuery);
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<label onclick='filter_books()'><input type='checkbox' class='author-selector' value='" . $row['author'] . "'>&nbsp;&nbsp;" . $row['author'] . "</label><br>";
        }
        ?>
    </div>
    <!-- Right side: Displaying books -->
    <div id="books-container">
        <?php
        // Displaying Books
        $myQuery = null;
        if (isset($_GET['search'])) { // If a search query was sent
            if(preg_match("/^[0-9]+$/",$_GET['sQuery'])){ // if query was numbers only
                $myQuery = "select * from books where name like '%" . $_GET['sQuery'] . "%' or ISBN like " . $_GET['sQuery'] . ";";
            }else{ // if query wasn't numbers only
                $myQuery = "select * from books where name like '%" . $_GET['sQuery'] . "%';";
            }
        } else { // If no search query was sent
            $myQuery = "select * from books";
        }
        $result = mysqli_query($connect, $myQuery);
        // Displaying available books if found
        if (mysqli_num_rows($result) == 0) {
            echo "<p style='text-align: center'>No Books were found</p>";
        } else {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class=\"book\">";
                echo "<h3>" . $row['name'] . "</h3>";
                echo "<h4>By: <span style='color: #EB3349'>" . ($row['author']) . "</span></h4>";
                echo "<h4>Available: " . ($row['copies'] - $row['borrowed']) . " copies </h4>";
                echo "<p>" . $row['bdesc'] . "</p>";
                echo "<a href='book.php?bookID=".$row['ISBN']."'>View</a></div>";
            }
        }
        mysqli_free_result($result); // Free result memory
        ?>
    </div>
</div>
</body>
</html>
<?php
// Closing Database connection
mysqli_close($connect);
?>