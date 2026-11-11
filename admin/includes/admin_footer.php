<?php
if (!defined('VELORA_ACCESS')) {
    define('VELORA_ACCESS', true);
}
?>
    </main>
  </div>

  <script>
    // Global toast trigger for admin feedback
    function showAdminToast(message, isSuccess = true) {
      let toast = document.getElementById('adminToast');
      if (!toast) {
        toast = document.createElement('div');
        toast.id = 'adminToast';
        toast.className = 'fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3 rounded-xl shadow-2xl text-xs font-semibold uppercase tracking-wider transition-all duration-300 transform translate-y-12 opacity-0';
        document.body.appendChild(toast);
      }
      toast.className = `fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3 rounded-xl shadow-2xl text-xs font-semibold uppercase tracking-wider transition-all duration-300 transform translate-y-0 opacity-100 ${
        isSuccess ? 'bg-primary text-on-primary border border-secondary/40' : 'bg-error text-on-error'
      }`;
      toast.innerHTML = `
        <span class="material-symbols-outlined text-[18px]">${isSuccess ? 'verified' : 'warning'}</span>
        <span>${message}</span>
      `;
      setTimeout(() => {
        toast.className = toast.className.replace('translate-y-0 opacity-100', 'translate-y-12 opacity-0');
      }, 3500);
    }
  </script>
</body>
</html>

