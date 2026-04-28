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
$github_url = mysqli_real_escape_string($conn, $_POST['github_url'] ?? '');
$experience = mysqli_real_escape_string($conn, $_POST['experience'] ?? '');

// Check if the email already exists in the User table
$check = "SELECT * FROM User WHERE Email = '$email'";
$result = $conn->query($check);

if ($result->num_rows > 0) {
    // Email is already taken — show error and go back to register page
    echo "<script>
        alert('Error: This email is already registered.');
        window.location.href = 'register.html';
    </script>";
} else {
    // Insert the new user into the User table
    $sql = "INSERT INTO User (First_Name, Last_Name, Email, Password, Role)
            VALUES ('$first_name', '$last_name', '$email', '$password', '$role')";

    if ($conn->query($sql) === TRUE) {

        // If the user is an Applicant, also insert into the Applicant subtable
        if ($role == 'Applicant') {
            $newUserId = $conn->insert_id;
            $_SESSION['current_user_id'] = $newUserId;
            $sql2 = "INSERT INTO Applicant (UserID, GitHub_URL, Experience, Referral_Points)
                     VALUES ($newUserId, '$github_url', '$experience', 0)";
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
$conn->close();
?>
