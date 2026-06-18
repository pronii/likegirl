/**
 * 媒体播放器模块 - 支持视频和图片的 lightbox 展示
 * @author Claude Code
 * @description 提供统一的媒体播放界面，支持键盘导航、自动播放和媒体切换
 */

(function() {
    'use strict';

    const logger = typeof LikeGirlLog !== 'undefined' ? LikeGirlLog : {
        log: function() {},
        warn: function() {}
    };

    /**
     * 打开媒体灯箱（图片或视频）
     * @param {Object} mediaData - 媒体数据
     * @param {string} mediaData.type - 'image' 或 'video'
     * @param {string} mediaData.url - 媒体URL
     * @param {string} mediaData.description - 描述（可选）
     * @param {Array} mediaList - 媒体列表（用于上一个/下一个）
     * @param {number} currentIndex - 当前索引
     */
    function openMediaLightbox(mediaData, mediaList, currentIndex) {
        if (!mediaData || !mediaData.url) {
            console.error('MediaPlayer: 无效的媒体数据');
            return;
        }

        // 创建灯箱容器
        const lightbox = createLightbox();

        // 渲染媒体内容
        const contentDiv = renderMedia(lightbox, mediaData);

        // 设置控制按钮
        setupControls(lightbox, mediaList, currentIndex);

        // 设置键盘快捷键
        setupKeyboard(lightbox, mediaList, currentIndex);

        // 点击背景关闭
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox(lightbox);
            }
        });

        // 添加到页面并显示
        document.body.appendChild(lightbox);
        document.body.style.overflow = 'hidden';

        // 触发淡入动画
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                lightbox.style.opacity = '1';
            });
        });
    }

    /**
     * 创建灯箱容器
     * @returns {HTMLElement} 灯箱元素
     */
    function createLightbox() {
        const lightbox = document.createElement('div');
        lightbox.className = 'media-lightbox';
        lightbox.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.9); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;';

        return lightbox;
    }

    /**
     * 渲染媒体内容（图片或视频）
     * @param {HTMLElement} lightbox - 灯箱容器
     * @param {Object} mediaData - 媒体数据
     * @returns {HTMLElement} 内容容器
     */
    function renderMedia(lightbox, mediaData) {
        const contentDiv = document.createElement('div');
        contentDiv.className = 'lightbox-content';
        contentDiv.style.cssText = 'display: flex; flex-direction: column; align-items: center; max-width: 95vw; max-height: 90vh; gap: 15px;';

        // 根据类型渲染媒体
        if (mediaData.type === 'video') {
            const video = document.createElement('video');
            video.className = 'lightbox-video';
            video.controls = true;
            video.controlsList = 'nodownload';
            video.autoplay = true;
            video.preload = 'metadata';
            video.playsInline = true;
            video.style.cssText = 'max-width: 90vw; max-height: 80vh; display: block; border-radius: 8px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6); background: #000;';

            const source = document.createElement('source');
            source.src = mediaData.url;
            source.type = getVideoMimeType(mediaData.url);
            video.appendChild(source);

            // 错误处理
            video.addEventListener('error', function() {
                contentDiv.innerHTML = '<p style="color: white; padding: 20px; text-align: center; font-size: 16px;">⚠️ 视频加载失败<br><small style="opacity: 0.7;">请检查文件格式或网络连接</small></p>';
            });

            // 加载成功提示
            video.addEventListener('loadeddata', function() {
                logger.log('MediaPlayer: 视频加载成功', mediaData.url);
            });

            contentDiv.appendChild(video);
        } else {
            // 图片模式
            const img = document.createElement('img');
            img.className = 'lightbox-image';
            img.src = mediaData.url;
            img.alt = mediaData.description || '';
            img.style.cssText = 'max-width: 90vw; max-height: 85vh; display: block; border-radius: 8px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6); object-fit: contain;';

            // 图片加载失败处理
            img.addEventListener('error', function() {
                contentDiv.innerHTML = '<p style="color: white; padding: 20px; text-align: center; font-size: 16px;">⚠️ 图片加载失败<br><small style="opacity: 0.7;">请检查图片路径或网络连接</small></p>';
            });

            contentDiv.appendChild(img);
        }

        // 添加描述（如果有）
        if (mediaData.description) {
            const descDiv = document.createElement('div');
            descDiv.className = 'lightbox-description';
            descDiv.textContent = mediaData.description;
            descDiv.style.cssText = 'color: white; font-size: 14px; text-align: center; padding: 10px 20px; background: rgba(0, 0, 0, 0.5); border-radius: 6px; max-width: 80vw; line-height: 1.5;';
            contentDiv.appendChild(descDiv);
        }

        lightbox.appendChild(contentDiv);
        return contentDiv;
    }

    /**
     * 设置控制按钮
     * @param {HTMLElement} lightbox - 灯箱容器
     * @param {Array} mediaList - 媒体列表
     * @param {number} currentIndex - 当前索引
     */
    function setupControls(lightbox, mediaList, currentIndex) {
        const controlsDiv = document.createElement('div');
        controlsDiv.className = 'lightbox-controls';
        controlsDiv.style.cssText = 'position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); display: flex; gap: 15px; z-index: 10000;';

        // 按钮通用样式
        const buttonStyle = 'padding: 12px 24px; background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); color: white; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.3s ease; white-space: nowrap;';

        // 上一个按钮
        if (mediaList && currentIndex > 0) {
            const prevBtn = document.createElement('button');
            prevBtn.textContent = '← 上一个';
            prevBtn.style.cssText = buttonStyle;
            prevBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                closeLightbox(lightbox);
                setTimeout(function() {
                    openMediaLightbox(mediaList[currentIndex - 1], mediaList, currentIndex - 1);
                }, 300);
            });
            controlsDiv.appendChild(prevBtn);
        }

        // 关闭按钮
        const closeBtn = document.createElement('button');
        closeBtn.textContent = '✕ 关闭';
        closeBtn.style.cssText = buttonStyle;
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeLightbox(lightbox);
        });
        controlsDiv.appendChild(closeBtn);

        // 下一个按钮
        if (mediaList && currentIndex < mediaList.length - 1) {
            const nextBtn = document.createElement('button');
            nextBtn.textContent = '下一个 →';
            nextBtn.style.cssText = buttonStyle;
            nextBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                closeLightbox(lightbox);
                setTimeout(function() {
                    openMediaLightbox(mediaList[currentIndex + 1], mediaList, currentIndex + 1);
                }, 300);
            });
            controlsDiv.appendChild(nextBtn);
        }

        lightbox.appendChild(controlsDiv);
    }

    /**
     * 设置键盘快捷键
     * @param {HTMLElement} lightbox - 灯箱容器
     * @param {Array} mediaList - 媒体列表
     * @param {number} currentIndex - 当前索引
     */
    function setupKeyboard(lightbox, mediaList, currentIndex) {
        const keyHandler = function(e) {
            // ESC 关闭
            if (e.key === 'Escape') {
                closeLightbox(lightbox);
            }
            // 左箭头 - 上一个
            else if (e.key === 'ArrowLeft' && mediaList && currentIndex > 0) {
                closeLightbox(lightbox);
                setTimeout(function() {
                    openMediaLightbox(mediaList[currentIndex - 1], mediaList, currentIndex - 1);
                }, 300);
            }
            // 右箭头 - 下一个
            else if (e.key === 'ArrowRight' && mediaList && currentIndex < mediaList.length - 1) {
                closeLightbox(lightbox);
                setTimeout(function() {
                    openMediaLightbox(mediaList[currentIndex + 1], mediaList, currentIndex + 1);
                }, 300);
            }
            // 空格键 - 播放/暂停视频
            else if (e.key === ' ' || e.code === 'Space') {
                const video = lightbox.querySelector('video');
                if (video) {
                    e.preventDefault();
                    if (video.paused) {
                        video.play();
                    } else {
                        video.pause();
                    }
                }
            }
        };

        document.addEventListener('keydown', keyHandler);
        lightbox._keyHandler = keyHandler;
    }

    /**
     * 关闭灯箱并清理资源
     * @param {HTMLElement} lightbox - 灯箱容器
     */
    function closeLightbox(lightbox) {
        // 移除键盘监听
        if (lightbox._keyHandler) {
            document.removeEventListener('keydown', lightbox._keyHandler);
            lightbox._keyHandler = null;
        }

        // 停止视频播放并释放资源
        const video = lightbox.querySelector('video');
        if (video) {
            video.pause();
            video.src = '';
            video.load();
        }

        // 淡出效果
        lightbox.style.opacity = '0';

        setTimeout(function() {
            if (lightbox.parentNode) {
                document.body.removeChild(lightbox);
            }
            document.body.style.overflow = '';
        }, 300);
    }

    /**
     * 根据文件扩展名获取视频 MIME 类型
     * @param {string} url - 视频URL
     * @returns {string} MIME类型
     */
    function getVideoMimeType(url) {
        const ext = url.split('.').pop().toLowerCase().split('?')[0];
        const mimeTypes = {
            'mp4': 'video/mp4',
            'webm': 'video/webm',
            'ogg': 'video/ogg',
            'ogv': 'video/ogg',
            'avi': 'video/x-msvideo',
            'mov': 'video/quicktime',
            'mkv': 'video/x-matroska',
            'm4v': 'video/x-m4v',
            'flv': 'video/x-flv',
            'wmv': 'video/x-ms-wmv'
        };
        return mimeTypes[ext] || 'video/mp4';
    }

    // 导出全局API
    window.MediaPlayer = {
        open: openMediaLightbox
    };

    logger.log('MediaPlayer: 模块已加载');

})();
