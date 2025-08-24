<header class="site-header">
  <div class="header-wrap">
    <div class="header-left">
      <div class="logo-circle"><img src="assets/images/logo.jpg" alt=""></div>
      <h1 class="brand">KH University</h1>
    </div>

    <nav class="header-center">
 <a href="student_dashboard.php?username=<?php echo $_SESSION['username']; ?>">Home</a>
    <a href="events.php">Events</a>
      <a href="help.php">Help</a>
    </nav>

    <div class="header-right">
      <a class="logout-btn" href="index.php">Logout</a>
    </div>
  </div>
</header>
