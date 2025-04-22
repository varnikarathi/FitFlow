<?php
session_start();
require_once 'db/db.php';

// Redirect to the dashboard if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";
$messageType = ""; // success or error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic form validation
    if (empty($name) || empty($email) || empty($password)) {
        $message = "All fields are required.";
        $messageType = "error";
    } else {
        // Check if email already exists in the database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $message = "Email already registered. <a href='login.php' class='underline text-[#fff] hover:text-blue-600'>Try logging in</a>.";
            $messageType = "error";
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user data into the database
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();

            $message = "Registration successful! 🎉 <a href='login.php' class='underline text-[#BB8493] text-bold'>Login now</a>.";
            $messageType = "success";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up - FitFlow Generator</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Toggle dark/light theme
    function toggleTheme() {
      const body = document.body;
      body.classList.toggle('dark');
      localStorage.setItem('theme', body.classList.contains('dark') ? 'dark' : 'light');
      updateIcon();
    }

    function updateIcon() {
      const icon = document.getElementById('theme-toggle-icon');
      if (document.body.classList.contains('dark')) {
        icon.innerHTML = '🌙'; // Moon icon for dark theme
      } else {
        icon.innerHTML = '🌞'; // Sun icon for light theme
      }
    }

    window.onload = () => {
      if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark');
      }
      updateIcon(); // Ensure the icon is updated based on the current theme
    }
  </script>
  <style>
    @keyframes slideIn {
      0% { transform: translateY(50px); opacity: 0; }
      100% { transform: translateY(0); opacity: 1; }
    }

    .slide-in {
      animation: slideIn 1s ease-out forwards;
    }
  </style>
</head>
<body class="bg-gradient-to-b from-[#06111F] via-[#002147] to-[#193050] text-[#D0E8C5] flex flex-col min-h-screen">

  <!-- Navbar -->
  <header class="fixed top-0 left-0 w-full z-50 bg-gradient-to-r from-[#082240] via-[#002147] to-[#193050] shadow-md">
    <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-[#FFF8DE]">FitFlow</h1>
    </div>
  </header>

  <!-- Signup Form -->
  <section class="pt-28 pb-24 px-4 text-center flex-grow">
    <div class="max-w-sm mx-auto bg-[#002147] p-8 rounded-lg shadow-lg slide-in">
      <h2 class="text-2xl font-bold text-[#FFF8DE] mb-4">Create an Account</h2>
      <?php if (!empty($message)): ?>
        <p class="slide-in mb-4 px-4 py-2 rounded text-sm <?= $messageType === 'success' ? 'bg-green-600 text-white' : 'bg-red-500 text-white' ?>">
          <?= $message ?>
        </p>
      <?php endif; ?>
      <form action="signup.php" method="POST">
        <div class="mb-4">
          <input type="text" name="name" placeholder="Full Name" class="w-full p-3 bg-[#193050] text-[#D0E8C5] rounded-lg" required>
        </div>
        <div class="mb-4">
          <input type="email" name="email" placeholder="Email" class="w-full p-3 bg-[#193050] text-[#D0E8C5] rounded-lg" required>
        </div>
        <div class="mb-4">
          <input type="password" name="password" placeholder="Password" class="w-full p-3 bg-[#193050] text-[#D0E8C5] rounded-lg" required>
        </div>
        <button type="submit" class="w-full bg-[#BB8493] hover:bg-[#704264] text-[#FFF8DE] py-3 rounded-full mt-4">Sign Up</button>
      </form>
      <div class="mt-4 text-[#D0E8C5]">
        Already have an account? <a href="login.php" class="text-[#BB8493] hover:text-[#704264]">Login</a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gradient-to-r from-[#193050] to-[#082240] dark:from-[#06111F] dark:to-[#091428] text-center text-[#FFF8DE] dark:text-[#D0E8C5] text-sm py-6">
      <p>&copy; 2025 FitFlow. Built by Bhaumik, Rahul, Varnika, Monalisa.</p>
      <p class="italic mt-2">"Fitness is not about being better than someone else. It's about being better than you used to be."</p>

      <!-- Social Media Links -->
      <div class="mt-4">
        <a href="https://www.facebook.com" target="_blank" class="text-[#FFF8DE] hover:text-[#BB8493] mx-2">
          <i class="fab fa-facebook-f"></i> <!-- Facebook Icon -->
        </a>
        <a href="https://www.twitter.com" target="_blank" class="text-[#FFF8DE] hover:text-[#BB8493] mx-2">
          <i class="fab fa-twitter"></i> <!-- Twitter Icon -->
        </a>
        <a href="https://www.instagram.com" target="_blank" class="text-[#FFF8DE] hover:text-[#BB8493] mx-2">
          <i class="fab fa-instagram"></i> <!-- Instagram Icon -->
        </a>
        <a href="https://www.linkedin.com" target="_blank" class="text-[#FFF8DE] hover:text-[#BB8493] mx-2">
          <i class="fab fa-linkedin-in"></i> <!-- LinkedIn Icon -->
        </a>
        <a href="https://www.youtube.com" target="_blank" class="text-[#FFF8DE] hover:text-[#BB8493] mx-2">
          <i class="fab fa-youtube"></i> <!-- YouTube Icon -->
        </a>
      </div>
    </footer>

</body>
</html>
