<?php
////////////////////////////////////
///           Ajax-Helper        ///
////////////////////////////////////
if (isset($_GET['action'])) { // Checks if any data was sent using GET Method
    include("database-connect.php"); // Connecting to the database
    // Composing the query for MySQL
    $myQuery = "select * from books";
    $first = true;
    if (isset($_GET['year'])) { // Applying Publication Year filters IFF needed
        if (!empty($_GET['year'])) {
            $explode_given = explode(',', $_GET['year']);
            $years = implode("','", $explode_given);
            $myQuery .= " WHERE pubYear IN ('" . $years . "')";
            $first = false;
        }
    }
    if (isset($_GET['author'])) { // Applying Author filters IFF needed
        if (!empty($_GET['author'])) {
            $explode_given = explode(',', $_GET['author']);
            $authors = implode("','", $explode_given);
            if ($first) {
                $myQuery .= " WHERE author IN ('" . $authors . "')";
            } else {
                $myQuery .= " AND author IN ('" . $authors . "')";
            }
        }
    }
    $myQuery .= ";"; // Closing the query
    // Sending the query to database
    $result = mysqli_query($connect, $myQuery) or die("Problem in sql statement: " . mysqli_error($connect));
    // Composing the response
    $send_back = "";
    if (mysqli_num_rows($result) > 0) { // If some books met the wanted filters
        while ($row = mysqli_fetch_assoc($result)) {
            $send_back .= "<div class=\"book\"><h3>" . $row['name'] . "</h3><h4>By: <span style='color: #EB3349'>" . ($row['author']) . "</span></h4><h4>Available: " . ($row['copies'] - $row['borrowed']) . " copies </h4><p>" . $row['bdesc'] . "</p><a href='book.php?bookID=" . $row['ISBN'] . "'>View</a></div>";
        }
    } else { // If no books met the wanted filters
        $send_back .= "<p style='text-align: center'>No Books were found</p>";
    }
    // Free result and Closing database connection
    mysqli_free_result($result);
    mysqli_close($connect);
    // Sending the response
    echo $send_back;
} else {
    header("Location: index.php"); // Redirects to homepage
}
?>