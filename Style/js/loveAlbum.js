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
    let initAttempted = false;

    // 检查是否可以初始化
    function tryInit() {
        if (modulesReady && domReady && !initAttempted) {
            initAttempted = true;

            // 给一个小延迟确保所有元素都渲染完成
            setTimeout(() => {
                if (typeof initLoveAlbum === 'function') {
                    try {
                        initLoveAlbum();
                    } catch (e) {
                        console.error('❌ 相册初始化失败:', e);
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
            if (loaded === modules.length) {
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
        document.addEventListener('DOMContentLoaded', function() {
            domReady = true;
            tryInit();
        });
    } else {
        domReady = true;
        tryInit();
    }
})();
