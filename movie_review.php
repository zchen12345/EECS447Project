<?php
session_start();

// Connect to database
$conn = new mysqli('mysql.eecs.ku.edu', 'username', 'password', 'same as username');

if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$error = "";
$success = "";

// LOGOUT
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// LOGIN
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password FROM Users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = "User not found.";
    } else {
        $row = $result->fetch_assoc();
        if ($password == $row['password']) { // switch to password_verify() once passwords are hashed
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $username;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Incorrect password.";
        }
    }
}

// CREATE ACCOUNT
if (isset($_POST['create_user'])) {
    $username = $_POST['new_username'];
    $email    = $_POST['new_email'];
    $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO Users (email, password, username) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $password, $username);

    if ($stmt->execute()) {
        $success = "Account created! You can now log in.";
    } else {
        $error = $stmt->errno == 1062 ? "Username or email already exists." : "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Movie Review</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { border-collapse: collapse; width: 80%; margin-bottom: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; }
        th { background-color: #f2f2f2; }
        .tabs { border-bottom: 1px solid #ccc; margin-bottom: 15px; }
        .tabs button {
            background: #eee; border: none; cursor: pointer;
            padding: 10px 15px; font-size: 16px;
        }
        .tabs button.active { background: #ccc; font-weight: bold; }
        .tabcontent { display: none; }

        /* Login styles */
        .login-container { width: 350px; margin: 60px auto; }
        .login-container h2 { text-align: center; }
        .login-container input[type=text],
        .login-container input[type=email],
        .login-container input[type=password] {
            width: 100%; padding: 8px; margin: 6px 0 14px; box-sizing: border-box;
        }
        .login-container input[type=submit] { width: 100%; padding: 10px; cursor: pointer; }
        .login-tabs { display: flex; margin-bottom: 20px; }
        .login-tabs button {
            flex: 1; padding: 10px; font-size: 15px;
            cursor: pointer; background: #eee; border: 1px solid #ccc;
        }
        .login-tabs button.active { background: #ccc; font-weight: bold; }
        .error { color: red; }
        .success { color: green; }
    </style>
    <script>
        function openTab(evt, tabName) {
            document.querySelectorAll('.tabcontent').forEach(t => t.style.display = 'none');
            document.querySelectorAll('.tabs button').forEach(b => b.classList.remove('active'));
            document.getElementById(tabName).style.display = 'block';
            evt.currentTarget.classList.add('active');
        }
        function openLoginTab(evt, tabName) {
            document.querySelectorAll('.login-tabcontent').forEach(t => t.style.display = 'none');
            document.querySelectorAll('.login-tabs button').forEach(b => b.classList.remove('active'));
            document.getElementById(tabName).style.display = 'block';
            evt.currentTarget.classList.add('active');
        }
        window.onload = function () {
            <?php if (isset($_SESSION['user_id'])): ?>
                // Main app tab
                const defaultTab = "<?php echo isset($_POST['active_tab']) ? $_POST['active_tab'] : 'movies'; ?>";
                const btn = document.querySelector(`[onclick*="openTab"][onclick*="${defaultTab}"]`);
                if (btn) btn.click();
            <?php else: ?>
                // Login tab - default to login, switch to register if account just created
                const defaultLogin = <?php echo ($success ? "'register'" : "'loginTab'"); ?>;
                const loginBtn = document.querySelector(`[onclick*="openLoginTab"][onclick*="${defaultLogin}"]`);
                if (loginBtn) loginBtn.click();
            <?php endif; ?>
        };
    </script>
</head>
<body>

<?php if (!isset($_SESSION['user_id'])): ?>
<!-- ==================== LOGIN SCREEN ==================== -->
<div class="login-container">
    <h2>Movie Review System</h2>

    <?php if ($error) echo "<p class='error'>$error</p>"; ?>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>

    <div class="login-tabs">
        <button onclick="openLoginTab(event, 'loginTab')">Log In</button>
        <button onclick="openLoginTab(event, 'registerTab')">Create Account</button>
    </div>

    <div id="loginTab" class="login-tabcontent">
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <input type="submit" name="login" value="Log In">
        </form>
    </div>

    <div id="registerTab" class="login-tabcontent">
        <form method="POST">
            <label>Username</label>
            <input type="text" name="new_username" required>
            <label>Email</label>
            <input type="email" name="new_email" required>
            <label>Password</label>
            <input type="password" name="new_password" required>
            <input type="submit" name="create_user" value="Create Account">
        </form>
    </div>
</div>

<?php else: ?>
<!-- ==================== MAIN APP ==================== -->

<h1>Movie Review System</h1>
<p>Welcome, <strong><?php echo $_SESSION['username']; ?></strong>!
    <form method="POST" style="display:inline;">
        <input type="submit" name="logout" value="Log Out">
    </form>
</p>

<div class="tabs">
    <button class="tablinks" onclick="openTab(event, 'movies')">Movies</button>
    <button class="tablinks" onclick="openTab(event, 'searchReviews')">Search Reviews</button>
    <button class="tablinks" onclick="openTab(event, 'allReviews')">View All Reviews</button>
    <button class="tablinks" onclick="openTab(event, 'writeReviews')">Write a Review</button>
</div>

<!-- MOVIES TAB -->
<div id="movies" class="tabcontent">

<h2>Search Movie</h2>
<form method="POST">
    <input type="text" name="title" placeholder="Enter movie title">
    <input type="submit" name="search" value="Search">
    <input type="hidden" name="active_tab" value="movies">
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
    <input type="hidden" name="active_tab" value="movies">
</form>

<hr>

<?php
//CREATE USER
if (isset($_POST['create_user']) &&
    !empty($_POST['username']) &&
    !empty($_POST['email']) &&
    !empty($_POST['password'])) {

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Insert WITHOUT user_id
    $stmt = $conn->prepare("
        INSERT INTO Users (email, password, username)
        VALUES (?, ?, ?)
    ");
    if (!$stmt) die("Prepare failed: " . $conn->error);

    $stmt->bind_param("sss", $email, $password, $username);

if ($stmt->execute()) {
    $new_id = $conn->insert_id;
    echo "<p style='color:green;'>User created! ID: $new_id</p>";
} else {
    if ($stmt->errno == 1062) {
        echo "<p style='color:red;'>Username or email already exists.</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }
}
}
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
</div>

<!-- SEARCH REVIEWS TAB -->
<div id="searchReviews" class="tabcontent">
<h2>Search Reviews</h2>
<form method="POST">
    Movie Title:
    <input type="text" name="review_movie"><br><br>
    Username:
    <input type="text" name="review_user"><br><br>
    Minimum Rating:
    <input type="number" name="review_rating" min="1" max="5"><br><br>
    <input type="submit" name="search_review" value="Search Reviews">
    <input type="hidden" name="active_tab" value="searchReviews">
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
</div>

<!-- ALL REVIEWS TAB -->
<div id="allReviews" class="tabcontent">
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
?>
</div>

<!-- WRITE REVIEW TAB -->
<div id="writeReviews" class="tabcontent">
    <h2>Write a Review</h2>
    <form method="POST">
        <!-- Username is pre-filled from session now! -->
        Movie Title: <input type="text" name="write_movie"><br><br>
        Rating: <input type="number" name="write_rating" min="1" max="5"><br><br>
        Comment: <input type="text" name="write_comment"><br><br>
        <input type="submit" name="write_review" value="Write a Review">
        <input type="hidden" name="active_tab" value="writeReviews">
    </form>

    <?php
    if (isset($_POST['write_review'])) {
        $user_id   = $_SESSION['user_id']; // pulled from session instead of form input
        $movieTitle = $_POST['write_movie'];
        $rating    = $_POST['write_rating'];
        $comment   = $_POST['write_comment'];

        $stmt = $conn->prepare("SELECT movie_id FROM Movie WHERE LOWER(title) = LOWER(?)");
        $stmt->bind_param("s", $movieTitle);
        $stmt->execute();
        $movieResult = $stmt->get_result();

        if ($movieResult->num_rows == 0) {
            echo "<p class='error'>Movie not found.</p>";
        } else {
            $movie_id = $movieResult->fetch_assoc()['movie_id'];
            $stmt = $conn->prepare("INSERT INTO Reviews (user_id, movie_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $user_id, $movie_id, $rating, $comment);
            echo $stmt->execute()
                ? "<p class='success'>Review added!</p>"
                : "<p class='error'>Error adding review.</p>";
        }
    }
    ?>
</div>

<?php endif; ?>

<?php $conn->close(); ?>
</body>
</html>