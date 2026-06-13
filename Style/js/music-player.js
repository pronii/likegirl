// 音乐播放器 - Like Girl 5.2.1
(function() {
    'use strict';

    class MusicPlayer {
        constructor() {
            this.playlist = [];
            this.currentIndex = 0;
            this.audio = new Audio();
            this.isPlaying = false;
            this.isMinimized = false;
            this.volume = 0.7;

            this.init();
        }

        init() {
            this.loadPlaylist();
            this.createPlayer();
            this.bindEvents();
            this.audio.volume = this.volume;
        }

        async loadPlaylist() {
            try {
                const response = await fetch('/api/getMusicList.php');
                const result = await response.json();
                if (result.success && result.data.length > 0) {
                    this.playlist = result.data;
                    this.loadTrack(0);
                }
            } catch (error) {
                console.error('加载音乐列表失败:', error);
            }
        }

        createPlayer() {
            const html = `
                <div id="musicPlayer" class="minimized">
                    <div class="music-control-menu show" id="musicControlMenu">
                        <div class="music-info-display">
                            <div class="music-title" id="musicTitle">暂无音乐</div>
                            <div class="music-artist" id="musicArtist">-</div>
                        </div>
                        <div class="music-control-menu-item" data-action="play">
                            <i class="mdi mdi-play" id="playIcon"></i>
                            <span>播放</span>
                        </div>
                        <div class="music-control-menu-item" data-action="prev">
                            <i class="mdi mdi-skip-previous"></i>
                            <span>上一首</span>
                        </div>
                        <div class="music-control-menu-item" data-action="next">
                            <i class="mdi mdi-skip-next"></i>
                            <span>下一首</span>
                        </div>
                        <div class="music-control-menu-item" data-action="volume">
                            <i class="mdi mdi-volume-high" id="volumeIcon"></i>
                            <input type="range" class="volume-slider-mini" id="volumeSlider" min="0" max="100" value="70">
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
        }

        bindEvents() {
            const player = document.getElementById('musicPlayer');
            const menuItems = document.querySelectorAll('.music-control-menu-item');
            const volumeSlider = document.getElementById('volumeSlider');

            // 菜单项点击事件
            menuItems.forEach(item => {
                item.addEventListener('click', (e) => {
                    const action = item.dataset.action;
                    switch(action) {
                        case 'play':
                            this.togglePlay();
                            break;
                        case 'prev':
                            this.prev();
                            break;
                        case 'next':
                            this.next();
                            break;
                    }
                });
            });

            // 音量控制
            volumeSlider.addEventListener('input', (e) => {
                this.volume = e.target.value / 100;
                this.audio.volume = this.volume;
            });

            // 音频事件
            this.audio.addEventListener('timeupdate', () => this.updateProgress());
            this.audio.addEventListener('ended', () => this.next());
            this.audio.addEventListener('loadedmetadata', () => this.updateDuration());
        }

        loadTrack(index) {
            if (index < 0 || index >= this.playlist.length) return;

            this.currentIndex = index;
            const track = this.playlist[index];

            document.getElementById('musicTitle').textContent = track.title;
            document.getElementById('musicArtist').textContent = track.artist || '未知艺术家';

            this.audio.src = track.music_url;
        }

        togglePlay() {
            if (!this.playlist.length) return;

            if (this.isPlaying) {
                this.pause();
            } else {
                this.play();
            }
        }

        play() {
            this.audio.play().catch(error => {
                console.error('播放失败:', error);
            });
            this.isPlaying = true;
            const playIcon = document.getElementById('playIcon');
            const playItem = document.querySelector('[data-action="play"] span');
            if (playIcon) {
                playIcon.className = 'mdi mdi-pause';
            }
            if (playItem) {
                playItem.textContent = '暂停';
            }
        }

        pause() {
            this.audio.pause();
            this.isPlaying = false;
            const playIcon = document.getElementById('playIcon');
            const playItem = document.querySelector('[data-action="play"] span');
            if (playIcon) {
                playIcon.className = 'mdi mdi-play';
            }
            if (playItem) {
                playItem.textContent = '播放';
            }
        }

        prev() {
            const newIndex = (this.currentIndex - 1 + this.playlist.length) % this.playlist.length;
            this.loadTrack(newIndex);
            if (this.isPlaying) this.play();
        }

        next() {
            const newIndex = (this.currentIndex + 1) % this.playlist.length;
            this.loadTrack(newIndex);
            if (this.isPlaying) this.play();
        }

        toggleMinimize() {
            this.isMinimized = !this.isMinimized;
            const player = document.getElementById('musicPlayer');
            if (this.isMinimized) {
                player.classList.add('minimized');
            } else {
                player.classList.remove('minimized');
            }
        }

        updateProgress() {
            // 简化版，不再需要进度条显示
        }

        updateDuration() {
            // 简化版，不再需要时长显示
        }

        formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            const min = Math.floor(seconds / 60);
            const sec = Math.floor(seconds % 60);
            return `${min}:${sec.toString().padStart(2, '0')}`;
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.musicPlayer = new MusicPlayer();
        });
    } else {
        window.musicPlayer = new MusicPlayer();
    }
})();
