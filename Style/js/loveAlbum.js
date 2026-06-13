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
    let modulesReady = false;
    let domReady = false;

    // 检查是否可以初始化
    function tryInit() {
        if (modulesReady && domReady) {
            console.log('✅ 所有模块加载完成 + DOM 就绪，开始初始化');
            if (typeof initLoveAlbum === 'function') {
                initLoveAlbum();
            } else {
                console.error('❌ initLoveAlbum 函数未定义');
            }
        }
    }

    // 加载所有模块
    modules.forEach(src => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = function() {
            loaded++;
            console.log(`📦 模块加载: ${src} (${loaded}/${modules.length})`);
            if (loaded === modules.length) {
                console.log('✅ 所有模块脚本加载完成');
                modulesReady = true;
                tryInit();
            }
        };
        script.onerror = function() {
            console.error(`❌ 模块加载失败: ${src}`);
        };
        document.head.appendChild(script);
    });

    // 确保 DOM 就绪
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 DOM 就绪');
            domReady = true;
            tryInit();
        });
    } else {
        // DOM 已经就绪
        console.log('📄 DOM 已就绪');
        domReady = true;
        tryInit();
    }
})();
