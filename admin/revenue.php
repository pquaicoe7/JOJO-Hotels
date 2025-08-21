<?php
// admin/revenue.php
$config = require __DIR__.'/includes/config.php';
require __DIR__.'/includes/helpers.php';
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/middleware.php';

start_session($config['SESSION_NAME']);
$db = (new Database($config))->pdo();
require_auth();

/* -----------------------------------------
   Filters
------------------------------------------ */
$from   = trim($_GET['from'] ?? '');
$to     = trim($_GET['to'] ?? '');
$status = trim($_GET['status'] ?? ''); // for bookings only

$validBookingStatuses = ['Pending','Confirmed','Paid','Completed','Cancelled'];
if ($status !== '' && !in_array($status, $validBookingStatuses, true)) $status = '';

/* -----------------------------------------
   Helpers: date range & pickup datetime
------------------------------------------ */
$pickupDateExpr = "COALESCE(CONCAT(arrival,' ',pickup_time), request_date)"; // your table columns

$rangeClauseBookings = "";
$rangeTypes = ''; $rangeParams = [];
if ($from !== '') { $rangeClauseBookings .= " AND b.CheckInDate >= ?"; $rangeTypes.='s'; $rangeParams[]=$from; }
if ($to   !== '') { $rangeClauseBookings .= " AND b.CheckOutDate <= ?"; $rangeTypes.='s'; $rangeParams[]=$to; }

$rangeClausePickups  = "";
$rangeTypesP = ''; $rangeParamsP = [];
if ($from !== '') { $rangeClausePickups  .= " AND $pickupDateExpr >= ?"; $rangeTypesP.='s'; $rangeParamsP[]=$from." 00:00:00"; }
if ($to   !== '') { $rangeClausePickups  .= " AND $pickupDateExpr <= ?"; $rangeTypesP.='s'; $rangeParamsP[]=$to." 23:59:59"; }

/* -----------------------------------------
   Top summary cards
------------------------------------------ */
// Bookings revenue (paid paths)
$sqlBookRev = "SELECT COALESCE(SUM(TotalPrice),0) s FROM bookings b
               WHERE BookingStatus IN ('Paid','Completed','Confirmed')".$rangeClauseBookings;
$stmt = $db->prepare($sqlBookRev);
if ($rangeParams) $stmt->bind_param($rangeTypes, ...$rangeParams);
$stmt->execute(); $bookRev = (float)($stmt->get_result()->fetch_assoc()['s'] ?? 0);

// Pickup revenue (no status field in your table — include all)
$sqlPickRev = "SELECT COALESCE(SUM(total_price),0) s FROM pickup_requests
               WHERE 1=1 ".$rangeClausePickups;
$stmt = $db->prepare($sqlPickRev);
if ($rangeParamsP) $stmt->bind_param($rangeTypesP, ...$rangeParamsP);
$stmt->execute(); $pickRev = (float)($stmt->get_result()->fetch_assoc()['s'] ?? 0);

$totalRev = $bookRev + $pickRev;

/* -----------------------------------------
   Monthly revenue (last 12 months) UNION
------------------------------------------ */
$monthlySql = "
  SELECT ym, SUM(amount) AS revenue FROM (
    SELECT DATE_FORMAT(b.CheckInDate, '%Y-%m') AS ym, COALESCE(b.TotalPrice,0) AS amount
    FROM bookings b
    WHERE b.CheckInDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
      AND b.BookingStatus IN ('Paid','Completed','Confirmed')
    UNION ALL
    SELECT DATE_FORMAT($pickupDateExpr, '%Y-%m') AS ym, COALESCE(p.total_price,0) AS amount
    FROM pickup_requests p
    WHERE $pickupDateExpr >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
  ) x
  GROUP BY ym
  ORDER BY ym;
";
$mres = $db->query($monthlySql);
$labels=[]; $revenues=[];
if ($mres) { while ($r=$mres->fetch_assoc()) { $labels[]=$r['ym']; $revenues[]=(float)$r['revenue']; } }
if (!$labels){ $labels=[date('Y-m')]; $revenues=[0]; }

/* -----------------------------------------
   Detailed table (latest 300 rows)
------------------------------------------ */
$tableSql = "
  SELECT * FROM (
    SELECT 
      b.CreatedAt AS dt,
      'Booking' AS src,
      b.BookingID AS ref,
      u.username AS name,
      CONCAT('Room ', r.RoomNumber) AS info,
      b.TotalPrice AS amount,
      b.BookingStatus AS status
    FROM bookings b
    LEFT JOIN users u ON u.id=b.UserID
    LEFT JOIN rooms r ON r.RoomID=b.RoomID
    WHERE 1=1 ".($status!=='' ? " AND b.BookingStatus=?" : '').$rangeClauseBookings."

    UNION ALL

    SELECT 
      $pickupDateExpr AS dt,
      'Pickup' AS src,
      p.id AS ref,
      p.name AS name,
      p.location AS info,
      p.total_price AS amount,
      '—' AS status
    FROM pickup_requests p
    WHERE 1=1 ".$rangeClausePickups."
  ) t
  ORDER BY dt DESC
  LIMIT 300
";
$paramsAll = [];
$typesAll = '';
if ($status!==''){ $typesAll.='s'; $paramsAll[]=$status; }
$typesAll .= $rangeTypes.$rangeTypesP;
$paramsAll = array_merge($paramsAll, $rangeParams, $rangeParamsP);

$rows=[];
if ($stmt = $db->prepare($tableSql)) {
  if ($paramsAll) { $stmt->bind_param($typesAll, ...$paramsAll); }
  $stmt->execute(); $res=$stmt->get_result();
  while ($row=$res->fetch_assoc()) $rows[]=$row;
}

function money($n){ return '₵'.number_format((float)$n,2); }
?>
<?php include __DIR__.'/includes/header.php'; ?>
<?php include __DIR__.'/includes/sidebar.php'; ?>

<main class="content">
  <h1>Revenue</h1>

  <div class="revgrid">
    <div class="widget"><h3>💰 Total Revenue</h3><div class="value money"><?= money($totalRev) ?></div></div>
    <div class="widget"><h3>🏨 Bookings Revenue</h3><div class="value money"><?= money($bookRev) ?></div></div>
    <div class="widget"><h3>🚕 Pickups Revenue</h3><div class="value money"><?= money($pickRev) ?></div></div>
  </div>

  <div class="card" style="margin-bottom:16px">
    <form method="get" class="filters">
      <label>From <input type="date" name="from" value="<?= e($from) ?>"></label>
      <label>To <input type="date" name="to" value="<?= e($to) ?>"></label>
      <label>Status (bookings)
        <select name="status">
          <option value="">All</option>
          <?php foreach ($validBookingStatuses as $s): ?>
            <option value="<?= e($s) ?>" <?= $status===$s?'selected':'' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="go" type="submit">Apply</button>
      <a class="btn-link" href="revenue.php">Reset</a>
    </form>
  </div>

  <div class="card">
    <h3>📈 Monthly Revenue (Bookings + Pickups)</h3>
    <div class="chartwrap"><canvas id="revChart"></canvas></div>
  </div>

  <div class="card">
    <h3>Details (latest 300)</h3>
    <div style="overflow:auto">
      <table class="table">
        <thead>
          <tr>
            <th>Date</th><th>Source</th><th>Ref</th><th>Name</th><th>Info</th><th class="money">Amount</th><th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['dt']) ?></td>
              <td><?= e($r['src']) ?></td>
              <td><?= e($r['ref']) ?></td>
              <td><?= e($r['name'] ?? '—') ?></td>
              <td><?= e($r['info'] ?? '—') ?></td>
              <td class="money"><?= money($r['amount']) ?></td>
              <td><?= e($r['status']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?>
            <tr><td colspan="7">No records in this range.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>

<script>
(() => {
  const el = document.getElementById('revChart');
  if (!el || typeof Chart === 'undefined') return;
  const labels   = <?= json_encode($labels) ?>;
  const revenues = <?= json_encode($revenues) ?>;
  new Chart(el, {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Revenue (₵)', data: revenues }] },
    options: { responsive:true, maintainAspectRatio:false,
      scales:{ y:{ beginAtZero:true, title:{ display:true, text:'₵' } } }
    }
  });
})();
</script>
