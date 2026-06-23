<?php
session_start();
require 'dhb.inc.php';
require '../../vendor/autoload.php';

\Stripe\Stripe::setApiKey('');

// --- THE FIX ---
// This checks for 'id' first. If it's missing, it falls back to 'task_id'
$task_id = $_GET['id'] ?? $_GET['task_id'] ?? null;
$runner_id = $_GET['runner_id'] ?? null;
$session_id = $_GET['session_id'] ?? null;

// Ensure all 3 pieces of data exist before proceeding
if (!$task_id || !$runner_id || !$session_id) {
    die("Error: Missing task, runner, or session ID in the URL. (Check if Stripe redirected properly!)");
}
try {
    // 1. Verify the Stripe Session
    $session = \Stripe\Checkout\Session::retrieve($session_id);

    if ($session->payment_status === 'paid') {

        $pdo->beginTransaction();

        // 2. Lock in the Runner and start the task
        $updateTask = "UPDATE tasks SET RunnerID = :runner_id, Status = 'In Progress' WHERE TaskID = :task_id";
        $stmt1 = $pdo->prepare($updateTask);
        $stmt1->execute([':runner_id' => $runner_id, ':task_id' => $task_id]);

        // 3. Mark the chosen runner as 'Accepted' in the applying table
        $acceptRunner = "UPDATE applying SET Status = 'Accepted' WHERE TaskID = :task_id AND RunnerID = :runner_id";
        $stmt2 = $pdo->prepare($acceptRunner);
        $stmt2->execute([':task_id' => $task_id, ':runner_id' => $runner_id]);

        // 4. Reject the other runners who applied
        $rejectOthers = "UPDATE applying SET Status = 'Rejected' WHERE TaskID = :task_id AND RunnerID != :runner_id";
        $stmt3 = $pdo->prepare($rejectOthers);
        $stmt3->execute([':task_id' => $task_id, ':runner_id' => $runner_id]);

        $pdo->commit();

        // 5. Success! Redirect the poster back to the tracking page
        // We use header location here so the user isn't stuck looking at a blank white screen
        header("Location: ../../taskProgressPoster.html?id=" . $task_id);
        exit();

    } else {
        die("Payment was not successful.");
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Database or Stripe Error: " . $e->getMessage());
}
?>