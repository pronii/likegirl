const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.resolve(__dirname, '..');
const headPhp = fs.readFileSync(path.join(root, 'head.php'), 'utf8');

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

console.log('PJAX media player loader check passed');
