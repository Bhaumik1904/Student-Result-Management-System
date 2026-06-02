/**
 * ResultPro — Main Application JavaScript
 * Student Result Management System
 * ES6+, jQuery, DataTables, SweetAlert2, Toastr, Select2
 */

'use strict';

// =====================================================
// GLOBAL CONFIG
// =====================================================
const APP = {
  baseUrl: document.querySelector('meta[name="base-url"]')?.content || '',
  toastrDefaults: {
    positionClass: 'toast-top-right',
    timeOut: 3000,
    progressBar: true,
    closeButton: true,
    newestOnTop: true,
  },
};

// ── Toastr defaults ──────────────────────────────────
if (typeof toastr !== 'undefined') {
  toastr.options = APP.toastrDefaults;
}

// ── Chart.js global defaults ─────────────────────────
if (typeof Chart !== 'undefined') {
  Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
  Chart.defaults.font.size   = 12;
  Chart.defaults.color       = '#6E6E73';
}

// =====================================================
// UTILITIES
// =====================================================

/**
 * Show SweetAlert2 delete confirmation
 * @param {string} name  Entity name to confirm
 * @param {Function} callback  Called on confirm
 */
function confirmDelete(name, callback) {
  Swal.fire({
    title: 'Delete ' + name + '?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#FF3B30',
    cancelButtonColor: '#E5E5E7',
    customClass: {
      cancelButton: 'swal-cancel-btn',
      popup: 'swal-apple-popup',
    },
    reverseButtons: true,
  }).then(result => {
    if (result.isConfirmed) callback();
  });
}

/**
 * Show success SweetAlert
 */
function showSuccess(title, text = '') {
  Swal.fire({
    title, text,
    icon: 'success',
    confirmButtonColor: '#1D1D1F',
    timer: 2000,
    timerProgressBar: true,
  });
}

/**
 * Show error SweetAlert
 */
function showError(title, text = '') {
  Swal.fire({ title, text, icon: 'error', confirmButtonColor: '#1D1D1F' });
}

/**
 * Initialize Select2 on all .select2 elements
 */
function initSelect2() {
  if (typeof $.fn.select2 === 'undefined') return;
  $('.select2').select2({ width: '100%' });
}

/**
 * Initialize DataTable
 * @param {string} selector  jQuery selector
 * @param {object} opts      Extra DataTable options
 */
function initDataTable(selector, opts = {}) {
  if (typeof $.fn.DataTable === 'undefined') return;
  const defaults = {
    pageLength: 10,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
    language: {
      search: '',
      searchPlaceholder: 'Search…',
      emptyTable: 'No records found.',
      zeroRecords: 'No matching records found.',
      info: 'Showing _START_ to _END_ of _TOTAL_ entries',
      paginate: { previous: '‹', next: '›' },
    },
    dom: "<'row mb-16'<'col-sm-6'l><'col-sm-6 d-flex justify-content-end'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row mt-16'<'col-sm-5'i><'col-sm-7 d-flex justify-content-end'p>>",
    ...opts,
  };
  return $(selector).DataTable(defaults);
}

// =====================================================
// AJAX HELPERS
// =====================================================

/**
 * Generic AJAX POST
 */
function ajaxPost(url, data, onSuccess, onError) {
  $.ajax({
    url, type: 'POST', data,
    success: function(res) {
      if (typeof res === 'string') {
        try { res = JSON.parse(res); } catch(e) {}
      }
      if (res.success) {
        onSuccess(res);
      } else {
        if (onError) onError(res);
        else toastr.error(res.message || 'An error occurred.');
      }
    },
    error: function() {
      if (onError) onError({ message: 'Server error. Please try again.' });
      else toastr.error('Server error. Please try again.');
    },
  });
}

// =====================================================
// DELETE HANDLERS — generic table-level delete
// =====================================================
$(document).on('click', '.btn-delete-row', function() {
  const url    = $(this).data('url');
  const name   = $(this).data('name') || 'this record';
  const row    = $(this).closest('tr');
  const table  = window.__dataTable;

  confirmDelete(name, function() {
    $.post(url, { csrf_token: $('meta[name="csrf-token"]').attr('content') }, function(res) {
      if (typeof res === 'string') { try { res = JSON.parse(res); } catch(e) {} }
      if (res.success) {
        toastr.success(res.message || 'Deleted successfully.');
        if (table) { table.row(row).remove().draw(); }
        else { row.fadeOut(300, () => row.remove()); }
      } else {
        toastr.error(res.message || 'Delete failed.');
      }
    });
  });
});

// =====================================================
// RESULT SEARCH — AJAX
// =====================================================
function initResultSearch() {
  const form     = document.getElementById('result-search-form');
  const input    = document.getElementById('search-roll');
  const resultWrap = document.getElementById('result-output');
  const spinner  = document.getElementById('search-spinner');

  if (!form) return;

  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const roll = input.value.trim();
    if (!roll) { toastr.warning('Please enter a Roll Number.'); return; }

    if (spinner) spinner.style.display = 'flex';
    if (resultWrap) resultWrap.innerHTML = '';

    $.ajax({
      url: '../results/search.php',
      type: 'POST',
      data: { roll_number: roll, ajax: 1, csrf_token: $('meta[name="csrf-token"]').attr('content') },
      success: function(html) {
        if (spinner) spinner.style.display = 'none';
        if (resultWrap) resultWrap.innerHTML = html;
      },
      error: function() {
        if (spinner) spinner.style.display = 'none';
        toastr.error('Search failed. Please try again.');
      },
    });
  });
}

// =====================================================
// MARKS — Enforce max marks validation
// =====================================================
function initMarksValidation() {
  const marksInput   = document.getElementById('marks_obtained');
  const maxMarksSpan = document.getElementById('max-marks-display');
  const subjectSel   = document.getElementById('subject_id');
  if (!marksInput || !subjectSel) return;

  subjectSel.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const max = opt?.dataset?.max || '';
    if (maxMarksSpan) maxMarksSpan.textContent = max ? 'Max: ' + max : '';
    marksInput.max = max;
  });

  marksInput.addEventListener('input', function() {
    const max = parseFloat(this.max);
    const val = parseFloat(this.value);
    if (val < 0)    this.value = 0;
    if (!isNaN(max) && val > max) this.value = max;
  });
}

// =====================================================
// DASHBOARD CHARTS
// =====================================================
function initDashboardCharts(passCount, failCount, deptLabels, deptData) {
  // Pass vs Fail Doughnut
  const passFailCtx = document.getElementById('passFailChart');
  if (passFailCtx) {
    new Chart(passFailCtx, {
      type: 'doughnut',
      data: {
        labels: ['Pass', 'Fail'],
        datasets: [{
          data: [passCount, failCount],
          backgroundColor: ['#34C759', '#FF3B30'],
          borderWidth: 0,
          hoverOffset: 8,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: { position: 'bottom', labels: { padding: 20, font: { size: 12 } } },
          tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } },
        },
      },
    });
  }

  // Department Bar Chart
  const deptCtx = document.getElementById('deptChart');
  if (deptCtx) {
    new Chart(deptCtx, {
      type: 'bar',
      data: {
        labels: deptLabels,
        datasets: [{
          label: 'Students',
          data: deptData,
          backgroundColor: ['#0071E3','#34C759','#FF9F0A','#FF3B30','#5AC8FA'],
          borderRadius: 6,
          borderSkipped: false,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 },
            grid: { color: '#F0F0F2' },
          },
          x: { grid: { display: false } },
        },
      },
    });
  }
}

// =====================================================
// FORM VALIDATION — client-side
// =====================================================
function validateStudentForm(form) {
  const roll   = form.querySelector('#roll_number');
  const mobile = form.querySelector('#mobile');
  const email  = form.querySelector('#email');
  let valid = true;

  // Roll Number
  if (roll && !roll.value.trim()) {
    highlightError(roll, 'Roll Number is required.');
    valid = false;
  } else if (roll) clearError(roll);

  // Mobile — exactly 10 digits
  if (mobile) {
    const mVal = mobile.value.trim();
    if (!/^\d{10}$/.test(mVal)) {
      highlightError(mobile, 'Mobile must be exactly 10 digits.');
      valid = false;
    } else clearError(mobile);
  }

  // Email
  if (email) {
    const eVal = email.value.trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(eVal)) {
      highlightError(email, 'Please enter a valid email address.');
      valid = false;
    } else clearError(email);
  }

  return valid;
}

function highlightError(el, msg) {
  el.style.borderColor = '#FF3B30';
  el.style.boxShadow   = '0 0 0 3px rgba(255,59,48,.12)';
  let hint = el.parentElement.querySelector('.form-error');
  if (!hint) { hint = document.createElement('div'); hint.className = 'form-error'; el.parentElement.appendChild(hint); }
  hint.textContent = msg;
}

function clearError(el) {
  el.style.borderColor = '';
  el.style.boxShadow   = '';
  const hint = el.parentElement.querySelector('.form-error');
  if (hint) hint.remove();
}

// =====================================================
// DOM READY
// =====================================================
$(function() {
  initSelect2();
  initMarksValidation();
  initResultSearch();

  // Auto-fade flash messages
  setTimeout(() => $('.flash-message').fadeOut(400), 4000);

  // Student form validation
  const studentForm = document.getElementById('student-form');
  if (studentForm) {
    studentForm.addEventListener('submit', function(e) {
      if (!validateStudentForm(this)) e.preventDefault();
    });
  }
});
