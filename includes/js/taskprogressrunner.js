document.addEventListener('DOMContentLoaded', () => {

  const timeEl = document.getElementById('timeValue');

  function parseToSeconds(hhmmss) {
    const [h, m, s] = hhmmss.split(':').map(Number);
    return h * 3600 + m * 60 + s;
  }

  function formatFromSeconds(totalSeconds) {
    const h = Math.floor(totalSeconds / 3600);
    const m = Math.floor((totalSeconds % 3600) / 60);
    const s = totalSeconds % 60;
    return [h, m, s].map(n => String(n).padStart(2, '0')).join(':');
  }

  let remaining = parseToSeconds(timeEl.textContent.trim());

  const countdown = setInterval(() => {
    if (remaining <= 0) {
      clearInterval(countdown);
      timeEl.textContent = '00:00:00';
      return;
    }
    remaining -= 1;
    timeEl.textContent = formatFromSeconds(remaining);
  }, 1000);

  const statusOptions = document.querySelectorAll('.status-option');

  statusOptions.forEach(option => {
    option.addEventListener('click', () => {
      if (option.classList.contains('active')) 
        return;

      statusOptions.forEach(o => {
        o.classList.remove('active');
        const tag = o.querySelector('.current-tag');
        if (tag) tag.remove();
      });

      option.classList.add('active');
      const tag = document.createElement('span');
      tag.className = 'current-tag';
      tag.textContent = ' (current)';
      option.querySelector('.status-text').appendChild(tag);
    });
  });

  document.getElementById('chatBtn').addEventListener('click', () => {
    window.location.href = 'ChatPage.html';
  });

  document.getElementById('cancelBtn').addEventListener('click', () => {
    const confirmed = window.confirm('Are you sure you want to cancel this task?');
    if (confirmed) {
      window.location.href = 'mytasks.html';
    }
  });

  const completeBtn = document.getElementById('completeBtn');
  const cancelBtn = document.getElementById('cancelBtn');

  completeBtn.addEventListener('click', () => {
    completeBtn.disabled = true;
    cancelBtn.disabled = true;
    completeBtn.textContent = 'Completing...';
    clearInterval(countdown);

    setTimeout(() => {
      window.location.href = 'TaskDoneRunner.html';
    }, 700);
  });

});