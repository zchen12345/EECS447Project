<?php
session_start();

// Connect to database
$conn = new mysqli('mysql.eecs.ku.edu', 'username', 'password', 'same as username');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Movie Review</title>
    <style>
        body {
            font-family: Arial;
            margin: 20px;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>

<h1>Movie Review System</h1>

<!-- Search Movie -->
<h2>Search Movie</h2>
<form method="POST">
    <input type="text" name="title" placeholder="Enter movie title">
    <input type="submit" name="search" value="Search">
</form>

<!-- Filter by Genre -->
<h2>Filter by Genre</h2>
<form method="POST">
    <select name="genre">
        <option value="">--Select Genre--</option>
        <option value="Action">Action</option>
        <option value="Comedy">Comedy</option>
        <option value="Drama">Drama</option>
        <option value="Horror">Horror</option>
        <option value="Sci-Fi">Sci-Fi</option>
    </select>
    <input type="submit" name="filter" value="Filter">
</form>

<hr>

<?php
// SEARCH MOVIE
if (isset($_POST['search']) && !empty($_POST['title'])) {
    $title = "%" . $_POST['title'] . "%";

    $stmt = $conn->prepare("SELECT movie_id, title FROM Movie WHERE title LIKE ?");
    if (!$stmt) die("Prepare failed: " . $conn->error);

    $stmt->bind_param("s", $title);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Search Results</h2>";
}

// FILTER BY GENRE (JOIN)
else if (isset($_POST['filter']) && !empty($_POST['genre'])) {
    $genre = $_POST['genre'];

    $stmt = $conn->prepare("
        SELECT M.movie_id, M.title, G.name AS genre
        FROM Movie M
        JOIN Genre G ON M.genre_id = G.genre_id
        WHERE G.name = ?
    ");
    if (!$stmt) die("Prepare failed: " . $conn->error);

    $stmt->bind_param("s", $genre);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Filtered Movies</h2>";
}

// DEFAULT
else {
    $result = $conn->query("SELECT movie_id, title FROM Movie");
    if (!$result) die("Query failed: " . $conn->error);

    echo "<h2>All Movies</h2>";
}

// DISPLAY MOVIES
echo "<table>";
echo "<tr><th>ID</th><th>Title</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['movie_id']}</td>";
    echo "<td>{$row['title']}</td>";
    echo "</tr>";
}
echo "</table>";
?>

<hr>

<!-- Search Reviews -->
<h2>Search Reviews</h2>
<form method="POST">
    Movie Title:
    <input type="text" name="review_movie"><br><br>
    Username:
    <input type="text" name="review_user"><br><br>
    Minimum Rating:
    <input type="number" name="review_rating" min="1" max="5"><br><br>
    <input type="submit" name="search_review" value="Search Reviews">
</form>

<br>

<?php
if (isset($_POST['search_review'])) {

    $conditions = [];
    $params = [];
    $types = "";

    $query = "
        SELECT U.username, M.title, R.rating, R.comment
        FROM Reviews R
        JOIN Users U ON R.user_id = U.user_id
        JOIN Movie M ON R.movie_id = M.movie_id
        WHERE 1=1
    ";

    if (!empty($_POST['review_movie'])) {
        $conditions[] = "M.title LIKE ?";
        $params[] = "%" . $_POST['review_movie'] . "%";
        $types .= "s";
    }

    if (!empty($_POST['review_user'])) {
        $conditions[] = "U.username LIKE ?";
        $params[] = "%" . $_POST['review_user'] . "%";
        $types .= "s";
    }

    if (!empty($_POST['review_rating'])) {
        $conditions[] = "R.rating >= ?";
        $params[] = $_POST['review_rating'];
        $types .= "i";
    }

    if (count($conditions) > 0) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $stmt = $conn->prepare($query);
    if (!$stmt) die("Prepare failed: " . $conn->error);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h2>Review Search Results</h2>";

    echo "<table>";
    echo "<tr><th>User</th><th>Movie</th><th>Rating</th><th>Comment</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['rating']}</td>";
        echo "<td>{$row['comment']}</td>";
        echo "</tr>";
    }

    echo "</table>";
}
?>

<hr>

<!-- View All Reviews -->
<h2>All Reviews</h2>

<?php
$result = $conn->query("
    SELECT U.username, M.title, R.rating, R.comment
    FROM Reviews R
    JOIN Users U ON R.user_id = U.user_id
    JOIN Movie M ON R.movie_id = M.movie_id
");

if (!$result) die("Query failed: " . $conn->error);

echo "<table>";
echo "<tr><th>User</th><th>Movie</th><th>Rating</th><th>Comment</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['username']}</td>";
    echo "<td>{$row['title']}</td>";
    echo "<td>{$row['rating']}</td>";
    echo "<td>{$row['comment']}</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>

</body>
</html>