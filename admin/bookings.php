<?php
// admin/bookings.php
$config = require __DIR__.'/includes/config.php';
require __DIR__.'/includes/helpers.php';
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/middleware.php';

start_session($config['SESSION_NAME']);
$db = (new Database($config))->pdo();
require_auth();

/* -----------------------------------------
   Status actions (POST)
------------------------------------------ */
$allowedStatuses = ['Pending','Confirmed','Paid','Completed','Cancelled'];
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='status') {
  verify_csrf();
  $id = (int)$_POST['BookingID'];
  $new = trim($_POST['new_status']);
  if (!in_array($new, $allowedStatuses, true)) {
    flash('error','Invalid status.'); header('Location: bookings.php'); exit;
  }
  $stmt = $db->prepare("UPDATE bookings SET BookingStatus=? WHERE BookingID=?");
  $stmt->bind_param('si', $new, $id);
  if ($stmt->execute()) flash('ok',"Booking #$id → $new");
  else flash('error','Update failed: '.$stmt->error);
  header('Location: bookings.php'); exit;
}

/* -----------------------------------------
   Filters (GET)
------------------------------------------ */
$q       = trim($_GET['q'] ?? '');                 // name/email/room
$status  = trim($_GET['status'] ?? '');            // status filter
$from    = trim($_GET['from'] ?? '');              // CheckInDate >=
$to      = trim($_GET['to'] ?? '');                // CheckOutDate <=

$sql = "
  SELECT b.BookingID, b.UserID, b.RoomID, b.CheckInDate, b.CheckOutDate, b.NumGuests,
         b.TotalPrice, b.BookingStatus, b.CreatedAt,
         u.username, u.email,
         r.RoomNumber
  FROM bookings b
  LEFT JOIN users u  ON u.id = b.UserID
  LEFT JOIN rooms r  ON r.RoomID = b.RoomID
  WHERE 1=1
";
$types=''; $params=[];

if ($q !== '') {
  $sql .= " AND (u.username LIKE CONCAT('%',?,'%') OR u.email LIKE CONCAT('%',?,'%') OR r.RoomNumber LIKE CONCAT('%',?,'%'))";
  $types .= 'sss'; $params[]=$q; $params[]=$q; $params[]=$q;
}
if ($status !== '' && in_array($status, $allowedStatuses, true)) {
  $sql .= " AND b.BookingStatus = ?";
  $types .= 's'; $params[]=$status;
}
if ($from !== '') {
  $sql .= " AND b.CheckInDate >= ?";
  $types .= 's'; $params[]=$from;
}
if ($to !== '') {
  $sql .= " AND b.CheckOutDate <= ?";
  $types .= 's'; $params[]=$to;
}

$sql .= " ORDER BY b.CreatedAt DESC LIMIT 300";

/* -----------------------------------------
   Fetch results
------------------------------------------ */
$bookings = [];
if ($stmt = $db->prepare($sql)) {
  if ($params) { $stmt->bind_param($types, ...$params); }
  $stmt->execute(); $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) $bookings[] = $row;
}

/* -----------------------------------------
   Quick totals (for header tiles on this page)
------------------------------------------ */
$revRes = $db->query("
  SELECT 
    SUM(CASE WHEN BookingStatus IN ('Paid','Completed','Confirmed') THEN TotalPrice ELSE 0 END) as rev,
    SUM(CASE WHEN BookingStatus='Pending' THEN 1 ELSE 0 END) as p,
    SUM(CASE WHEN BookingStatus='Confirmed' THEN 1 ELSE 0 END) as c,
    SUM(CASE WHEN BookingStatus='Paid' THEN 1 ELSE 0 END) as pd,
    SUM(CASE WHEN BookingStatus='Completed' THEN 1 ELSE 0 END) as d,
    SUM(CASE WHEN BookingStatus='Cancelled' THEN 1 ELSE 0 END) as x
  FROM bookings
");
$tot = $revRes ? $revRes->fetch_assoc() : ['rev'=>0,'p'=>0,'c'=>0,'pd'=>0,'d'=>0,'x'=>0];

function badge($s){
  $cls = strtolower($s);
  return "<span class='badge {$cls}'>".htmlspecialchars($s,ENT_QUOTES,'UTF-8')."</span>";
}
?>
<?php include __DIR__.'/includes/header.php'; ?>
<?php include __DIR__.'/includes/sidebar.php'; ?>

<main class="content">
  <h1>Bookings</h1>

  <?php if ($m=flash('ok')): ?><div class="alert" style="background:#e8fff0;border-color:#9ee6b4;color:#055a1c"><?= e($m) ?></div><?php endif; ?>
  <?php if ($m=flash('error')): ?><div class="alert"><?= e($m) ?></div><?php endif; ?>

  <div class="widgets">
    <div class="widget"><h3>💰 Revenue (Paid/Completed/Confirmed)</h3><div class="value">₵<?= number_format((float)($tot['rev'] ?? 0),2) ?></div></div>
    <div class="widget"><h3>🟡 Pending</h3><div class="value"><?= (int)($tot['p'] ?? 0) ?></div></div>
    <div class="widget"><h3>🔵 Confirmed</h3><div class="value"><?= (int)($tot['c'] ?? 0) ?></div></div>
    <div class="widget"><h3>🟢 Paid</h3><div class="value"><?= (int)($tot['pd'] ?? 0) ?></div></div>
    <div class="widget"><h3>✅ Completed</h3><div class="value"><?= (int)($tot['d'] ?? 0) ?></div></div>
    <div class="widget"><h3>❌ Cancelled</h3><div class="value"><?= (int)($tot['x'] ?? 0) ?></div></div>
  </div>

  <div class="card" style="margin-bottom:16px">
    <form method="get" class="filters">
      <label>Search (name/email/room)
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="e.g. john, 104">
      </label>
      <label>Status
        <select name="status">
          <option value="">All</option>
          <?php foreach ($allowedStatuses as $s): ?>
            <option value="<?= e($s) ?>" <?= $status===$s?'selected':'' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>From
        <input type="date" name="from" value="<?= e($from) ?>">
      </label>
      <label>To
        <input type="date" name="to" value="<?= e($to) ?>">
      </label>
      <button class="go" type="submit">Apply</button>
      <a class="btn-link" href="bookings.php">Reset</a>
    </form>
  </div>

  <div class="card">
    <div style="overflow:auto">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Room</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Guests</th>
            <th>Total (₵)</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
          <tr>
            <td><?= (int)$b['BookingID'] ?></td>
            <td><?= e($b['username'] ?? '—') ?></td>
            <td><?= e($b['email'] ?? '—') ?></td>
            <td><?= e($b['RoomNumber'] ?? $b['RoomID']) ?></td>
            <td><?= e($b['CheckInDate']) ?></td>
            <td><?= e($b['CheckOutDate']) ?></td>
            <td><?= (int)$b['NumGuests'] ?></td>
            <td><?= number_format((float)$b['TotalPrice'], 2) ?></td>
            <td><?= badge($b['BookingStatus']) ?></td>
            <td><?= e($b['CreatedAt']) ?></td>
            <td class="actions">
              <?php $id=(int)$b['BookingID']; $st=$b['BookingStatus']; ?>
              <?php if ($st==='Pending'): ?>
                <form method="post"><?php csrf_field(); ?>
                  <input type="hidden" name="action" value="status">
                  <input type="hidden" name="BookingID" value="<?= $id ?>">
                  <input type="hidden" name="new_status" value="Confirmed">
                  <button class="btn-link" type="submit">Confirm</button>
                </form>
                <form method="post" onsubmit="return confirm('Cancel booking #<?= $id ?>?');"><?php csrf_field(); ?>
                  <input type="hidden" name="action" value="status">
                  <input type="hidden" name="BookingID" value="<?= $id ?>">
                  <input type="hidden" name="new_status" value="Cancelled">
                  <button class="btn-link" type="submit" style="color:#b00020">Cancel</button>
                </form>
              <?php endif; ?>

              <?php if ($st==='Confirmed'): ?>
                <form method="post"><?php csrf_field(); ?>
                  <input type="hidden" name="action" value="status">
                  <input type="hidden" name="BookingID" value="<?= $id ?>">
                  <input type="hidden" name="new_status" value="Paid">
                  <button class="btn-link" type="submit">Mark Paid</button>
                </form>
              <?php endif; ?>

              <?php if ($st==='Paid'): ?>
                <form method="post"><?php csrf_field(); ?>
                  <input type="hidden" name="action" value="status">
                  <input type="hidden" name="BookingID" value="<?= $id ?>">
                  <input type="hidden" name="new_status" value="Completed">
                  <button class="btn-link" type="submit">Complete</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$bookings): ?>
          <tr><td colspan="11">No bookings found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>
