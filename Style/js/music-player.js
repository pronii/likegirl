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
            this.currentSourceIndex = 0; // 当前音源索引
            this.sources = []; // 可用音源列表

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
                    <button class="minimized-play-btn" id="minimizedPlayBtn">
                        <i class="mdi mdi-music"></i>
                    </button>
                    <div class="music-player-header">
                        <h5><i class="mdi mdi-music-note"></i> 音乐播放器</h5>
                        <button class="music-player-toggle" id="togglePlayer">
                            <i class="mdi mdi-window-minimize"></i>
                        </button>
                    </div>
                    <div class="music-player-content">
                        <img src="" alt="封面" class="music-cover" id="musicCover">
                        <div class="music-info">
                            <div class="music-title" id="musicTitle">暂无音乐</div>
                            <div class="music-artist" id="musicArtist">-</div>
                        </div>
                        <div class="music-source" id="musicSource" style="text-align:center; margin-bottom:10px; display:none;">
                            <button class="source-switch-btn" id="switchSourceBtn" title="切换音源">
                                <i class="mdi mdi-swap-horizontal"></i> 切换音源
                            </button>
                        </div>
                        <div class="music-progress">
                            <div class="progress-bar-container" id="progressBar">
                                <div class="progress-bar-fill" id="progressFill"></div>
                            </div>
                            <div class="progress-time">
                                <span id="currentTime">0:00</span>
                                <span id="totalTime">0:00</span>
                            </div>
                        </div>
                        <div class="music-controls">
                            <button class="control-btn" id="prevBtn">
                                <i class="mdi mdi-skip-previous"></i>
                            </button>
                            <button class="control-btn play-btn" id="playBtn">
                                <i class="mdi mdi-play"></i>
                            </button>
                            <button class="control-btn" id="nextBtn">
                                <i class="mdi mdi-skip-next"></i>
                            </button>
                        </div>
                        <div class="volume-control">
                            <i class="mdi mdi-volume-high"></i>
                            <input type="range" class="volume-slider" id="volumeSlider" min="0" max="100" value="70">
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
        }

        bindEvents() {
            const player = document.getElementById('musicPlayer');
            const toggleBtn = document.getElementById('togglePlayer');
            const minimizedBtn = document.getElementById('minimizedPlayBtn');
            const playBtn = document.getElementById('playBtn');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const switchSourceBtn = document.getElementById('switchSourceBtn');
            const volumeSlider = document.getElementById('volumeSlider');
            const progressBar = document.getElementById('progressBar');

            toggleBtn.addEventListener('click', () => this.toggleMinimize());
            minimizedBtn.addEventListener('click', () => this.toggleMinimize());
            playBtn.addEventListener('click', () => this.togglePlay());
            prevBtn.addEventListener('click', () => this.prev());
            nextBtn.addEventListener('click', () => this.next());
            switchSourceBtn.addEventListener('click', () => this.switchSource());

            volumeSlider.addEventListener('input', (e) => {
                this.volume = e.target.value / 100;
                this.audio.volume = this.volume;
            });

            progressBar.addEventListener('click', (e) => {
                const rect = progressBar.getBoundingClientRect();
                const percent = (e.clientX - rect.left) / rect.width;
                this.audio.currentTime = this.audio.duration * percent;
            });

            // 音频事件
            this.audio.addEventListener('timeupdate', () => this.updateProgress());
            this.audio.addEventListener('ended', () => this.next());
            this.audio.addEventListener('loadedmetadata', () => this.updateDuration());

            // 播放错误时自动切换音源
            this.audio.addEventListener('error', () => {
                console.error('播放失败，尝试切换音源...');
                this.autoSwitchSource();
            });
        }

        async loadTrack(index) {
            if (index < 0 || index >= this.playlist.length) return;

            this.currentIndex = index;
            const track = this.playlist[index];
            this.currentSourceIndex = 0;

            // 获取可用音源
            await this.loadSources(track);

            document.getElementById('musicTitle').textContent = track.title;
            document.getElementById('musicArtist').textContent = track.artist || '未知艺术家';

            const cover = document.getElementById('musicCover');
            if (track.cover_url) {
                cover.src = track.cover_url;
            } else {
                cover.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200"%3E%3Crect fill="%23f0f0f0" width="200" height="200"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23999" font-size="48"%3E♪%3C/text%3E%3C/svg%3E';
            }

            // 显示/隐藏切换音源按钮
            const switchBtn = document.getElementById('musicSource');
            if (this.sources.length > 1) {
                switchBtn.style.display = 'block';
            } else {
                switchBtn.style.display = 'none';
            }
        }

        async loadSources(track) {
            this.sources = [];

            // 如果有直接URL，添加为第一个音源
            if (track.music_url) {
                this.sources.push({
                    name: '默认源',
                    url: track.music_url
                });
            }

            // 如果有song_id，从API获取其他音源
            if (track.song_id) {
                try {
                    const response = await fetch(`/api/musicSourceApi.php?song_id=${track.song_id}`);
                    const result = await response.json();
                    if (result.success && result.sources) {
                        result.sources.forEach(source => {
                            this.sources.push(source);
                        });
                    }
                } catch (error) {
                    console.error('获取音源失败:', error);
                }
            }

            // 加载第一个音源
            if (this.sources.length > 0) {
                this.audio.src = this.sources[0].url;
                console.log('当前音源:', this.sources[0].name);
            }
        }

        switchSource() {
            if (this.sources.length <= 1) return;

            this.currentSourceIndex = (this.currentSourceIndex + 1) % this.sources.length;
            const currentTime = this.audio.currentTime;
            const wasPlaying = this.isPlaying;

            this.audio.src = this.sources[this.currentSourceIndex].url;
            this.audio.currentTime = currentTime;

            console.log('切换到音源:', this.sources[this.currentSourceIndex].name);

            if (wasPlaying) {
                this.audio.play().catch(() => {
                    console.error('切换后播放失败');
                });
            }
        }

        autoSwitchSource() {
            if (this.currentSourceIndex < this.sources.length - 1) {
                setTimeout(() => {
                    this.switchSource();
                    if (this.isPlaying) {
                        this.play();
                    }
                }, 500);
            } else {
                console.error('所有音源都播放失败');
                this.pause();
            }
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
            document.querySelector('#playBtn i').className = 'mdi mdi-pause';
            document.querySelector('#minimizedPlayBtn i').className = 'mdi mdi-pause';
        }

        pause() {
            this.audio.pause();
            this.isPlaying = false;
            document.querySelector('#playBtn i').className = 'mdi mdi-play';
            document.querySelector('#minimizedPlayBtn i').className = 'mdi mdi-play';
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
            const percent = (this.audio.currentTime / this.audio.duration) * 100;
            document.getElementById('progressFill').style.width = percent + '%';
            document.getElementById('currentTime').textContent = this.formatTime(this.audio.currentTime);
        }

        updateDuration() {
            document.getElementById('totalTime').textContent = this.formatTime(this.audio.duration);
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
