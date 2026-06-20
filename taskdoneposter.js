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

    submitBtn.addEventListener('click', () => 
    {
        const userFeedback = feedbackBox.value.trim();

        console.log("---Review Submitted---");
        console.log("Rating Given: ", currentRating);
        console.log("Feedback Comment:", userFeedback ? userFeedback : "No comment left.");

        window.location.href = 'dashboard.html';
    });

    skipBtn.addEventListener('click' , () => 
    {
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

    function loadReceiptData(taskDetails)
    {
        document.getElementById('topRunnerName').textContent = taskDetails.topRunnerName;
        document.getElementById('taskName').textContent = taskDetails.taskName;
        document.getElementById('taskRunner').textContent = taskDetails.taskRunner;
        document.getElementById('taskDuration').textContent = taskDetails.taskDuration;
        document.getElementById('taskPrice').textContent = taskDetails.taskPrice;
    }

        loadReceiptData(mockDatabaseResponse);