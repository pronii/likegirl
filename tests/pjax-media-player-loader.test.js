const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.resolve(__dirname, '..');
const headPhp = fs.readFileSync(path.join(root, 'head.php'), 'utf8');
const videoPlayerCustom = fs.readFileSync(path.join(root, 'Style/js/videoPlayerCustom.js'), 'utf8');

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
  videoPlayerCustom.includes('function setupSwipeControls'),
  'custom media player should define swipe controls for image/video navigation'
);

assert(
  videoPlayerCustom.includes('setupSwipeControls(lightbox, mediaList, newIndex') &&
    videoPlayerCustom.includes('setupSwipeControls(lightbox, mediaList, currentIndex'),
  'custom media player should bind swipe controls on open and after media switches'
);

assert(
  videoPlayerCustom.includes('touchstart') &&
    videoPlayerCustom.includes('pointerdown') &&
    videoPlayerCustom.includes('switchMedia(lightbox, mediaList[targetIndex], mediaList, targetIndex)'),
  'custom media player should support touch and mouse drag navigation through switchMedia'
);

console.log('PJAX media player loader check passed');
