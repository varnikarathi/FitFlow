<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once "db/db.php";

$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'];

// Fetch all planners
$stmt = $pdo->prepare("SELECT * FROM plans WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$planners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorize planners
$completed = array_filter($planners, fn($p) => $p['status'] === 'completed');
$pending = array_filter($planners, fn($p) => $p['status'] !== 'completed');

// Get the most recent plan for the user (active plan)
$activePlan = null;
foreach ($planners as $plan) {
    if ($plan['status'] !== 'completed') {
        $activePlan = $plan;
        break;
    }
}

// Get the most recent completed plan
$lastCompletedPlan = null;
foreach ($completed as $plan) {
    $lastCompletedPlan = $plan;
    break;
}

// Get the next pending plan
$nextPlan = null;
foreach ($pending as $plan) {
    $nextPlan = $plan;
    break;
}

// Calculate days active (from earliest plan to today)
$daysActive = 0;
if (!empty($planners)) {
    $earliestPlan = end($planners); // Last in DESC order is earliest
    $startDate = new DateTime($earliestPlan['created_at']);
    $today = new DateTime();
    $daysActive = $startDate->diff($today)->days;
}

// Calculate calories burned
$caloriesBurned = 0;
foreach ($completed as $plan) {
    $duration = $plan['duration'] ?? 30; // Default to 30 minutes
    $goal = strtolower($plan['goal'] ?? '');
    $rate = 7; // Default: strength
    if (strpos($goal, 'weight loss') !== false) {
        $rate = 10; // Cardio
    } elseif (strpos($goal, 'endurance') !== false) {
        $rate = 8; // Endurance
    }
    $caloriesBurned += $duration * $rate;
}

$total = count($planners);
$done = count($completed);
$progress = $total > 0 ? round(($done / $total) * 100) : 0;

// Get progress timeline
$timeline = [];
foreach ($planners as $plan) {
    $timeline[] = [
        'date' => date('F j', strtotime($plan['created_at'])),
        'status' => $plan['status'] === 'completed' ? '✔' : '⏳',
        'goal' => $plan['goal']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard - FitFlow</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    @keyframes slideIn {
      from { transform: translateY(30px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    .slide-in {
      animation: slideIn 0.6s ease-out;
    }
    .progress-ring__circle {
      stroke-dasharray: 440;
      stroke-dashoffset: 440;
      transition: stroke-dashoffset 1s ease;
    }
  </style>
</head>

<body class="bg-gradient-to-b from-[#06111F] via-[#002147] to-[#193050] text-[#D0E8C5] flex flex-col min-h-screen">

<!-- Navbar -->
<header class="fixed top-0 left-0 w-full z-50 bg-gradient-to-r from-[#082240] via-[#002147] to-[#193050] shadow-lg">
  <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-[#FFF8DE] tracking-wide">FitFlow</h1>
    <div class="flex gap-4">
      <a href="dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm">Dashboard</a>
      <a href="delete_account.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm">Delete Account</a>
      <a href="logout.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">Logout</a>
    </div>
  </div>
</header>

<main class="pt-28 pb-24 px-4 text-center flex-grow">

  <!-- Welcome Section -->
  <div class="max-w-3xl mx-auto bg-[#1A1A40] text-white p-6 rounded-xl shadow-lg flex items-center gap-6 slide-in mb-12">
    <div class="bg-[#BB8493] w-16 h-16 flex items-center justify-center rounded-full text-2xl font-bold uppercase">
      <?= strtoupper(substr($userName, 0, 1)); ?>
    </div>
    <div class="text-left">
      <h3 class="text-2xl font-semibold mb-1">Welcome, <?= htmlspecialchars($userName); ?>!</h3>
      <p class="text-sm text-[#A0AEC0]">Last login: <?= date('F j, Y'); ?></p>
    </div>
  </div>

  <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3 max-w-5xl mx-auto mb-12">
    <a href="generate.php" class="bg-[#193050] hover:bg-[#2c4069] p-6 rounded-lg shadow-lg transition hover:scale-[1.02]">
      <h3 class="text-xl font-semibold text-[#FFF8DE] mb-2">Generate Plan</h3>
      <p>Create your custom workout flow</p>
    </a>
    <a href="workout.php?plan_id=<?= $activePlan['id'] ?? '' ?>" class="bg-[#193050] hover:bg-[#2c4069] p-6 rounded-lg shadow-lg transition hover:scale-[1.02]">
      <h3 class="text-xl font-semibold text-[#FFF8DE] mb-2">View Plan</h3>
      <p>See your current plan</p>
    </a>
    <a href="logs.php" class="bg-[#193050] hover:bg-[#2c4069] p-6 rounded-lg shadow-lg transition hover:scale-[1.02]">
      <h3 class="text-xl font-semibold text-[#FFF8DE] mb-2">Workout Logs</h3>
      <p>Track your workout history</p>
    </a>
  </div>

  <!-- Two-Column Layout -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto mb-12">
    <!-- Left Column: Info Section -->
    <div class="space-y-6 text-left p-6 bg-gradient-to-r from-[#1A1A40] via-[#002147] to-[#193050] rounded-xl shadow-lg flex flex-col">
      <div>
        <h3 class="text-xl font-semibold mb-3 text-green-300">Current Workout Plan Status</h3>
        <?php if ($activePlan): ?>
          <p>✅ <strong><?= $done ?> Workouts Completed</strong></p>
          <p>🕒 <strong><?= count($pending) ?> Remaining</strong></p>
          <p>📅 <strong>Started on: <?= date('F j, Y', strtotime($activePlan['created_at'])) ?></strong></p>
          <p>🚀 <strong>Goal: <?= htmlspecialchars($activePlan['goal'] ?? 'Unnamed') ?></strong></p>
        <?php else: ?>
          <p class="text-gray-400">No active plan. <a href="generate.php" class="text-blue-400">Create one now!</a></p>
        <?php endif; ?>
      </div>

      <div>
        <h3 class="text-xl font-semibold mb-3 text-yellow-300">Last Completed Plan</h3>
        <?php if ($lastCompletedPlan): ?>
          <p><strong><?= htmlspecialchars($lastCompletedPlan['goal'] ?? 'Unnamed Plan') ?></strong></p>
          <p><small>Completed on: <?= date('F j, Y', strtotime($lastCompletedPlan['created_at'])) ?></small></p>
        <?php else: ?>
          <p class="text-gray-400">No completed plans yet.</p>
        <?php endif; ?>
      </div>

      <div>
        <h3 class="text-xl font-semibold mb-3 text-gray-300">Motivational Quote</h3>
        <blockquote class="italic text-gray-400">"Progress, not perfection."</blockquote>
      </div>

      <!-- Next Workout Preview (Compact with auto width) -->
      <div class="w-auto inline-block bg-white/5 border border-white/10 backdrop-blur-md p-4 rounded-md text-center mb-4 shadow-xl">
        <h3 class="text-xl font-semibold text-white mb-2">Next Workout:</h3>
        <?php if ($nextPlan): ?>
          <p><strong class="text-green-300"><?= htmlspecialchars($nextPlan['goal'] ?? 'Unnamed Plan') ?></strong></p>
          <p><small class="text-gray-300">⏰ Scheduled for: <?= date('F j, Y', strtotime($nextPlan['created_at'] . ' +1 day')) ?></small></p>
        <?php else: ?>
          <p class="text-gray-400">No upcoming workouts.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right Column: Progress Section -->
    <div class="flex flex-col items-center p-6 bg-gradient-to-r from-[#1A1A40] via-[#002147] to-[#193050] rounded-xl shadow-lg">
      <h3 class="text-lg font-bold mb-4 text-white">Overall Progress</h3>
      <div class="relative w-40 h-40 mb-6">
        <svg viewBox="0 0 160 160" class="w-40 h-40">
          <circle class="text-gray-700" stroke="currentColor" stroke-width="16" fill="transparent" r="60" cx="80" cy="80" />
          <circle
            id="progressCircle"
            class="text-green-400 progress-ring__circle"
            stroke="currentColor"
            stroke-width="16"
            fill="transparent"
            r="60"
            cx="80"
            cy="80"
            transform="rotate(-90 80 80)"
          />
          <text x="50%" y="50%" text-anchor="middle" dy=".3em" class="text-white text-2xl font-bold fill-current">
            <?= $progress ?>%
          </text>
        </svg>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-3 gap-4 max-w-xs mb-8">
        <div class="bg-green-800 p-4 rounded-xl text-center shadow-lg text-white">🔥 Total Plans<br><span class="font-bold text-xl"><?= $total ?></span></div>
        <div class="bg-blue-800 p-4 rounded-xl text-center shadow-lg text-white">📅 Days Active<br><span class="font-bold text-xl"><?= $daysActive ?></span></div>
        <div class="bg-yellow-800 p-4 rounded-xl text-center shadow-lg text-white">⚡ Calories Burned<br><span class="font-bold text-xl"><?= $caloriesBurned ?></span></div>
      </div>

      <!-- Mini Progress Timeline -->
      <div class="text-left mb-6 w-full">
        <h3 class="text-lg font-bold text-white mb-3">Progress Timeline</h3>
        <?php if (empty($timeline)): ?>
          <p class="text-gray-400">No timeline available.</p>
        <?php else: ?>
          <ul class="space-y-2 text-gray-300">
            <?php foreach ($timeline as $entry): ?>
              <li><?= $entry['status'] ?> <?= $entry['goal'] ?> – <?= $entry['date'] ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Planner Lists -->
  <div class="max-w-5xl mx-auto text-left space-y-12">
    <div>
      <h3 class="text-2xl font-bold mb-4 text-green-300">Completed Planners</h3>
      <?php if ($completed): ?>
        <ul class="space-y-2">
          <?php foreach ($completed as $plan): ?>
            <li class="bg-green-800 bg-opacity-30 p-4 rounded-md flex justify-between items-center">
              <?= htmlspecialchars($plan['goal'] ?? 'Unnamed Plan') ?>
              <span class="text-sm text-green-400">Completed</span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-sm text-gray-400">No completed plans yet.</p>
      <?php endif; ?>
    </div>

    <div>
      <h3 class="text-2xl font-bold mb-4 text-yellow-300">Pending Planners</h3>
      <?php if ($pending): ?>
        <ul class="space-y-2">
          <?php foreach ($pending as $plan): ?>
            <li class="bg-yellow-800 bg-opacity-20 p-4 rounded-md flex justify-between items-center">
              <?= htmlspecialchars($plan['goal'] ?? 'Unnamed Plan') ?>
              <form action="update_status.php" method="POST" class="ml-4">
                <input type="hidden" name="plan_id" value="<?= htmlspecialchars($plan['id']) ?>">
                <button type="submit" class="text-sm bg-green-800 hover:bg-green-600 text-white px-3 py-1 rounded">
                  Mark as Done
                </button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-sm text-gray-400">No pending plans.</p>
      <?php endif; ?>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const progress = <?= $progress ?>;
    const circle = document.getElementById('progressCircle');

    // Calculate the full perimeter (circumference) of the circle
    const radius = 60;
    const circumference = 2 * Math.PI * radius;

    // Calculate the stroke dashoffset based on the progress percentage
    const offset = circumference - (progress / 100) * circumference;

    // Animate the stroke dashoffset to simulate the progress
    setTimeout(() => {
      circle.style.strokeDasharray = circumference; // Full circumference
      circle.style.strokeDashoffset = offset; // Adjust the stroke based on progress
    }, 300);
    });
</script>



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