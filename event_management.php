<?php
/* ============================================================
   SCPS • Event Management (single file)
   Tabs: registration | details | prizes
   Requires: ?username=USERNAME  (student/faculty)
   Uses: header.php, footer.php, assets/css/dashboard.css
   DB tables:
     CREATE TABLE events (
       id INT AUTO_INCREMENT PRIMARY KEY,
       event_name VARCHAR(100) NOT NULL,
       venue VARCHAR(100),
       timings VARCHAR(100),
       description TEXT,
       prizes TEXT
     );

     CREATE TABLE registrations (
       id INT AUTO_INCREMENT PRIMARY KEY,
       first_name VARCHAR(50) NOT NULL,
       last_name VARCHAR(50),
       roll_number VARCHAR(50) NOT NULL,
       email VARCHAR(100) NOT NULL,
       phone VARCHAR(20) NOT NULL,
       age INT,
       academic_year VARCHAR(20),
       description TEXT,
       event_id INT,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (event_id) REFERENCES events(id)
     );
   ============================================================ */
require_once __DIR__ . '/db.php';

if (!isset($_GET['username']) || $_GET['username'] === '') {
  echo 'Invalid access!';
  exit;
}
$username = $_GET['username'];
$tab = isset($_GET['tab']) && in_array($_GET['tab'], ['registration','details','prizes'], true)
  ? $_GET['tab'] : 'details';

$qUser = '?username=' . urlencode($username);

// ---------- Fetch all events ----------
$events = [];
if ($rs = $conn->query("SELECT id, event_name, venue, timings, description, prizes FROM events ORDER BY id DESC")) {
  while ($r = $rs->fetch_assoc()) { $events[] = $r; }
}

// ---------- Handle Registration POST ----------
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form']) && $_POST['form']==='register') {
  $first = trim($_POST['first_name'] ?? '');
  $last  = trim($_POST['last_name'] ?? '');
  $roll  = trim($_POST['roll_number'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $age   = isset($_POST['age']) ? (int)$_POST['age'] : null;
  $year  = trim($_POST['academic_year'] ?? '');
  $desc  = trim($_POST['description'] ?? '');
  $event_id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

  if ($first === '' || $roll === '' || $email === '' || $phone === '' || $event_id === 0) {
    $flash = 'Please fill all required fields (First Name, Roll Number, Email, Phone, Select Event).';
    $tab = 'registration';
  } else {
    $stmt = $conn->prepare(
      "INSERT INTO registrations (first_name,last_name,roll_number,email,phone,age,academic_year,description,event_id)
       VALUES (?,?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param(
      "sssssiisi",
      $first, $last, $roll, $email, $phone, $age, $year, $desc, $event_id
    );
    if ($stmt->execute()) {
      $flash = 'Registration submitted successfully! 🎉';
      $tab = 'registration';
      $_POST = [];
    } else {
      $flash = 'Failed to submit. Please try again later.';
      $tab = 'registration';
    }
    $stmt->close();
  }
}

// helper
function h2($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>SCPS • Event Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/css/dashboard.css" />
  <style>
    .section-title{margin:8px 0 16px;font-size:26px;font-weight:800;color:#27364a}
    .list{display:grid;gap:12px;max-width:980px}
    .bar{display:flex;align-items:center;gap:16px;background:#fff;border-radius:14px;padding:16px;box-shadow:var(--shadow)}
    .file-badge{width:58px;height:58px;min-width:58px;border-radius:14px;display:grid;place-items:center;background:#eef4ff;color:#2c6fbc;font-size:26px;box-shadow:inset 0 0 0 1.5px #d9e3f1}
    .meta{flex:1}
    .meta h4{margin:0;font-size:18px}
    .meta p{margin:3px 0;color:#607089;font-size:13px}
    .btn-dl{ text-decoration:none; display:inline-block; background:linear-gradient(90deg,#7b61ff,#38f9d7); color:#fff; font-weight:800; padding:10px 14px; border-radius:12px; }
    .ev-form {background:#fff;border-radius:16px;padding:18px;box-shadow:var(--shadow);max-width:980px}
    .ev-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .ev-row-3{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .ev-row > div, .ev-row-3 > div{display:flex;flex-direction:column;gap:6px}
    .ev-form input, .ev-form select, .ev-form textarea{width:100%;padding:10px 12px;border:1px solid #e1e8f0;border-radius:12px;font:inherit}
    .ev-form textarea{min-height:110px;resize:vertical}
    .ev-actions{margin-top:12px;display:flex;gap:10px;align-items:center}
    .hint{color:#607089;font-weight:600}
    .sidebar .nav-item.active{background:#fff;box-shadow:var(--shadow)}
    .main { padding-bottom: 24px; }

    /* New card layout */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
      max-width: 980px;
    }
    .card {
      background: #fff;
      border-radius: 16px;
      padding: 18px;
      box-shadow: var(--shadow);
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .card-icon {
      font-size: 32px;
      margin-bottom: 6px;
    }
    .card h4 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
    }
    .card .desc {
      color: #607089;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="layout">
    <aside class="sidebar">
      <nav>
        <a class="nav-item <?php echo $tab==='registration'?'active':''; ?>" href="event_management.php<?= $qUser ?>&tab=registration">
          <span class="dot"><img src="assets/images/event.png" alt=""></span>
          <span class="nav-text">Event Registration</span>
        </a>
        <a class="nav-item <?php echo $tab==='details'?'active':''; ?>" href="event_management.php<?= $qUser ?>&tab=details">
          <span class="dot"><img src="assets/images/event.png" alt=""></span>
          <span class="nav-text">Event Details</span>
        </a>
        <a class="nav-item <?php echo $tab==='prizes'?'active':''; ?>" href="event_management.php<?= $qUser ?>&tab=prizes">
          <span class="dot"><img src="assets/images/prize.png" alt=""></span>
          <span class="nav-text">Event Prizes</span>
        </a>
      </nav>
    </aside>

    <main class="main">
      <?php if ($tab === 'registration'): ?>
        <h2 class="section-title">Event Registration</h2>
        <?php if ($flash): ?><p class="hint"><?= h2($flash) ?></p><?php endif; ?>

        <!-- Registration Form -->
        <div class="ev-form">
          <div class="bar" style="margin-bottom:14px">
            <div class="file-badge">📝</div>
            <div class="meta">
              <h4>Registration Form</h4>
              <p>Fill the form to register for an event</p>
            </div>
          </div>
          <form method="post" action="event_management.php<?= $qUser ?>&tab=registration" autocomplete="off" novalidate>
            <input type="hidden" name="form" value="register" />
            <div class="ev-row">
              <div><label>First Name *</label><input name="first_name" required value="<?= h2($_POST['first_name'] ?? '') ?>"></div>
              <div><label>Last Name (optional)</label><input name="last_name" value="<?= h2($_POST['last_name'] ?? '') ?>"></div>
            </div>
            <div class="ev-row">
              <div><label>Email *</label><input type="email" name="email" required value="<?= h2($_POST['email'] ?? '') ?>"></div>
              <div><label>Phone Number *</label><input name="phone" required value="<?= h2($_POST['phone'] ?? '') ?>"></div>
            </div>
            <div class="ev-row">
              <div><label>Roll Number *</label><input name="roll_number" required value="<?= h2($_POST['roll_number'] ?? '') ?>"></div>
              <div>
                <label>Academic Year *</label>
                <select name="academic_year" required>
                  <?php
                  $years = ['First Year','Second Year','Third Year','Fourth Year','PG'];
                  $selYear = $_POST['academic_year'] ?? '';
                  echo '<option value="">Select your year</option>';
                  foreach ($years as $y) {
                    $sel = ($selYear===$y)?'selected':''; echo "<option $sel>".h2($y)."</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="ev-row-3">
              <div>
                <label>Age</label>
                <select name="age">
                  <option value="">Select age</option>
                  <?php $selAge = (int)($_POST['age'] ?? 0);
                  for ($a=16; $a<=60; $a++){ $sel = ($selAge===$a)?'selected':''; echo "<option value=\"$a\" $sel>$a</option>"; } ?>
                </select>
              </div>
              <div>
                <label>Select Event *</label>
                <select name="event_id" required>
                  <option value="">-- Select Event --</option>
                  <?php foreach ($events as $ev): ?>
                    <option value="<?= (int)$ev['id'] ?>" <?= (isset($_POST['event_id']) && (int)$_POST['event_id']===(int)$ev['id'])?'selected':'' ?>>
                      <?= h2($ev['event_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div style="margin-top:12px">
              <label>What do you hope to gain from this event?</label>
              <textarea name="description"><?= h2($_POST['description'] ?? '') ?></textarea>
            </div>
            <div class="ev-actions">
              <button class="btn-dl" type="submit">Register for Event</button>
              <span class="hint">All fields marked * are required.</span>
            </div>
          </form>
        </div>

      <?php elseif ($tab === 'details'): ?>
        <h2 class="section-title">All Events</h2>
        <?php if (!$events): ?>
          <p>No events found.</p>
        <?php else: ?>
          <div class="cards-grid">
            <?php foreach ($events as $ev): ?>
              <div class="card">
                <div class="card-icon">📅</div>
                <h4><?= h2($ev['event_name']) ?></h4>
                <p class="desc"><?= h2($ev['description'] ?: '') ?></p>
                <p><strong>Venue:</strong> <?= h2($ev['venue'] ?: '—') ?></p>
                <p><strong>Time:</strong> <?= h2($ev['timings'] ?: '—') ?></p>
                <a class="btn-dl" href="event_management.php<?= $qUser ?>&tab=registration">Register</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php else: /* prizes */ ?>
        <h2 class="section-title">Event Prizes</h2>
        <?php if (!$events): ?>
          <p>No events found.</p>
        <?php else: ?>
          <div class="cards-grid">
            <?php foreach ($events as $ev): ?>
              <div class="card">
                <div class="card-icon">🏆</div>
                <h4><?= h2($ev['event_name']) ?></h4>
                <p><strong>Prizes:</strong> <?= h2($ev['prizes'] ?: '—') ?></p>
                <p><strong>Venue:</strong> <?= h2($ev['venue'] ?: '—') ?></p>
                <p><strong>Time:</strong> <?= h2($ev['timings'] ?: '—') ?></p>
                <a class="btn-dl" href="event_management.php<?= $qUser ?>&tab=registration">Register</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </main>
  </div>

  <?php include 'footer.php'; ?>
</body>
</html>
