/**
 * 增强版视频播放器 - 带自定义控件
 * 提供更大、更美观的进度条和控制按钮
 */

(function() {
    'use strict';

    /**
     * 切换到另一个媒体（无缝切换，不关闭UI）
     * @param {HTMLElement} lightbox - 当前灯箱
     * @param {Object} mediaData - 新的媒体数据
     * @param {Array} mediaList - 媒体列表
     * @param {number} newIndex - 新的索引
     */
    function switchMedia(lightbox, mediaData, mediaList, newIndex) {
        console.log('🔄 切换媒体到索引:', newIndex);

        // 停止当前视频
        const oldVideo = lightbox.querySelector('video');
        if (oldVideo) {
            oldVideo.pause();
            oldVideo.src = '';
        }

        // 找到内容容器并清空
        const oldContent = lightbox.querySelector('.lightbox-content');
        if (oldContent) {
            oldContent.remove();
        }

        // 移除旧的键盘监听
        if (lightbox._keyHandler) {
            document.removeEventListener('keydown', lightbox._keyHandler);
            lightbox._keyHandler = null;
        }

        // 移除旧的滑动监听
        if (lightbox._swipeCleanup) {
            lightbox._swipeCleanup();
            lightbox._swipeCleanup = null;
        }

        // 移除旧的导航按钮
        const oldNavButtons = lightbox.querySelectorAll('button');
        oldNavButtons.forEach(function(btn) {
            btn.remove();
        });

        // 渲染新的媒体内容
        const contentDiv = renderMedia(lightbox, mediaData);

        // 重新设置导航按钮
        setupNavControls(lightbox, mediaList, newIndex);

        // 重新设置键盘监听
        setupKeyboard(lightbox, mediaList, newIndex, contentDiv);

        // 重新设置滑动监听
        setupSwipeControls(lightbox, mediaList, newIndex, contentDiv);
    }

    function openMediaLightbox(mediaData, mediaList, currentIndex) {
        if (!mediaData || !mediaData.url) {
            console.error('MediaPlayer: 无效的媒体数据');
            return;
        }

        const lightbox = createLightbox();
        const contentDiv = renderMedia(lightbox, mediaData);
        setupNavControls(lightbox, mediaList, currentIndex);
        setupKeyboard(lightbox, mediaList, currentIndex, contentDiv);
        setupSwipeControls(lightbox, mediaList, currentIndex, contentDiv);

        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox(lightbox);
            }
        });

        document.body.appendChild(lightbox);
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                lightbox.style.opacity = '1';
            });
        });
    }

    function createLightbox() {
        const lightbox = document.createElement('div');
        lightbox.className = 'media-lightbox';
        lightbox.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.92); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;';
        return lightbox;
    }

    function renderMedia(lightbox, mediaData) {
        const contentDiv = document.createElement('div');
        contentDiv.className = 'lightbox-content';
        contentDiv.style.cssText = 'display: flex; flex-direction: column; align-items: center; width: 90vw; max-height: 85vh; gap: 0;';

        if (mediaData.type === 'video') {
            const videoContainer = document.createElement('div');
            videoContainer.style.cssText = 'position: relative; width: 100%; display: flex; flex-direction: column; align-items: center;';

            const video = document.createElement('video');
            video.className = 'lightbox-video';
            video.controls = false; // 禁用原生控件，使用自定义控件
            video.autoplay = true;
            video.preload = 'metadata';
            video.playsInline = true;
            video.style.cssText = 'width: 100%; max-height: 75vh; display: block; border-radius: 8px 8px 0 0; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6); background: #000; filter: brightness(1.0);';

            const source = document.createElement('source');
            source.src = mediaData.url;
            source.type = getVideoMimeType(mediaData.url);
            video.appendChild(source);

            video.addEventListener('error', function() {
                contentDiv.innerHTML = '<p style="color: white; padding: 20px; text-align: center; font-size: 16px;">⚠️ 视频加载失败<br><small style="opacity: 0.7;">请检查文件格式或网络连接</small></p>';
            });

            video.addEventListener('loadeddata', function() {
                console.log('MediaPlayer: 视频加载成功', mediaData.url);
            });

            // 点击视频播放/暂停
            video.addEventListener('click', function(e) {
                e.stopPropagation();
                if (video.paused) {
                    video.play();
                    const playBtn = videoContainer.querySelector('.media-control-play-toggle');
                    if (playBtn) playBtn.innerHTML = '⏸️';
                } else {
                    video.pause();
                    const playBtn = videoContainer.querySelector('.media-control-play-toggle');
                    if (playBtn) playBtn.innerHTML = '▶️';
                }
            });

            videoContainer.appendChild(video);

            // 创建自定义视频控件
            const customControls = createCustomControls(video);
            videoContainer.appendChild(customControls);

            contentDiv.appendChild(videoContainer);
        } else {
            // 图片模式
            const img = document.createElement('img');
            img.className = 'lightbox-image';
            img.src = mediaData.url;
            img.alt = mediaData.description || '';
            img.style.cssText = 'max-width: 90vw; max-height: 85vh; display: block; border-radius: 8px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6); object-fit: contain;';

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
            descDiv.style.cssText = 'color: white; font-size: 14px; text-align: center; padding: 15px 20px; background: rgba(0, 0, 0, 0.6); border-radius: 0 0 8px 8px; width: 100%; box-sizing: border-box; line-height: 1.6; margin-top: -1px;';
            contentDiv.appendChild(descDiv);
        }

        lightbox.appendChild(contentDiv);
        return contentDiv;
    }

    /**
     * 创建自定义视频控件
     */
    function createCustomControls(video) {
        const controlsBar = document.createElement('div');
        controlsBar.className = 'custom-video-controls';
        controlsBar.style.cssText = 'width: 100%; background: linear-gradient(to top, rgba(0,0,0,0.85), rgba(0,0,0,0.65)); padding: 15px 20px; box-sizing: border-box; display: flex; flex-direction: column; gap: 12px; border-radius: 0 0 8px 8px; margin-top: -1px;';

        // 进度条容器
        const progressContainer = document.createElement('div');
        progressContainer.style.cssText = 'width: 100%; height: 28px; display: flex; align-items: center; cursor: pointer; position: relative;';

        const progressBg = document.createElement('div');
        progressBg.style.cssText = 'width: 100%; height: 6px; background: rgba(255, 255, 255, 0.25); border-radius: 3px; position: relative; overflow: hidden;';

        const progressFilled = document.createElement('div');
        progressFilled.className = 'progress-filled';
        progressFilled.style.cssText = 'height: 100%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); width: 0%; transition: width 0.1s linear; border-radius: 3px; position: relative;';

        // 进度条滑块
        const progressThumb = document.createElement('div');
        progressThumb.style.cssText = 'position: absolute; right: -8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; background: white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.3); opacity: 0; transition: opacity 0.2s;';
        progressFilled.appendChild(progressThumb);

        progressBg.appendChild(progressFilled);
        progressContainer.appendChild(progressBg);

        // 鼠标悬停显示滑块
        progressContainer.addEventListener('mouseenter', function() {
            progressThumb.style.opacity = '1';
            progressBg.style.height = '8px';
        });
        progressContainer.addEventListener('mouseleave', function() {
            progressThumb.style.opacity = '0';
            progressBg.style.height = '6px';
        });

        // 点击进度条跳转
        progressContainer.addEventListener('click', function(e) {
            const rect = progressBg.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            video.currentTime = percent * video.duration;
        });

        // 更新进度条
        video.addEventListener('timeupdate', function() {
            const percent = (video.currentTime / video.duration) * 100;
            progressFilled.style.width = percent + '%';
            updateTimeDisplay();
        });

        // 控制按钮容器
        const buttonsContainer = document.createElement('div');
        buttonsContainer.style.cssText = 'display: flex; align-items: center; gap: 15px; justify-content: space-between;';

        // 左侧按钮组
        const leftButtons = document.createElement('div');
        leftButtons.style.cssText = 'display: flex; align-items: center; gap: 15px;';

        // 播放/暂停按钮
        const playBtn = document.createElement('button');
        playBtn.className = 'media-control-play-toggle';
        playBtn.innerHTML = '⏸️';
        playBtn.style.cssText = 'background: transparent; border: none; color: white; font-size: 24px; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;';
        playBtn.addEventListener('click', function() {
            if (video.paused) {
                video.play();
                playBtn.innerHTML = '⏸️';
            } else {
                video.pause();
                playBtn.innerHTML = '▶️';
            }
        });
        playBtn.addEventListener('mouseenter', function() {
            playBtn.style.transform = 'scale(1.1)';
        });
        playBtn.addEventListener('mouseleave', function() {
            playBtn.style.transform = 'scale(1)';
        });

        // 时间显示
        const timeDisplay = document.createElement('span');
        timeDisplay.style.cssText = 'color: white; font-size: 14px; font-family: monospace; min-width: 120px;';
        timeDisplay.textContent = '0:00 / 0:00';

        function updateTimeDisplay() {
            const current = formatTime(video.currentTime);
            const total = formatTime(video.duration);
            timeDisplay.textContent = current + ' / ' + total;
        }

        video.addEventListener('loadedmetadata', updateTimeDisplay);

        leftButtons.appendChild(playBtn);
        leftButtons.appendChild(timeDisplay);

        // 右侧按钮组
        const rightButtons = document.createElement('div');
        rightButtons.style.cssText = 'display: flex; align-items: center; gap: 12px;';

        // 音量按钮
        const volumeBtn = document.createElement('button');
        volumeBtn.innerHTML = '🔊';
        volumeBtn.style.cssText = 'background: transparent; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;';
        volumeBtn.addEventListener('click', function() {
            video.muted = !video.muted;
            volumeBtn.innerHTML = video.muted ? '🔇' : '🔊';
        });

        // 全屏按钮
        const fullscreenBtn = document.createElement('button');
        fullscreenBtn.innerHTML = '⛶';
        fullscreenBtn.style.cssText = 'background: transparent; border: none; color: white; font-size: 20px; cursor: pointer; padding: 0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;';
        fullscreenBtn.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                video.requestFullscreen().catch(function(err) {
                    console.log('全屏失败:', err);
                });
            } else {
                document.exitFullscreen();
            }
        });

        rightButtons.appendChild(volumeBtn);
        rightButtons.appendChild(fullscreenBtn);

        buttonsContainer.appendChild(leftButtons);
        buttonsContainer.appendChild(rightButtons);

        controlsBar.appendChild(progressContainer);
        controlsBar.appendChild(buttonsContainer);

        return controlsBar;
    }

    /**
     * 格式化时间
     */
    function formatTime(seconds) {
        if (isNaN(seconds) || seconds === Infinity) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    /**
     * 设置导航控制按钮（上一个/下一个在左右两侧）
     */
    function setupNavControls(lightbox, mediaList, currentIndex) {
        // 调试信息
        console.log('📊 导航按钮信息:', {
            hasMediaList: !!mediaList,
            mediaListLength: mediaList ? mediaList.length : 0,
            currentIndex: currentIndex,
            showPrev: mediaList && currentIndex > 0,
            showNext: mediaList && currentIndex < mediaList.length - 1
        });

        const buttonStyle = 'padding: 16px 24px; background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(10px); color: white; border: 2px solid rgba(255, 255, 255, 0.3); border-radius: 50%; cursor: pointer; font-size: 28px; font-weight: bold; transition: all 0.3s ease; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);';

        const buttonHoverStyle = 'background: rgba(0, 0, 0, 0.8); border-color: rgba(255, 255, 255, 0.6); transform: scale(1.1); box-shadow: 0 6px 20px rgba(0, 0, 0, 0.6);';

        console.log('🔘 创建导航按钮');

        // 左侧"上一个"按钮
        if (mediaList && currentIndex > 0) {
            console.log('➕ 添加"上一个"按钮（左侧）');
            const prevBtn = document.createElement('button');
            prevBtn.innerHTML = '‹'; // 使用简洁的左箭头符号
            prevBtn.style.cssText = buttonStyle + 'position: fixed; left: 30px; top: 50%; transform: translateY(-50%); z-index: 10001;';
            prevBtn.addEventListener('mouseenter', function() {
                prevBtn.style.cssText = buttonStyle + buttonHoverStyle + 'position: fixed; left: 30px; top: 50%; transform: translateY(-50%) scale(1.1); z-index: 10001;';
            });
            prevBtn.addEventListener('mouseleave', function() {
                prevBtn.style.cssText = buttonStyle + 'position: fixed; left: 30px; top: 50%; transform: translateY(-50%); z-index: 10001;';
            });
            prevBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                goToMedia(lightbox, mediaList, currentIndex - 1);
            });
            lightbox.appendChild(prevBtn);
        }

        // 右侧"下一个"按钮
        if (mediaList && currentIndex < mediaList.length - 1) {
            console.log('➕ 添加"下一个"按钮（右侧）');
            const nextBtn = document.createElement('button');
            nextBtn.innerHTML = '›'; // 使用简洁的右箭头符号
            nextBtn.style.cssText = buttonStyle + 'position: fixed; right: 30px; top: 50%; transform: translateY(-50%); z-index: 10001;';
            nextBtn.addEventListener('mouseenter', function() {
                nextBtn.style.cssText = buttonStyle + buttonHoverStyle + 'position: fixed; right: 30px; top: 50%; transform: translateY(-50%) scale(1.1); z-index: 10001;';
            });
            nextBtn.addEventListener('mouseleave', function() {
                nextBtn.style.cssText = buttonStyle + 'position: fixed; right: 30px; top: 50%; transform: translateY(-50%); z-index: 10001;';
            });
            nextBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                goToMedia(lightbox, mediaList, currentIndex + 1);
            });
            lightbox.appendChild(nextBtn);
        }

        console.log('✅ 导航按钮已添加到灯箱');
    }

    function goToMedia(lightbox, mediaList, targetIndex) {
        if (!mediaList || targetIndex < 0 || targetIndex >= mediaList.length) {
            return false;
        }

        switchMedia(lightbox, mediaList[targetIndex], mediaList, targetIndex);
        return true;
    }

    function setupSwipeControls(lightbox, mediaList, currentIndex, contentDiv) {
        if (!mediaList || mediaList.length <= 1) {
            return;
        }

        const swipeTarget = contentDiv;
        let startX = 0;
        let startY = 0;
        let tracking = false;
        let pointerId = null;

        function startTracking(x, y, id) {
            startX = x;
            startY = y;
            pointerId = id;
            tracking = true;
        }

        function finishTracking(x, y) {
            if (!tracking) return;

            const deltaX = x - startX;
            const deltaY = y - startY;
            tracking = false;
            pointerId = null;

            if (Math.abs(deltaX) < 60 || Math.abs(deltaX) < Math.abs(deltaY) * 1.2) {
                return;
            }

            if (deltaX > 0) {
                goToMedia(lightbox, mediaList, currentIndex - 1);
            } else {
                goToMedia(lightbox, mediaList, currentIndex + 1);
            }
        }

        function onTouchStart(e) {
            if (e.target.closest('.custom-video-controls')) {
                return;
            }
            if (e.touches.length !== 1) return;
            startTracking(e.touches[0].clientX, e.touches[0].clientY, null);
        }

        function onTouchEnd(e) {
            if (!tracking || e.changedTouches.length === 0) return;
            finishTracking(e.changedTouches[0].clientX, e.changedTouches[0].clientY);
        }

        function onPointerDown(e) {
            if (e.pointerType === 'touch' || e.button !== 0 || e.target.closest('.custom-video-controls')) {
                return;
            }
            startTracking(e.clientX, e.clientY, e.pointerId);
        }

        function onPointerUp(e) {
            if (!tracking || (pointerId !== null && e.pointerId !== pointerId)) return;
            finishTracking(e.clientX, e.clientY);
        }

        function onPointerCancel(e) {
            if (pointerId !== null && e.pointerId !== pointerId) return;
            tracking = false;
            pointerId = null;
        }

        swipeTarget.addEventListener('touchstart', onTouchStart, { passive: true });
        swipeTarget.addEventListener('touchend', onTouchEnd);
        swipeTarget.addEventListener('pointerdown', onPointerDown);
        swipeTarget.addEventListener('pointerup', onPointerUp);
        swipeTarget.addEventListener('pointercancel', onPointerCancel);

        lightbox._swipeCleanup = function() {
            swipeTarget.removeEventListener('touchstart', onTouchStart);
            swipeTarget.removeEventListener('touchend', onTouchEnd);
            swipeTarget.removeEventListener('pointerdown', onPointerDown);
            swipeTarget.removeEventListener('pointerup', onPointerUp);
            swipeTarget.removeEventListener('pointercancel', onPointerCancel);
        };
    }

    /**
     * 设置键盘快捷键
     */
    function setupKeyboard(lightbox, mediaList, currentIndex, contentDiv) {
        const keyHandler = function(e) {
            const video = contentDiv.querySelector('video');

            if (e.key === 'Escape') {
                closeLightbox(lightbox);
            } else if (e.key === 'ArrowLeft' && mediaList && currentIndex > 0) {
                e.preventDefault();
                goToMedia(lightbox, mediaList, currentIndex - 1);
            } else if (e.key === 'ArrowRight' && mediaList && currentIndex < mediaList.length - 1) {
                e.preventDefault();
                goToMedia(lightbox, mediaList, currentIndex + 1);
            } else if (e.key === ' ' || e.code === 'Space') {
                if (video) {
                    e.preventDefault();
                    if (video.paused) {
                        video.play();
                        const playBtn = contentDiv.querySelector('.media-control-play-toggle');
                        if (playBtn) playBtn.innerHTML = '⏸️';
                    } else {
                        video.pause();
                        const playBtn = contentDiv.querySelector('.media-control-play-toggle');
                        if (playBtn) playBtn.innerHTML = '▶️';
                    }
                }
            }
        };

        document.addEventListener('keydown', keyHandler);
        lightbox._keyHandler = keyHandler;
    }

    /**
     * 关闭灯箱并清理资源
     */
    function closeLightbox(lightbox) {
        if (lightbox._keyHandler) {
            document.removeEventListener('keydown', lightbox._keyHandler);
            lightbox._keyHandler = null;
        }

        if (lightbox._swipeCleanup) {
            lightbox._swipeCleanup();
            lightbox._swipeCleanup = null;
        }

        const video = lightbox.querySelector('video');
        if (video) {
            video.pause();
            video.src = '';
            video.load();
        }

        lightbox.style.opacity = '0';

        setTimeout(function() {
            if (lightbox.parentNode) {
                document.body.removeChild(lightbox);
            }
            document.body.style.overflow = '';
        }, 300);
    }

    /**
     * 获取视频 MIME 类型
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
            'm4v': 'video/x-m4v'
        };
        return mimeTypes[ext] || 'video/mp4';
    }

    // 导出全局API
    window.MediaPlayer = {
        open: openMediaLightbox
    };

    console.log('MediaPlayer: 增强版模块已加载（自定义控件）');

})();
