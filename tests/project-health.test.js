const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.resolve(__dirname, '..');

function read(relativePath) {
  return fs.readFileSync(path.join(root, relativePath), 'utf8');
}

const debugPhotosPath = path.join(root, 'admin/debug_photos.php');
if (fs.existsSync(debugPhotosPath)) {
  const debugPhotos = fs.readFileSync(debugPhotosPath, 'utf8');
  assert(
    debugPhotos.includes("$_SESSION['loginadmin']") || debugPhotos.includes('$_SESSION["loginadmin"]'),
    'admin/debug_photos.php should require an admin session before showing diagnostics'
  );
  assert(
    debugPhotos.includes('header("Location:login.php")') || debugPhotos.includes("header('Location:login.php')"),
    'admin/debug_photos.php should redirect unauthenticated users to login.php'
  );
}

const batchUpload = read('admin/batchUploadLocalPost.php');
assert(
  !batchUpload.includes("ini_set('display_errors', 1)") && !batchUpload.includes('ini_set("display_errors", 1)'),
  'batchUploadLocalPost.php should not display PHP errors in JSON responses'
);
assert(
  batchUpload.includes("ini_set('display_errors', 0)") || batchUpload.includes('ini_set("display_errors", 0)'),
  'batchUploadLocalPost.php should explicitly disable display_errors'
);

const emptyAsset = path.join(root, 'admin/assets/images/file-searching.svg');
assert(
  !fs.existsSync(emptyAsset),
  'unused empty file-searching.svg should be removed'
);

console.log('Project health check passed');
