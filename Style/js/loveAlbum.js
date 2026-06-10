// ========== 恋爱相册 - 模块化版本 ==========
// 加载所有模块
(function() {
    'use strict';

    // 加载模块脚本
    const modules = [
        'Style/js/loveAlbum/state.js',
        'Style/js/loveAlbum/album.js',
        'Style/js/loveAlbum/selection.js',
        'Style/js/loveAlbum/drag.js',
        'Style/js/loveAlbum/main.js'
    ];

    modules.forEach(src => {
        const script = document.createElement('script');
        script.src = src;
        document.head.appendChild(script);
    });
})();
