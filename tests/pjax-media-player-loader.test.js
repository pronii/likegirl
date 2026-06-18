const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.resolve(__dirname, '..');
const headPhp = fs.readFileSync(path.join(root, 'head.php'), 'utf8');
const loveImgPhp = fs.readFileSync(path.join(root, 'loveImg.php'), 'utf8');
const adminLoveImgSetPhp = fs.readFileSync(path.join(root, 'admin/loveImgSet.php'), 'utf8');

assert(
  headPhp.includes('function loadScriptOnce'),
  'head.php should define a reusable PJAX script loader'
);

assert(
  headPhp.includes("loadScriptOnce('Style/js/videoPlayerCustom.js', 'MediaPlayer'"),
  'PJAX album initialization should load videoPlayerCustom.js before loveAlbum.js'
);

const playerLoadIndex = headPhp.indexOf("loadScriptOnce('Style/js/videoPlayerCustom.js', 'MediaPlayer'");
const albumLoadIndex = headPhp.indexOf("script.src = 'Style/js/loveAlbum.js?t=' + Date.now();");

assert(playerLoadIndex !== -1, 'video player loader call should exist');
assert(albumLoadIndex !== -1, 'loveAlbum.js dynamic loader should still exist');
assert(
  playerLoadIndex < albumLoadIndex,
  'video player should be requested before loveAlbum.js is initialized'
);

assert(
  loveImgPhp.indexOf('Style/js/videoPlayerCustom.js') < loveImgPhp.indexOf('Style/js/loveAlbum.js'),
  'loveImg.php should load videoPlayerCustom.js before loveAlbum.js on full page loads'
);

assert(
  loveImgPhp.indexOf('Style/js/loveAlbum.js') < loveImgPhp.indexOf('Style/js/loveAlbum/lazyload.js'),
  'loveImg.php should load the lazyload helper after the album modules are requested'
);

assert(
  adminLoveImgSetPhp.includes('../Style/js/videoPlayerCustom.js'),
  'admin/loveImgSet.php should use the same custom MediaPlayer implementation as the public album'
);

console.log('PJAX media player loader check passed');
