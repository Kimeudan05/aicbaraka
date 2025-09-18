document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('appSidebar');
  const toggle = document.getElementById('sidebarToggle');

  if (sidebar && toggle) {
    toggle.addEventListener('click', () => {
      const isVisible = sidebar.classList.toggle('is-visible');
      toggle.setAttribute('aria-expanded', String(isVisible));
    });

    sidebar.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 992 && sidebar.classList.contains('is-visible')) {
          sidebar.classList.remove('is-visible');
          toggle.setAttribute('aria-expanded', 'false');
        }
      });
    });
  }

  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const targetId = button.getAttribute('data-password-toggle');
      if (!targetId) {
        return;
      }

      const input = document.getElementById(targetId);
      if (!input) {
        return;
      }

      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      button.setAttribute('aria-pressed', String(isPassword));

      const icon = button.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      }
    });
  });
});
