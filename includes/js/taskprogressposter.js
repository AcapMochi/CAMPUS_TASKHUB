document.addEventListener('DOMContentLoaded', () => {

  const etaEl = document.getElementById('etaValue');
  let etaMinutes = 20;

  const etaInterval = setInterval(() => {
    if (etaMinutes <= 1) {
      etaEl.textContent = '<1 min';
      clearInterval(etaInterval);
      return;
    }
    etaMinutes -= 1;
    etaEl.textContent = `~${etaMinutes} mins`;
  }, 6000);


  document.getElementById('chatBtn').addEventListener('click', () => {
    window.location.href = 'chatpage.html';
  });

  document.getElementById('cancelBtn').addEventListener('click', () => {
    const confirmed = window.confirm('Are you sure you want to cancel this task?');
    if (confirmed) {
      window.location.href = 'mytasks.html';
    }
  });

  const completeBtn = document.getElementById('completeBtn');
  const cancelBtn = document.getElementById('cancelBtn');
  const stepBuying = document.getElementById('stepBuying');
  const stepDelivering = document.getElementById('stepDelivering');
  const statusValue = document.getElementById('statusValue');

  completeBtn.addEventListener('click', () => {
    // Get the Task ID from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const taskId = urlParams.get('id');

    if (!taskId) return alert("Task ID missing!");

    if (!confirm("Are you sure you want to mark this complete? Funds will be released to the runner.")) return;

    completeBtn.disabled = true;
    cancelBtn.disabled = true;
    completeBtn.textContent = 'Completing...';

    // Call the PHP Backend
    fetch('includes/php/complete_task_handler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ task_id: taskId })
    })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          // UI Animations
          stepBuying.classList.remove('active');
          stepBuying.classList.add('done');
          stepBuying.querySelector('.dot').classList.remove('dot-active');
          stepBuying.querySelector('.dot').classList.add('dot-done');

          stepDelivering.classList.remove('pending');
          stepDelivering.classList.add('done');
          stepDelivering.querySelector('.dot').classList.remove('dot-pending');
          stepDelivering.querySelector('.dot').classList.add('dot-done');
          stepDelivering.querySelector('.step-time').textContent = 'Delivered just now';

          statusValue.textContent = 'Completed';
          statusValue.style.color = 'var(--accent)';

          // Redirect to the review page, carrying the Task ID over!
          setTimeout(() => {
            window.location.href = `taskdoneposter.html?id=${taskId}`;
          }, 900);
        } else {
          alert("Error: " + data.message);
          completeBtn.disabled = false;
          completeBtn.textContent = 'Mark as Complete';
        }
      });
  });

});
