// Campus TaskHub — Manage Reports (Admin) page logic

document.addEventListener('DOMContentLoaded', () => {

  const reportsList  = document.getElementById('reportsList');
  const searchInput  = document.getElementById('searchInput');
  const filterPills   = document.querySelectorAll('.filter-pill');
  const resultsCount  = document.getElementById('resultsCount');
  const emptyState    = document.getElementById('emptyState');

  const statTotal     = document.getElementById('statTotal');
  const statOpen       = document.getElementById('statOpen');
  const statReviewing  = document.getElementById('statReviewing');
  const statResolved   = document.getElementById('statResolved');

  let activeFilter = 'all';

  const STATUS_LABELS = {
    open: 'Open',
    reviewing: 'Reviewing',
    resolved: 'Resolved',
    dismissed: 'Dismissed'
  };

  /* ── Recalculate the 4 summary stat cards from the current rows ── */
  function updateStats() {
    const rows = reportsList.querySelectorAll('.report-row');
    let open = 0, reviewing = 0, resolved = 0;

    rows.forEach(row => {
      const status = row.dataset.status;
      if (status === 'open') open++;
      if (status === 'reviewing') reviewing++;
      if (status === 'resolved') resolved++;
    });

    statTotal.textContent = rows.length;
    statOpen.textContent = open;
    statReviewing.textContent = reviewing;
    statResolved.textContent = resolved;
  }

  /* ── Apply the current search term + status filter together ── */
  function applyFilters() {
    const query = searchInput.value.trim().toLowerCase();
    const rows = reportsList.querySelectorAll('.report-row');
    let visibleCount = 0;

    rows.forEach(row => {
      const matchesSearch = row.dataset.title.includes(query);
      const matchesFilter = activeFilter === 'all' || row.dataset.status === activeFilter;
      const show = matchesSearch && matchesFilter;

      row.style.display = show ? 'flex' : 'none';
      if (show) visibleCount++;
    });

    resultsCount.textContent = `${visibleCount} report${visibleCount === 1 ? '' : 's'}`;
    emptyState.hidden = visibleCount !== 0;
  }

  searchInput.addEventListener('input', applyFilters);

  filterPills.forEach(pill => {
    pill.addEventListener('click', () => {
      filterPills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      activeFilter = pill.dataset.filter;
      applyFilters();
    });
  });

  /* ── Status pill + action button rebuilder ── */
  function setRowStatus(row, status) {
    row.dataset.status = status;

    const pill = row.querySelector('.status-pill');
    pill.className = `status-pill status-${status}`;
    pill.textContent = STATUS_LABELS[status];

    const actionsBox = row.querySelector('.report-actions');
    const hasReportedUser = row.dataset.reportedUser && row.dataset.reportedUser !== '—';

    if (status === 'open' || status === 'reviewing') {
      actionsBox.innerHTML = `
        <button class="btn-outline btn-green" data-action="resolve">Resolve</button>
        <button class="btn-outline" data-action="dismiss">Dismiss</button>
        ${hasReportedUser ? '<button class="btn-outline btn-red-fill" data-action="suspend">Suspend</button>' : ''}
      `;
    } else {
      // resolved or dismissed
      actionsBox.innerHTML = `
        <button class="btn-outline" data-action="reopen">Reopen</button>
      `;
    }
  }

  /* ── Row action delegation ── */
  reportsList.addEventListener('click', e => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    const row = btn.closest('.report-row');
    const action = btn.dataset.action;
    const title = row.querySelector('.report-title').textContent;
    const reportedUser = row.dataset.reportedUser;

    if (action === 'review') {
      setRowStatus(row, 'reviewing');
      updateStats();
      return;
    }

    if (action === 'resolve') {
      if (window.confirm(`Mark "${title}" as resolved?`)) {
        setRowStatus(row, 'resolved');
        updateStats();
      }
      return;
    }

    if (action === 'dismiss') {
      if (window.confirm(`Dismiss this report with no action taken?`)) {
        setRowStatus(row, 'dismissed');
        updateStats();
      }
      return;
    }

    if (action === 'reopen') {
      setRowStatus(row, 'open');
      updateStats();
      return;
    }

    if (action === 'suspend') {
      if (window.confirm(`Suspend ${reportedUser}'s account over this report?`)) {
        setRowStatus(row, 'resolved');
        updateStats();
        window.alert(`${reportedUser} has been suspended.`);
      }
      return;
    }
  });

  /* ── Init ── */
  updateStats();
  applyFilters();

});