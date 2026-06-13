// ========== 恋爱相册 - 模块化版本 ==========
// 加载所有模块
(function() {
    'use strict';

    console.log('🎬 loveAlbum.js 开始执行');

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
    let initAttempted = false;

    // 检查是否可以初始化
    function tryInit() {
        console.log(`🔍 tryInit 检查: modulesReady=${modulesReady}, domReady=${domReady}, initAttempted=${initAttempted}`);

        if (modulesReady && domReady && !initAttempted) {
            initAttempted = true;
            console.log('✅ 所有模块加载完成 + DOM 就绪，开始初始化');

            // 给一个小延迟确保所有元素都渲染完成
            setTimeout(() => {
                console.log('⏰ 延迟初始化执行');
                if (typeof initLoveAlbum === 'function') {
                    try {
                        initLoveAlbum();
                        console.log('✅ initLoveAlbum 执行成功');
                    } catch (e) {
                        console.error('❌ initLoveAlbum 执行出错:', e);
                        // 隐藏加载提示
                        const $loading = $('#loading');
                        if ($loading.length > 0) {
                            $loading.hide();
                            $('#albumGallery').html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">初始化失败: ' + e.message + '</div>');
                        }
                    }
                } else {
                    console.error('❌ initLoveAlbum 函数未定义');
                    // 隐藏加载提示
                    const $loading = $('#loading');
                    if ($loading.length > 0) {
                        $loading.hide();
                        $('#albumGallery').html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">初始化函数未找到</div>');
                    }
                }
            }, 100);
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
            // 即使有模块加载失败，也要隐藏loading
            if (loaded + 1 === modules.length) {
                const $loading = $('#loading');
                if ($loading.length > 0) {
                    $loading.hide();
                    $('#albumGallery').html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">模块加载失败: ' + src + '</div>');
                }
            }
        };
        document.head.appendChild(script);
    });

    // 确保 DOM 就绪
    if (document.readyState === 'loading') {
        console.log('⏳ DOM 正在加载中，注册 DOMContentLoaded 事件');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 DOM 就绪事件触发');
            domReady = true;
            tryInit();
        });
    } else {
        // DOM 已经就绪
        console.log('📄 DOM 已就绪 (readyState: ' + document.readyState + ')');
        domReady = true;
        tryInit();
    }
})();
