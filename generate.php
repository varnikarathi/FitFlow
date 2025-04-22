<?php
session_start();
require_once "db/db.php";
require_once "includes/auth.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goal = trim($_POST['goal']);
    $intensity = trim($_POST['intensity']);
    $duration = intval($_POST['duration']);

    if ($goal && $intensity && $duration) {
        $stmt = $pdo->prepare("INSERT INTO plans (user_id, goal, intensity, duration) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $goal, $intensity, $duration])) {
            $plan_id = $pdo->lastInsertId();
            header("Location: workout.php?plan_id=" . $plan_id);
            exit();
        } else {
            $message = "Error creating plan.";
        }
    } else {
        $message = "Please fill out all fields.";
    }
}
?>
 

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Generate Plan - FitFlow</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes slideIn {
      from { transform: translateY(30px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .slide-in {
      animation: slideIn 0.6s ease-out;
    }
  </style>
</head>

<body class="bg-gradient-to-b from-[#06111F] via-[#002147] to-[#193050] text-[#D0E8C5] flex flex-col min-h-screen">

  <!-- Navbar -->
  <header class="fixed top-0 left-0 w-full z-50 bg-gradient-to-r from-[#082240] via-[#002147] to-[#193050] shadow-lg">
      <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-[#FFF8DE] tracking-wide">FitFlow</h1>
        <div class="flex gap-4">
          <a href="dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm transition-all duration-200">Dashboard</a>
          <a href="delete_account.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm transition-all duration-200">Delete Account</a>
          <a href="logout.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm transition-all duration-200">Logout</a>
        </div>
      </div>
  </header>

  <!-- Form Section -->
  <main class="pt-28 pb-20 flex-grow px-4 flex items-center justify-center">
    <div class="bg-[#1A1A40] text-white p-8 rounded-xl shadow-xl w-full max-w-md slide-in">
      <h2 class="text-2xl font-bold mb-6 text-center text-[#FFF8DE]">Create Your Workout Plan</h2>

      <?php if ($message): ?>
        <p class="mb-4 text-red-400 text-center"><?php echo $message; ?></p>
      <?php endif; ?>

      <form method="POST">
        <label class="block mb-2 text-sm font-medium">Goal</label>
        <select name="goal" class="w-full mb-4 p-2 border rounded bg-[#0F172A] border-[#334155]" required>
          <option value="">Select goal</option>
          <option value="Build Muscle">💪 Build Muscle</option>
          <option value="Lose Fat">🔥 Lose Fat</option>
          <option value="Improve Endurance">🏃 Improve Endurance</option>
        </select>

        <label class="block mb-2 text-sm font-medium">Intensity</label>
        <select name="intensity" class="w-full mb-4 p-2 border rounded bg-[#0F172A] border-[#334155]" required>
          <option value="">Select intensity</option>
          <option value="Beginner">🟢 Beginner</option>
          <option value="Intermediate">🟡 Intermediate</option>
          <option value="Advanced">🔴 Advanced</option>
        </select>

        <label class="block mb-2 text-sm font-medium">Duration (minutes)</label>
        <input type="number" name="duration" min="10" max="120" class="w-full mb-6 p-2 border rounded bg-[#0F172A] border-[#334155]" required>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded transition duration-200">
          Generate Plan
        </button>
      </form>
    </div>
  </main>

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
