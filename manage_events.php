<?php
// manage_events.php  (Admin-only)
// Requires: db.php, header.php, footer.php, dashboard.css (your common files)

require_once "db.php";

// Guard
if (!isset($_GET['username']) || $_GET['username'] === '') {
  echo "Invalid access!";
  exit;
}
$username = $_GET['username'];

// Verify admin exists (optional but safer)
$adm = null;
if ($stmt = $conn->prepare("SELECT admin_name FROM admin WHERE username = ? LIMIT 1")) {
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res && $res->num_rows === 1) {
    $adm = $res->fetch_assoc();
  }
  $stmt->close();
}
if (!$adm) {
  echo "Admin not found!";
  exit;
}

$q = "?username=" . urlencode($username);

// Handle CREATE
$flash = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='create') {
  $event_name = trim($_POST['event_name'] ?? "");
  $venue      = trim($_POST['venue'] ?? "");
  $dt_text    = trim($_POST['event_datetime'] ?? "");  // ✅ fixed field name
  $prizes     = trim($_POST['prizes'] ?? "");
  $descr      = trim($_POST['description'] ?? "");

  if ($event_name !== "" && $venue !== "" && $dt_text !== "") {
    $sql = "INSERT INTO events (event_name, venue, timings, prizes, description, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    if ($st = $conn->prepare($sql)) {
      $st->bind_param("sssss", $event_name, $venue, $dt_text, $prizes, $descr);
      if ($st->execute()) {
        $flash = "Event created successfully.";
      } else {
        $flash = "Failed to create event.";
      }
      $st->close();
    } else {
      $flash = "DB error.";
    }
  } else {
    $flash = "Please fill Event Name, Venue, and Date & Time.";
  }
}

// Handle DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='delete') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id > 0) {
    $del = $conn->prepare("DELETE FROM events WHERE id = ?");
    $del->bind_param("i", $id);
    if ($del->execute()) {
      $flash = "Event deleted.";
    } else {
      $flash = "Delete failed.";
    }
    $del->close();
  }
}

// Fetch events list
$events = [];
$rs = mysqli_query($conn, "SELECT id, event_name, venue, timings, prizes, description, created_at
                            FROM events ORDER BY created_at DESC");
if ($rs) {
  while ($row = mysqli_fetch_assoc($rs)) $events[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage Events</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/css/dashboard.css">
  <style>
    .section-card{ background: var(--card-grad); border-radius:18px; box-shadow: var(--shadow); padding:18px; margin-bottom:18px; }
    .form-grid{ display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .form-grid .full{ grid-column: 1 / -1; }
    .table{ width:100%; border-collapse:separate; border-spacing:0 10px; }
    .table th{ text-align:left; color:#2b3a4a; font-weight:800; padding:8px 10px; }
    .table td{ background:#fff; padding:12px 10px; }
    .btn{ border:0; border-radius:12px; padding:10px 14px; font-weight:800; cursor:pointer; }
    .btn-primary{ color:#fff; background:linear-gradient(180deg,#7b61ff,#6aa6ff); }
    .btn-danger{ color:#fff; background:linear-gradient(180deg,#ff6b6b,#ff3d3d); }
    .muted{ color:var(--muted); }
    .field{ width:100%; border:1px solid #e6eaf1; border-radius:12px; padding:10px 12px; font:inherit; }
    .flash{ margin-bottom:14px; font-weight:700; color:#0f1f33; }
    @media (max-width: 860px){ .form-grid{ grid-template-columns:1fr; } }
  </style>
</head>
<body>
  <?php include "header.php"; ?>

  <div class="layout">
    <aside class="sidebar">
      <nav>
        <a class="nav-item" href="manage_students.php<?php echo $q; ?>">
          <span class="dot"><img src="assets/images/students.png" alt=""></span>
          <span class="nav-text">manage students</span>
        </a>
        <a class="nav-item" href="manage_faculty.php<?php echo $q; ?>">
          <span class="dot"><img src="assets/images/faculty.png" alt=""></span>
          <span class="nav-text">manage faculty</span>
        </a>
        <a class="nav-item" href="manage_events.php<?php echo $q; ?>">
          <span class="dot"><img src="assets/images/event.png" alt=""></span>
          <span class="nav-text">manage events</span>
        </a>
        <a class="nav-item" href="lost_reports.php<?php echo $q; ?>">
          <span class="dot"><img src="assets/images/lostfound.png" alt=""></span>
          <span class="nav-text">lost reports</span>
        </a>
        <a class="nav-item" href="old_books.php<?php echo $q; ?>">
          <span class="dot"><img src="assets/images/book_exchange.png" alt=""></span>
          <span class="nav-text">old books</span>
        </a>
      </nav>
    </aside>

    <main class="main">
      <section class="detail-bar">
        <div class="detail-overlay">
          <h2>Manage Events</h2>
          <p class="muted">Create new events or remove expired ones.</p>
        </div>
      </section>

      <?php if ($flash): ?>
        <div class="flash section-card"><?php echo htmlspecialchars($flash); ?></div>
      <?php endif; ?>

      <!-- Create Event -->
      <section class="section-card">
        <h3 style="margin:0 0 10px 0;">Create New Event</h3>
        <form method="post">
          <input type="hidden" name="action" value="create">
          <div class="form-grid">
            <div>
              <label>Event Name *</label>
              <input class="field" type="text" name="event_name" placeholder="e.g., Annual Science Fair" required>
            </div>
            <div>
              <label>Venue *</label>
              <input class="field" type="text" name="venue" placeholder="e.g., Main Auditorium" required>
            </div>

            <div class="full">
              <label>Date &amp; Time *</label>
              <input class="field" type="text" name="event_datetime" placeholder="e.g., 2024-03-15 10:00 AM - 4:00 PM" required>
            </div>

            <div class="full">
              <label>Prizes &amp; Rewards</label>
              <input class="field" type="text" name="prizes" placeholder="e.g., 1st: ₹10000, 2nd: ₹5000, 3rd: ₹2500">
            </div>

            <div class="full">
              <label>Description (Optional)</label>
              <textarea class="field" name="description" rows="4" placeholder="Additional event details..."></textarea>
            </div>

            <div class="full" style="display:flex; gap:10px;">
              <button class="btn btn-primary" type="submit">Create Event</button>
            </div>
          </div>
        </form>
      </section>

      <!-- Event List -->
      <section class="section-card">
        <h3 style="margin:0 0 10px 0;">Event Calendar</h3>

        <?php if (empty($events)): ?>
          <p class="muted">No events found.</p>
        <?php else: ?>
          <div style="overflow:auto;">
            <table class="table">
              <thead>
                <tr>
                  <th>Event Name</th>
                  <th>Venue</th>
                  <th>Timing</th>
                  <th>Prizes</th>
                  <th style="width:160px;">Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($events as $ev): ?>
                <tr>
                  <td><?php echo htmlspecialchars($ev['event_name']); ?></td>
                  <td><?php echo htmlspecialchars($ev['venue']); ?></td>
                  <td><?php echo htmlspecialchars($ev['timings']); ?></td>
                  <td><?php echo htmlspecialchars($ev['prizes']); ?></td>
                  <td>
                    <form method="post" onsubmit="return confirm('Delete this event?');" style="display:inline;">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?php echo (int)$ev['id']; ?>">
                      <button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <?php include "footer.php"; ?>
</body>
</html>
