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
  }, 4000);

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

  completeBtn.addEventListener('click', () => 
    {
    completeBtn.disabled = true;
    cancelBtn.disabled = true;
    completeBtn.textContent = 'Completing...';

    stepBuying.classList.remove('active');
    stepBuying.classList.add('done');
    stepBuying.querySelector('.dot').classList.remove('dot-active');
    stepBuying.querySelector('.dot').classList.add('dot-done');

    stepDelivering.classList.remove('pending');
    stepDelivering.classList.add('done');
    const deliveringDot = stepDelivering.querySelector('.dot');
    deliveringDot.classList.remove('dot-pending');
    deliveringDot.classList.add('dot-done');
    stepDelivering.querySelector('.step-time').textContent = 'Delivered just now';

    statusValue.textContent = 'Completed';
    statusValue.style.color = 'var(--accent)';

    setTimeout(() => 
    {
      window.location.href = 'taskdonerunner.html';
    }, 900);
  });

});