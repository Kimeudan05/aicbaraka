<?php
// Start the session
session_start();

// Enable error reporting for development (optional, remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$servername = "localhost:3308";  // Ensure this port matches your MySQL configuration
$username = "root";
$password = "";
$dbname = "youth_ministry_db";

// Create the database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize inputs for general use
function sanitize_input($data) {
    global $conn;
    $data = trim($data);               // Remove extra spaces
    $data = stripslashes($data);       // Remove backslashes
    $data = htmlspecialchars($data);   // Convert special characters to HTML entities
    return $conn->real_escape_string($data); // Escape special characters for use in SQL
}

// Example function for adding a youth (use prepared statements to prevent SQL injection)
function add_youth($firstname, $lastname, $email, $phone, $password, $profile_pic = null) {
    global $conn;
    
    // Hash the password securely
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare the SQL statement to insert the youth data
    $stmt = $conn->prepare("INSERT INTO youths (name, email, phone, password, profile_pic) VALUES (?, ?, ?, ?, ?)");
    
    // Join the first and last names
    $fullname = $firstname . " " . $lastname;
    
    // Bind the parameters to the SQL query
    $stmt->bind_param("sssss", $fullname, $email, $phone, $hashed_password, $profile_pic);
    
    // Execute the query and check for success
    if ($stmt->execute()) {
        echo "Youth added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Example function for user login (again, using prepared statements)
function login_youth($email, $password) {
    global $conn;
    
    // Prepare the SQL query to fetch the user's data by email
    $stmt = $conn->prepare("SELECT id, name, password FROM youths WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    // If a user is found with the given email
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();
        
        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Start the session and store user data
            $_SESSION['youth_id'] = $id;
            $_SESSION['youth_name'] = $name;
            echo "Login successful!";
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with this email!";
    }

    // Close the statement
    $stmt->close();
}

// Close the connection when done
// $conn->close();   // Uncomment this when you're done using the connection
?>
