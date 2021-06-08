<?php
////////////////////////////////////
///      Student Home Page       ///
////////////////////////////////////
session_start();

if (!isset($_SESSION['email']) || !isset($_COOKIE['email'])) { // Making sure the user is signed in
    $warning="You need to login as a student to access this page";
    header("Location: index.php?warning=".$warning);
    exit();
} elseif ($_SESSION['email'] != $_COOKIE['email']) { // Making sure the user didn't alter the cookies
    $warning="Please confirm your identity";
    header("Location: index.php?warning=".$warning);
    exit();
} elseif ($_SESSION['role'] != "student") { // Making sure the user is a student
    $warning="You need to login as a student to access this page";
    header("Location: index.php?warning=".$warning);
    exit();
}
include("database-connect.php"); // Connecting to the database
?>
    <html>
    <head>
        <title>Home - University Library</title>
        <link type="image/png" href="css/images/physics.png" rel="icon">
        <link type="text/css" href="fonts/fonts.css" rel="stylesheet">
        <link type="text/css" href="css/style2.css" rel="stylesheet">
        <meta charset="UTF-8">
        <!-- Filter books by Ajax -->
        <script language="JavaScript">
            // Function to be called when clicking on any filters
            function filter_books() {
                document.getElementById("books-container").innerHTML="<div id='loading'></div>";
                // Creates an HTML object of inputs of class "year-selector"
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
                // Creates an HTML object of inputs of class "author-selector"
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
        </script>
    </head>
    <body>
    <header>
        <!-- Logo and Icon -->
        <div id="header-c1">
            <img src="css/images/physics.png">
            <h1><a href="studentmain.php">University Library</a></h1>
        </div>
        <!-- Menu Options-->
        <div id="header-c2">
            <a href="profile.php">View Profile</a>
            <a href="index.php?logout=1">logout</a>
        </div>
    </header>
    <!-- View borrowed books -->
    <div id="my-books">
        <h2>My Books</h2>
        <h3>Hi, <?php echo $_COOKIE['name']?></h3>
        <!-- View books borrowed by the student -->
        <div id="borrowed-books">
            <?php
            // Query to  get borrowed books from borrowed_books table
            $myQuery="select books.ISBN,name,author,borrow_date,return_date from books inner join borrowed_books on books.ISBN = borrowed_books.ISBN  where user_email like '".$_SESSION['email']."'";
            $result=mysqli_query($connect,$myQuery) or die("Error message: ".mysqli_error($connect));
            if(mysqli_num_rows($result) == 0){
                echo "<p>You don't have any borrowed books, can borrow books below</p>";
            }else{
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class=\"book\">";
                    echo "<h3>" . $row['name'] . "</h3>";
                    echo "<h4>By: <span style='color: #EB3349'>" . ($row['author']) . "</span></h4>";
                    echo "<h4>Borrowed on: " . $row['borrow_date'] . "</h4>";
                    echo "<h4>Return on: " . $row['return_date'] . "</h4>";
                    echo "<a href='book.php?bookID=".$row['ISBN']."'>View</a><a href='book.php?bookID=".$row['ISBN']."&return=1'>Return</a><a href='book.php?bookID=".$row['ISBN']."&extend=1'>Extend Borrow</a></div>";
                }
            }
            ?>
        </div>
    </div>
    <!-- Where a student may browse, search and filter books -->
    <div id="borrow-book-container">
        <h2>Borrow a Book</h2>
        <!-- Left side: Search and filters -->
        <div id="filters-container">
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
        <!-- Right side: browse books-->
        <div id="books-container">
            <?php
            $myQuery = null;
            if (isset($_GET['search'])) { // If a search query was sent
                if(preg_match("/^[0-9]+$/",$_GET['sQuery'])){ // if query was numbers only
                    $myQuery = "select * from books where name like '%" . $_GET['sQuery'] . "%' or ISBN like " . $_GET['sQuery'] . ";";
                }else{ // if query wasn't numbers only
                    $myQuery = "select * from books where name like '%" . $_GET['sQuery'] . "%';";
                }
            } else {
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
            mysqli_free_result($result);
            ?>
        </div>
    </div>
    <footer>
        <p>Website by Mahmoud Hossam Atef (20180251) and Abdelrahman Khaled Yehia (20180142) </p>
    </footer>
    </body>
    </html>
<?php
// Closing Database connection
mysqli_close($connect);
?>