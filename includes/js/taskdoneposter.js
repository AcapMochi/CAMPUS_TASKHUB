const stars = document.querySelectorAll('.star');
let currentRating = 1;

stars.forEach(star => {
  star.addEventListener('click', () => {
    currentRating = parseInt(star.dataset.value);
    updateStars(currentRating);
  });
  star.addEventListener('mouseenter', () => updateStars(parseInt(star.dataset.value)));
  star.addEventListener('mouseleave', () => updateStars(currentRating));
});

function updateStars(val) {
  stars.forEach(s => {
    s.classList.toggle('active', parseInt(s.dataset.value) <= val);
  });
}

updateStars(currentRating);

const submitBtn = document.getElementById('nextBtn');
const skipBtn = document.getElementById('homeBtn');
const feedbackBox = document.getElementById('feedback');

submitBtn.addEventListener('click', () => {
  const userFeedback = feedbackBox.value.trim();
  const urlParams = new URLSearchParams(window.location.search);
  const taskId = urlParams.get('id');

  if (!taskId) {
    alert("No task ID found. Returning to dashboard.");
    window.location.href = 'dashboard.html';
    return;
  }

  submitBtn.disabled = true;
  submitBtn.textContent = 'Submitting...';

  fetch('includes/php/submit_review.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      task_id: taskId,
      rating: currentRating,
      comment: userFeedback
    })
  })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        alert("Thank you! Your review has been saved.");
        window.location.href = 'dashboard.html';
      } else {
        alert("Error: " + data.message);
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Review & Confirm Payment';
      }
    })
    .catch(error => console.error("Error submitting review:", error));
});

skipBtn.addEventListener('click', () => {
  window.location.href = 'dashboard.html';
});

const mockDatabaseResponse =
{
  taskName: "Nasi Lemak",
  topRunnerName: "Bangcip",
  taskRunner: "Bangcip",
  taskDuration: "30 minutes",
  taskPrice: "RM15.00"
};

function loadReceiptData(taskDetails) {
  document.getElementById('topRunnerName').textContent = taskDetails.topRunnerName;
  document.getElementById('taskName').textContent = taskDetails.taskName;
  document.getElementById('taskRunner').textContent = taskDetails.taskRunner;
  document.getElementById('taskDuration').textContent = taskDetails.taskDuration;
  document.getElementById('taskPrice').textContent = taskDetails.taskPrice;
}

loadReceiptData(mockDatabaseResponse);