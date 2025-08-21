<?php
// admin/rooms.php
$config = require __DIR__.'/includes/config.php';
require __DIR__.'/includes/helpers.php';
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/middleware.php';

start_session($config['SESSION_NAME']);
$db = (new Database($config))->pdo();
require_auth();

/* -----------------------------------------
   Fetch room types for the dropdown (optional)
------------------------------------------ */
$roomTypes = [];
$rt = $db->query("SELECT RoomTypeID, TypeName FROM roomtypes ORDER BY TypeName");
if ($rt) { while ($row = $rt->fetch_assoc()) { $roomTypes[] = $row; } }

/* -----------------------------------------
   Are we editing?
------------------------------------------ */
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editingRoom = null;
if ($editId) {
  $stmt = $db->prepare("SELECT * FROM rooms WHERE RoomID = ? LIMIT 1");
  $stmt->bind_param("i", $editId);
  $stmt->execute();
  $editingRoom = $stmt->get_result()->fetch_assoc();
}

/* -----------------------------------------
   Create
------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  verify_csrf();

  $roomNumber = trim($_POST['RoomNumber']);
  $roomTypeID = trim($_POST['RoomTypeID']);
  $roomTypeID = ($roomTypeID === '' || $roomTypeID === '0') ? null : (int)$roomTypeID;
  $floorNumber= (int)$_POST['FloorNumber'];
  $status     = trim($_POST['Status']);
  $guests     = (int)$_POST['Guests'];
  $price      = (float)$_POST['Price'];
  $imageURL   = trim($_POST['ImageURL']);

  $stmt = $db->prepare("INSERT INTO rooms (RoomNumber, RoomTypeID, FloorNumber, Status, Guests, Price, ImageURL) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("siisids", $roomNumber, $roomTypeID, $floorNumber, $status, $guests, $price, $imageURL);

  if ($stmt->execute()) {
    flash('ok', 'Room created successfully');
  } else {
    flash('error', 'Failed to create room: '.$stmt->error);
  }
  header("Location: rooms.php"); exit;
}

/* -----------------------------------------
   Update
------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
  verify_csrf();

  $id         = (int)$_POST['RoomID'];
  $roomNumber = trim($_POST['RoomNumber']);
  $roomTypeID = trim($_POST['RoomTypeID']);
  $roomTypeID = ($roomTypeID === '' || $roomTypeID === '0') ? null : (int)$roomTypeID;
  $floorNumber= (int)$_POST['FloorNumber'];
  $status     = trim($_POST['Status']);
  $guests     = (int)$_POST['Guests'];
  $price      = (float)$_POST['Price'];
  $imageURL   = trim($_POST['ImageURL']);

  $stmt = $db->prepare("
    UPDATE rooms
    SET RoomNumber=?, RoomTypeID=?, FloorNumber=?, Status=?, Guests=?, Price=?, ImageURL=?
    WHERE RoomID=?
  ");
  $stmt->bind_param("siisidsi", $roomNumber, $roomTypeID, $floorNumber, $status, $guests, $price, $imageURL, $id);

  if ($stmt->execute()) {
    flash('ok', "Room #{$id} updated");
  } else {
    flash('error', 'Update failed: '.$stmt->error);
  }
  header("Location: rooms.php"); exit;
}

/* -----------------------------------------
   Delete (block if room has bookings)
------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
  verify_csrf();
  $id = (int)$_POST['RoomID'];

  // Check if any bookings reference this room
  $stmt = $db->prepare("SELECT COUNT(*) c FROM bookings WHERE RoomID = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $has = $stmt->get_result()->fetch_assoc()['c'] ?? 0;

  if ($has > 0) {
    flash('error', "Cannot delete Room #$id — it has $has booking(s).");
    header("Location: rooms.php"); exit;
  }

  // Safe to delete
  $stmt = $db->prepare("DELETE FROM rooms WHERE RoomID = ? LIMIT 1");
  $stmt->bind_param("i", $id);
  if ($stmt->execute()) {
    flash('ok', "Room #$id deleted");
  } else {
    flash('error', "Delete failed: ".$stmt->error);
  }
  header("Location: rooms.php"); exit;
}

/* -----------------------------------------
   Fetch rooms to list
------------------------------------------ */
$rooms = [];
$q = $db->query("SELECT * FROM rooms ORDER BY RoomID DESC");
if ($q) { while ($row = $q->fetch_assoc()) { $rooms[] = $row; } }

?>
<?php include __DIR__.'/includes/header.php'; ?>
<?php include __DIR__.'/includes/sidebar.php'; ?>

<main class="content">
  <h1>Rooms</h1>

  <?php if ($m = flash('ok')): ?>
    <div class="alert" style="background:#e8fff0;border-color:#9ee6b4;color:#055a1c"><?= e($m) ?></div>
  <?php endif; ?>
  <?php if ($m = flash('error')): ?>
    <div class="alert"><?= e($m) ?></div>
  <?php endif; ?>

  <?php if (!$roomTypes): ?>
    <div class="alert">No room types found. You can still create rooms without a type, or add types in the <b>roomtypes</b> table.</div>
  <?php endif; ?>

  <?php if ($editingRoom): ?>
    <!-- EDIT FORM -->
    <div class="card" style="margin-bottom:16px">
      <h3>Edit Room #<?= e($editingRoom['RoomID']) ?></h3>
      <form method="post">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="RoomID" value="<?= e($editingRoom['RoomID']) ?>">

        <label>Room Number
          <input type="text" name="RoomNumber" value="<?= e($editingRoom['RoomNumber']) ?>" required>
        </label>

        <label>Room Type
          <select name="RoomTypeID">
            <option value="">-- No type --</option>
            <?php
              $currentType = $editingRoom['RoomTypeID'];
              foreach ($roomTypes as $t):
                $sel = ($currentType == $t['RoomTypeID']) ? 'selected' : '';
            ?>
              <option value="<?= (int)$t['RoomTypeID'] ?>" <?= $sel ?>><?= e($t['TypeName']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>Floor Number
          <input type="number" name="FloorNumber" value="<?= e($editingRoom['FloorNumber']) ?>" required>
        </label>

        <label>Status
          <select name="Status">
            <?php
              $opts = ['Available','Unavailable','Maintenance'];
              $cur  = $editingRoom['Status'];
              foreach ($opts as $o) {
                $sel = ($cur === $o) ? 'selected' : '';
                echo "<option value=\"".e($o)."\" $sel>".e($o)."</option>";
              }
            ?>
          </select>
        </label>

        <label>Guests
          <input type="number" name="Guests" value="<?= e($editingRoom['Guests']) ?>" required>
        </label>

        <label>Price
          <input type="number" step="0.01" name="Price" value="<?= e($editingRoom['Price']) ?>" required>
        </label>

        <label>Image URL
          <input type="text" name="ImageURL" value="<?= e($editingRoom['ImageURL']) ?>">
        </label>

        <button type="submit">Save Changes</button>
        <a href="rooms.php" style="margin-left:8px">Cancel</a>
      </form>
    </div>
  <?php else: ?>
    <!-- CREATE FORM -->
    <div class="card" style="margin-bottom:16px">
      <h3>Create Room</h3>
      <form method="post">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="create">

        <label>Room Number <input type="text" name="RoomNumber" required></label>

        <label>Room Type
          <select name="RoomTypeID">
            <option value="">-- Select type (or leave empty) --</option>
            <?php foreach ($roomTypes as $t): ?>
              <option value="<?= (int)$t['RoomTypeID'] ?>"><?= e($t['TypeName']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>Floor Number <input type="number" name="FloorNumber" required></label>

        <label>Status
          <select name="Status">
            <option value="Available">Available</option>
            <option value="Unavailable">Unavailable</option>
            <option value="Maintenance">Maintenance</option>
          </select>
        </label>

        <label>Guests <input type="number" name="Guests" required></label>

        <label>Price <input type="number" step="0.01" name="Price" required></label>

        <label>Image URL <input type="text" name="ImageURL" placeholder="images/room1.jpg"></label>

        <button type="submit">Add Room</button>
      </form>
    </div>
  <?php endif; ?>

  <div class="card">
    <h3>All Rooms</h3>
    <div style="overflow:auto">
      <table width="100%" cellpadding="8" cellspacing="0">
        <thead>
          <tr>
            <th>ID</th><th>Room #</th><th>TypeID</th><th>Floor</th><th>Status</th><th>Guests</th><th>Price</th><th>Image</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rooms as $r): ?>
            <tr>
              <td><?= e($r['RoomID']) ?></td>
              <td><?= e($r['RoomNumber']) ?></td>
              <td><?= e($r['RoomTypeID']) ?></td>
              <td><?= e($r['FloorNumber']) ?></td>
              <td><?= e($r['Status']) ?></td>
              <td><?= e($r['Guests']) ?></td>
              <td>₵<?= number_format((float)$r['Price'], 2) ?></td>
              <td><?php if ($r['ImageURL']): ?><img src="<?= e($r['ImageURL']) ?>" alt="" style="width:60px"><?php endif; ?></td>
              <td>
                <a href="rooms.php?edit=<?= (int)$r['RoomID'] ?>">Edit</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Delete Room #<?= (int)$r['RoomID'] ?>? This cannot be undone.');">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="RoomID" value="<?= (int)$r['RoomID'] ?>">
                  <button type="submit" style="background:none;border:none;color:#b00020;cursor:pointer">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>
