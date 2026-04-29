<?php
session_start();

// Include the database connection file
include '../Database.php';

// Collect the form data sent via POST
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['user_role'];  // 'Applicant' or 'Admin' from the form
$github_url = $_POST['github_url'];
$experience = $_POST['experience'];

// Check if the email already exists in the User table
$check = "SELECT * FROM User WHERE Email = '$email'";
$result = $conn->query($check);

if ($result && $result->num_rows > 0) {
    // Email is already taken 
    echo "<script>
        alert('Error: This email is already registered.');
        window.location.href = 'register.html';
    </script>";
} else {
    // Insert the new user into the User table
    $sql = "INSERT INTO User (First_Name, Last_Name, Email, Password, Role)
            VALUES ('$first_name', '$last_name', '$email', '$password', '$role')";

//    generates a unique id

    if ($conn->query($sql) === TRUE) {

        // Get the new user ID for role-specific tables
        $newUserId = $conn->insert_id;

        if ($role == 'Applicant') {
            $_SESSION['current_user_id'] = $newUserId;
            $sql2 = "INSERT INTO Applicant (UserID, GitHub_URL, Experience, Referral_Points)
                     VALUES ($newUserId, '$github_url', '$experience', 0)";
            $conn->query($sql2);
        } elseif ($role == 'Admin') {
            $sql2 = "INSERT INTO Admin (UserID) VALUES ($newUserId)";
            $conn->query($sql2);
        }

        // Registration successful — redirect based on role
        echo "<script>
            alert('Registration Successful!');
            window.location.href = '";
        
        if ($role == 'Applicant') {
            echo "../onboarding/skills.php";
        } else {
            echo "../index.html";
        }
        
        echo "';
        </script>";
    } else {
        // Something went wrong with the query
        echo "<script>
            alert('Error: Registration failed.');
            window.location.href = 'register.html';
        </script>";
    }
}

// Close the database connection
?>
