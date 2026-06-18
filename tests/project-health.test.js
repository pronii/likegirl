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

const { execFileSync } = require('child_process');
const trackedFiles = execFileSync('git', ['ls-files'], {
  cwd: root,
  encoding: 'utf8',
}).split(/\r?\n/).filter(Boolean);

const trackedRuntimeFiles = trackedFiles.filter(file => file.startsWith('.superpowers/'));
assert.strictEqual(
  trackedRuntimeFiles.length,
  0,
  '.superpowers runtime files should not be tracked in git'
);

const trackedUploadFiles = trackedFiles.filter(file =>
  file.startsWith('uploads/images/') ||
  file.startsWith('uploads/thumbs/') ||
  file.startsWith('uploads/videos/') ||
  file.startsWith('uploads/video_thumbs/')
).filter(file => !file.endsWith('/.gitkeep'));

assert.strictEqual(
  trackedUploadFiles.length,
  0,
  'uploaded user media files should not be tracked in git'
);

const loveImg = read('loveImg.php');
assert(
  loveImg.includes('filemtime('),
  'loveImg.php should use filemtime for video player cache busting'
);
assert(
  !loveImg.includes('$videoPlayerVersion = time();') && !loveImg.includes('rand();'),
  'loveImg.php should not force video player cache busting on every request'
);

console.log('Project health check passed');
