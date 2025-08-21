<?php
// admin/roomtypes.php
$config = require __DIR__.'/includes/config.php';
require __DIR__.'/includes/helpers.php';
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/middleware.php';

start_session($config['SESSION_NAME']);
$db = (new Database($config))->pdo();
require_auth();

/* -----------------------------------------
   Are we editing?
------------------------------------------ */
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editingType = null;
if ($editId) {
  $stmt = $db->prepare("SELECT * FROM roomtypes WHERE RoomTypeID = ? LIMIT 1");
  $stmt->bind_param("i", $editId);
  $stmt->execute();
  $editingType = $stmt->get_result()->fetch_assoc();
}

/* -----------------------------------------
   Create
------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  verify_csrf();
  $name = trim($_POST['TypeName']);
  if ($name === '') { flash('error','Type name is required'); header('Location: roomtypes.php'); exit; }

  $stmt = $db->prepare("INSERT INTO roomtypes (TypeName) VALUES (?)");
  $stmt->bind_param("s", $name);
  if ($stmt->execute()) flash('ok','Room type created');
  else flash('error','Create failed: '.$stmt->error);
  header('Location: roomtypes.php'); exit;
}

/* -----------------------------------------
   Update
------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
  verify_csrf();
  $id   = (int)$_POST['RoomTypeID'];
  $name = trim($_POST['TypeName']);
  if ($name === '') { flash('error','Type name is required'); header('Location: roomtypes.php?edit='.$id); exit; }

  $stmt = $db->prepare("UPDATE roomtypes SET TypeName=? WHERE RoomTypeID=?");
  $stmt->bind_param("si", $name, $id);
  if ($stmt->execute()) flash('ok',"Updated type #$id");
  else flash('error','Update failed: '.$stmt->error);
  header('Location: roomtypes.php'); exit;
}

/* -----------------------------------------
   Delete (blocked if in use by rooms)
------------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
  verify_csrf();
  $id = (int)$_POST['RoomTypeID'];

  // Check if any rooms reference this type
  $stmt = $db->prepare("SELECT COUNT(*) c FROM rooms WHERE RoomTypeID = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $has = $stmt->get_result()->fetch_assoc()['c'] ?? 0;

  if ($has > 0) {
    flash('error', "Cannot delete — $has room(s) use this type.");
    header('Location: roomtypes.php'); exit;
  }

  $stmt = $db->prepare("DELETE FROM roomtypes WHERE RoomTypeID = ? LIMIT 1");
  $stmt->bind_param("i", $id);
  if ($stmt->execute()) flash('ok',"Type #$id deleted");
  else flash('error','Delete failed: '.$stmt->error);
  header('Location: roomtypes.php'); exit;
}

/* -----------------------------------------
   List types
------------------------------------------ */
$types = [];
$q = $db->query("SELECT * FROM roomtypes ORDER BY TypeName");
if ($q) while ($row = $q->fetch_assoc()) $types[] = $row;

?>
<?php include __DIR__.'/includes/header.php'; ?>
<?php include __DIR__.'/includes/sidebar.php'; ?>

<main class="content">
  <h1>Room Types</h1>

  <?php if ($m = flash('ok')): ?>
    <div class="alert" style="background:#e8fff0;border-color:#9ee6b4;color:#055a1c"><?= e($m) ?></div>
  <?php endif; ?>
  <?php if ($m = flash('error')): ?>
    <div class="alert"><?= e($m) ?></div>
  <?php endif; ?>

  <?php if ($editingType): ?>
    <div class="card" style="margin-bottom:16px">
      <h3>Edit Type #<?= e($editingType['RoomTypeID']) ?></h3>
      <form method="post">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="RoomTypeID" value="<?= (int)$editingType['RoomTypeID'] ?>">
        <label>Type Name
          <input type="text" name="TypeName" value="<?= e($editingType['TypeName']) ?>" required>
        </label>
        <button type="submit">Save Changes</button>
        <a href="roomtypes.php" style="margin-left:8px">Cancel</a>
      </form>
    </div>
  <?php else: ?>
    <div class="card" style="margin-bottom:16px">
      <h3>Create Type</h3>
      <form method="post">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="create">
        <label>Type Name
          <input type="text" name="TypeName" required placeholder="Deluxe, Standard, Suite...">
        </label>
        <button type="submit">Add Type</button>
      </form>
    </div>
  <?php endif; ?>

  <div class="card">
    <h3>All Room Types</h3>
    <div style="overflow:auto">
      <table width="100%" cellpadding="8" cellspacing="0">
        <thead>
          <tr><th>ID</th><th>Type Name</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php foreach ($types as $t): ?>
            <tr>
              <td><?= (int)$t['RoomTypeID'] ?></td>
              <td><?= e($t['TypeName']) ?></td>
              <td>
                <a href="roomtypes.php?edit=<?= (int)$t['RoomTypeID'] ?>">Edit</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Delete this type?');">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="RoomTypeID" value="<?= (int)$t['RoomTypeID'] ?>">
                  <button type="submit" style="background:none;border:none;color:#b00020;cursor:pointer">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$types): ?>
            <tr><td colspan="3">No types yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>
