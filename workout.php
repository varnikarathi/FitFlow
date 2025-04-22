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
$plan_id = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;
$plan = null;
$workouts = [];

if ($plan_id) {
    // ✅ Use PDO instead of $conn and MySQLi
    $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = ? AND user_id = ?");
    $stmt->execute([$plan_id, $user_id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($plan) {
        // Load workouts.json
        $jsonData = file_get_contents("data/workouts.json");
        $allWorkouts = json_decode($jsonData, true);

        $goal = $plan['goal'];
        $intensity = $plan['intensity'];

        // Match workouts for goal & intensity
        if (isset($allWorkouts[$goal][$intensity])) {
            $workouts = $allWorkouts[$goal][$intensity];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Workout Plan - FitFlow</title>
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
        <a href="dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm transition-all duration-200">
          Dashboard
        </a>
        <a href="delete_account.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm transition-all duration-200">
          Delete Account
        </a>
        <a href="logout.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm transition-all duration-200">
          Logout
        </a>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="pt-28 pb-24 px-4 flex-grow">
  <div class="max-w-3xl mx-auto bg-white/5 backdrop-blur-xl text-white p-10 rounded-2xl shadow-2xl border border-white/10 slide-in">

    <h2 class="text-4xl font-extrabold mb-8 text-center text-[#FFF8DE] tracking-wide drop-shadow-md">
      🏋️ Your Personalized Workout Plan
    </h2>

    <?php if (!$plan): ?>
      <p class="text-red-400 text-center text-lg">Plan not found or you haven't started yet!</p>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center mb-10">
        <div class="bg-gradient-to-br from-purple-700 to-purple-500 p-4 rounded-xl shadow-md">
          <p class="text-sm uppercase text-gray-200">🎯 Goal</p>
          <p class="text-lg font-bold text-white mt-1"><?php echo htmlspecialchars($plan['goal']); ?></p>
        </div>
        <div class="bg-gradient-to-br from-green-700 to-green-500 p-4 rounded-xl shadow-md">
          <p class="text-sm uppercase text-gray-200">🔥 Intensity</p>
          <p class="text-lg font-bold text-white mt-1"><?php echo htmlspecialchars($plan['intensity']); ?></p>
        </div>
        <div class="bg-gradient-to-br from-blue-700 to-blue-500 p-4 rounded-xl shadow-md">
          <p class="text-sm uppercase text-gray-200">⏱ Duration</p>
          <p class="text-lg font-bold text-white mt-1"><?php echo htmlspecialchars($plan['duration']); ?> mins</p>
        </div>
      </div>

      <h3 class="text-2xl font-semibold mb-4 text-[#FFF8DE] text-center drop-shadow">💪 Recommended Exercises</h3>
      <ul class="grid sm:grid-cols-2 gap-4 text-sm text-white">
        <?php foreach ($workouts as $exercise): ?>
          <li class="bg-[#1A1A40] bg-opacity-60 border border-white/10 rounded-lg px-4 py-3 flex items-center gap-3 shadow hover:scale-[1.02] transition-transform duration-200">
            <span class="text-green-400 text-xl">✅</span>
            <span><?php echo htmlspecialchars($exercise); ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <!-- Bonus Buttons -->
      <div class="mt-10 text-center">
        <a href="generate.php" class="inline-block bg-gradient-to-r from-pink-500 to-red-500 text-white px-6 py-2 rounded-full shadow-md hover:shadow-lg transition-all duration-300 text-sm font-medium">
          Generate New Plan
        </a>
      </div>

    <?php endif; ?>
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
