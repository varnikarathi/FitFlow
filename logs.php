<?php
session_start();
require_once "db/db.php";
require_once "includes/auth.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission (marking workout as completed)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['plan_id'])) {
    $plan_id = intval($_POST['plan_id']);
    $today = date('Y-m-d');

    // Check if the log already exists
    $check = $pdo->prepare("SELECT 1 FROM logs WHERE user_id = ? AND plan_id = ? AND date = ?");
    $check->execute([$user_id, $plan_id, $today]);
    $exists = $check->fetchColumn();

    if (!$exists) {
        // Insert the new log if it doesn't exist
        $insert = $pdo->prepare("INSERT INTO logs (user_id, plan_id, date, status) VALUES (?, ?, ?, 'Completed')");
        $insert->execute([$user_id, $plan_id, $today]);
    }
}

// Fetch logs
$logsStmt = $pdo->prepare("
    SELECT logs.*, plans.goal, plans.intensity 
    FROM logs 
    JOIN plans ON logs.plan_id = plans.id 
    WHERE logs.user_id = ? 
    ORDER BY logs.date DESC
");
$logsStmt->execute([$user_id]);
$logsResult = $logsStmt->fetchAll();

// Fetch plans
$plansStmt = $pdo->prepare("SELECT * FROM plans WHERE user_id = ?");
$plansStmt->execute([$user_id]);
$plansResult = $plansStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Workout Logs - FitFlow</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes slideIn {
      from { transform: translateY(20px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .slide-in {
      animation: slideIn 0.5s ease-out;
    }
  </style>
</head>

<body class="bg-gradient-to-b from-[#06111F] via-[#002147] to-[#193050] text-[#D0E8C5] min-h-screen flex flex-col">

  <!-- Navbar -->
  <header class="fixed top-0 left-0 w-full z-50 bg-gradient-to-r from-[#082240] via-[#002147] to-[#193050] shadow-lg">
    <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-[#FFF8DE] tracking-wide">FitFlow</h1>
      <div class="flex gap-4">
        <a href="dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm transition-all">Dashboard</a>
        <a href="delete_account.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm transition-all">Delete Account</a>
        <a href="logout.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm transition-all">Logout</a>
      </div>
    </div>
  </header>

  <!-- Main -->
  <main class="pt-28 pb-20 px-4 flex-grow slide-in">
    <div class="max-w-4xl mx-auto bg-[#1A1A40] p-8 rounded-xl shadow-lg">
      <h2 class="text-3xl font-bold mb-6 text-[#FFF8DE]">Workout Logs</h2>

      <!-- Mark Completed Form -->
      <!-- Updated Dropdown -->
<form method="POST" class="mb-8 flex flex-col sm:flex-row gap-4 items-start sm:items-end">
  <div class="relative">
    <select name="plan_id" required class="bg-[#193050] text-white border border-gray-500 p-3 rounded-md flex-1 appearance-none hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
      <option value="">Select Plan to mark as completed</option>
      <?php foreach ($plansResult as $plan): ?>
        <option value="<?= $plan['id'] ?>">
          <?= htmlspecialchars($plan['goal'] . ' - ' . $plan['intensity']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <!-- Custom dropdown icon -->
    <div class="absolute top-1/2 right-3 transform -translate-y-1/2 pointer-events-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>
    </div>
  </div>

  <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md transition-all duration-200">
    Mark Today's Workout
  </button>
</form>


      <!-- History Table -->
      <h3 class="text-xl font-semibold mb-3 text-[#FFF8DE]">Your History</h3>
      <?php if (count($logsResult) > 0): ?>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left border-collapse border border-gray-600">
            <thead class="bg-[#0F1C2E] text-white">
              <tr>
                <th class="p-3 border border-gray-600">Date</th>
                <th class="p-3 border border-gray-600">Goal</th>
                <th class="p-3 border border-gray-600">Intensity</th>
                <th class="p-3 border border-gray-600">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logsResult as $log): ?>
                <tr class="hover:bg-[#263F63] transition-all duration-150">
                  <td class="p-3 border border-gray-600"><?= $log['date'] ?></td>
                  <td class="p-3 border border-gray-600"><?= htmlspecialchars($log['goal']) ?></td>
                  <td class="p-3 border border-gray-600"><?= htmlspecialchars($log['intensity']) ?></td>
                  <td class="p-3 border border-gray-600 text-green-400 font-semibold"><?= $log['status'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-center text-gray-300 mt-6">No workouts logged yet. Time to get moving!</p>
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
