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

const trackedPhpMyAdminTempFiles = trackedFiles.filter(file => file.startsWith('phpMyAdmin4.8.5/tmp/'));
assert.strictEqual(
  trackedPhpMyAdminTempFiles.length,
  0,
  'phpMyAdmin runtime temp cache files should not be tracked in git'
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
assert(
  !loveImg.includes('MediaPlayer 已成功加载') && !loveImg.includes('MediaPlayer 未加载，请刷新页面'),
  'loveImg.php should not print MediaPlayer diagnostics to the browser console'
);

const quietConsoleFiles = [
  'Style/js/loveAlbum/album.js',
  'Style/js/loveAlbum/lazyload.js',
  'Style/js/loveAlbum/state.js',
  'Style/js/videoPlayer.js',
  'Style/js/videoPlayerCustom.js',
];

quietConsoleFiles.forEach(file => {
  const source = read(file);
  assert(
    !/console\.(log|warn)\s*\(/.test(source),
    `${file} should route debug logging through LikeGirlLog instead of raw console.log/warn`
  );
});
assert(
  read('Style/js/loveAlbum/state.js').includes('logger:'),
  'loveAlbum state should provide a quiet logger fallback for admin pages'
);
assert(
  read('Style/js/videoPlayerCustom.js').includes('const logger = typeof LikeGirlLog') &&
    read('Style/js/videoPlayer.js').includes('const logger = typeof LikeGirlLog'),
  'video player scripts should have a quiet logger fallback when global config is absent'
);
assert(
  !read('Style/js/videoPlayerCustom.js').includes('play-pause-btn'),
  'custom video player controls should not reuse the global centered play-pause-btn class'
);
assert(
  read('Style/js/videoPlayerCustom.js').includes('media-control-play-toggle'),
  'custom video player should use its own scoped play/pause control class'
);

const phpMyAdminTempPath = path.join(root, 'phpMyAdmin4.8.5/tmp');
assert(
  fs.existsSync(phpMyAdminTempPath),
  'phpMyAdmin temp directory should still exist locally after removing tracked cache files'
);

console.log('Project health check passed');
