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

    let loaded = 0;
    modules.forEach(src => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = function() {
            loaded++;
            if (loaded === modules.length) {
                console.log('✅ 所有模块加载完成');
                if (typeof initLoveAlbum === 'function') {
                    initLoveAlbum();
                }
            }
        };
        document.head.appendChild(script);
    });
})();
