<?php include 'button.php'; ?>

<nav class="bg-gray-200 border-b border-gray-300">
  <div class="content flex justify-between items-center py-4">
    <?php if ($exchangemode) {
      echo '<a href="./interface.php?exchangemode"><img src="./resources/logo.svg" alt="logo" class="h-7 md:h-10"></a>';
    } else {
      echo '<a href="./interface.php"><img src="./resources/logo.svg" alt="logo" class="h-7 md:h-10"></a>';
    }
    ?>
    <div class="hidden lg:flex items-center gap-4">
      <div class="flex gap-8 mr-2">
        <?php button("Support", "", "mailto:support@ratemyelective.ie", "link"); ?>
        <?php if ($exchangemode) {
          button("Electives", "", "./interface.php", "link");
        } else {
          button("Exchanges", "", "./interface.php?exchangemode", "link");
        }
        ?>
      </div>
      <?php button("Account", "", "./account.php"); ?>
      <?php button("Log Out", "", "?logout=true", "outlined"); ?>
    </div>
    <div class="flex lg:hidden">
      <button id="menu-toggle" class="menu-toggle-button" onclick="toggleMobileMenu()">
        <div>
          <span></span>
          <span></span>
          <span></span>
        </div>
      </button>
      <button id="menu-toggle-close" class="hidden" onclick="toggleMobileMenu()">
        <img src="./resources/close.svg" alt="Close" class="w-6 h-6 cursor-pointer" />
      </button>
    </div>
  </div>
  <div id="mobile-nav" class="mobile-nav fixed top-[76px] w-full z-10">
    <div class="flex flex-col justify-start gap-3 content !pt-0 !pb-4 h-max-content w-full bg-gray-200 border-b border-gray-300 shadow-sm">
      <?php button("Support", "", "mailto:support@ratemyelective.ie", "link"); ?>
      <?php if ($exchangemode) {
        button("Electives", "", "./interface.php", "link");
      } else {
        button("Exchanges", "", "./interface.php?exchangemode", "link");
      }
      ?>
      <?php button("Account", "", "./account.php", "link"); ?>
      <?php button("Log Out", "", "?logout=true", "link"); ?>
    </div>
  </div>
</nav>

<script>
  function toggleMobileMenu() {

    const mobileMenuButton = document.getElementById('menu-toggle');
    mobileMenuButton.classList.toggle('hidden');

    const mobileMenuButtonClose = document.getElementById('menu-toggle-close');
    mobileMenuButtonClose.classList.toggle('hidden');


    const mobileNav = document.getElementById('mobile-nav');
    mobileNav.classList.toggle('show');
  }
</script>