document.addEventListener('DOMContentLoaded', function () {
  var toggleBtn = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('sbSidenav');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function () {
      sidebar.classList.toggle('show');
    });
  }

  // === Tambahan: overlay gelap di belakang sidebar saat dibuka di HP/tablet ===
  // Tap di area gelap ini akan otomatis menutup sidebar, dan sidebar juga
  // otomatis tertutup begitu salah satu menu ditekan (khusus layar <= 991px),
  // supaya lebih nyaman dipakai satu tangan di Android/iOS.
  var overlay = document.getElementById('sbOverlay');
  if (sidebar && overlay) {
    function closeSidebarMobile() {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    }
    function openSidebarMobile() {
      overlay.classList.add('show');
    }

    var mqMobile = window.matchMedia('(max-width: 991.98px)');

    if (toggleBtn) {
      toggleBtn.addEventListener('click', function () {
        if (mqMobile.matches) {
          if (sidebar.classList.contains('show')) {
            openSidebarMobile();
          } else {
            closeSidebarMobile();
          }
        }
      });
    }

    overlay.addEventListener('click', closeSidebarMobile);

    sidebar.querySelectorAll('.nav-link').forEach(function (link) {
      link.addEventListener('click', function () {
        if (mqMobile.matches) closeSidebarMobile();
      });
    });
  }

  // Auto-hide alert setelah beberapa detik
  document.querySelectorAll('.alert-auto-hide').forEach(function (el) {
    setTimeout(function () {
      var alert = bootstrap.Alert.getOrCreateInstance(el);
      alert.close();
    }, 5000);
  });
});
