<!--
 * @Version：Like Girl 5.2.1-Stable
 * @Author: Ki.
 * @Date: 2025-09-03 00:00:00
 * @LastEditTime: 2025-09-03
 * @Description: 愿得一心人 白头不相离
 * @Document：https://blog.kikiw.cn/index.php/archives/52/
 * @Copyright (c) 2023 - 2025 by Ki All Rights Reserved. 
 * @Warning：禁止以任何方式出售本项目 如有发现一切后果自行负责
 * @Warning：禁止以任何方式出售本项目 如有发现一切后果自行负责
 * @Warning：禁止以任何方式出售本项目 如有发现一切后果自行负责
 * @Message：开发不易 版权信息请保留 (删除/修改作者版权的Dog请勿使用 感谢配合)
-->
<?php
// 生产环境禁止显示错误，开发环境显示所有错误
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
include ("ipjc.php");
include_once ("ip.php");
include_once 'admin/connect.php';
include_once 'admin/Function.php';

$sql = "select * from text";
$result = mysqli_query($connect, $sql);
$text = mysqli_fetch_array($result);

$sql = "select * from diySet";
$result = mysqli_query($connect, $sql);
if (mysqli_num_rows($result)) {
    $diy = mysqli_fetch_array($result);
}

$copy = $text['Copyright'];
$icp = $text['icp'];
$Animation = $text['Animation'];
?>


<script>

    function setupVideoPlayer(video) {
        var videoContainer = $('<div class="video-container"></div>');
        var playPauseBtn = $('<div class="play-pause-btn"></div>');
    
        var playPauseIcon = `
            <svg t="1730884474730" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg"
                p-id="7671" width="200" height="200">
                <path
                    d="M861.829969 330.562413L391.150271 33.456576A214.465233 214.465233 0 0 0 62.30358 214.751187V809.248813a214.465233 214.465233 0 0 0 328.846691 181.294611l470.679698-297.105837a214.751187 214.751187 0 0 0 0-362.875174z"
                    fill="#ffffff" p-id="7672"></path>
            </svg>
        `;
        playPauseBtn.html(playPauseIcon);
    
        video.wrap(videoContainer);
        video.parent().append(playPauseBtn);
    
        video.attr('controls', false);
    
        video.css({
            'width': '100%',
            'height': 'auto'
        });
    
        playPauseBtn.show();
    
        playPauseBtn.on('click', function(e) {
            e.stopPropagation();
    
            if (video[0].paused) {
                video[0].play();
                playPauseBtn.hide();
            } else {
                video[0].pause();
                playPauseBtn.show();
            }
        });
    
        video.on('click', function() {
            if (video[0].paused) {
                video[0].play();
                playPauseBtn.hide();
            } else {
                video[0].pause();
                playPauseBtn.show();
            }
        });
    }

    function show_date_time() {
        window.setTimeout("show_date_time()", 1000);
        BirthDay = new Date("<?php echo $text['startTime'] ?>");
        today = new Date();
        timeold = (today.getTime() - BirthDay.getTime());
        sectimeold = timeold / 1000;
        secondsold = Math.floor(sectimeold);
        msPerDay = 24 * 60 * 60 * 1000;
        e_daysold = timeold / msPerDay;
        daysold = Math.floor(e_daysold);
        e_hrsold = (e_daysold - daysold) * 24;
        hrsold = Math.floor(e_hrsold);
        e_minsold = (e_hrsold - hrsold) * 60;
        minsold = Math.floor((e_hrsold - hrsold) * 60);
        seconds = Math.floor((e_minsold - minsold) * 60);
        let timeKi = document.getElementById('span_dt_dt');
        if (timeKi !== null) {
            span_dt_dt.innerHTML = "这是我们一起走过的";
            tian.innerHTML = daysold + '天';
            shi.innerHTML = hrsold + '时';
            fen.innerHTML = minsold + '分';
            if (seconds < 10) {
                seconds = "0" + seconds
            }
            miao.innerHTML = seconds + '秒';
        }
    }

    show_date_time();
    
    function initScrollButton(btnSelector, targetSelector, tolerance = 800, duration = 800) {
    const $btn = $(btnSelector);
    const $target = $(targetSelector);

    if ($btn.length && $target.length) {
        // 点击按钮滚动到目标
        $btn.on('click', () => {
            const targetOffset = $target.offset().top;
            $('html, body').animate({ scrollTop: targetOffset }, duration);
        });

        // 根据滚动位置显示/隐藏按钮
        $(window).on('scroll resize', () => {
            const scrollTop = $(window).scrollTop();
            const targetOffset = $target.offset().top;

            if (Math.abs(scrollTop - targetOffset) <= tolerance) {
                $btn.fadeOut();
            } else {
                $btn.fadeIn();
            }
        }).trigger('scroll');
    }
}

</script>
<link rel="shortcut icon" href="/favicon.ico" />

<!-- ===== DNS Prefetch + Preconnect 优化（解决 SSL 握手延迟） ===== -->
<!-- DNS 预解析：提前解析域名，节省 DNS 查询时间 -->
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//cdn.jsdelivr.net">
<link rel="dns-prefetch" href="//fonts.gstatic.com">

<!-- 预连接：提前建立 TCP + TLS 连接，节省 233ms SSL 握手时间 -->
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

<meta name="keywords"
    content="<?php echo $text['title'] ?>,Like Girl 5.2.1-Stable,LGNeUi,情侣小站,开源情侣网站,PHP情侣网站,情侣记录,情侣网站,情侣项目,情侣小窝,Love,LikeGirl,Ki,PHP情侣小站,情侣小站使用教程,情侣小站使用文档">
<meta name="discription" content="<?php echo $text['writing'] ?> - Like Girl 5.2.1-Stable">
<meta name="author" content="Ki">
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">

<!-- Google Fonts：合并请求减少 SSL 握手次数 -->
<link href="https://fonts.googleapis.com/css?family=Concert+One|Pacifico&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../Style/css/leaving.css?LikeGirl=<?php echo $version ?>">
<link rel="stylesheet" href="../Style/css/index.css?LikeGirl=<?php echo $version ?>">
<link rel="stylesheet" href="../Style/css/little.css?LikeGirl=<?php echo $version ?>">
<link rel="stylesheet" href="../Style/css/about.css?LikeGirl=<?php echo $version ?>">
<link rel="stylesheet" href="../Style/css/animate.min.css">
<link rel="stylesheet" href="../Botui/botui.min.css">
<link rel="stylesheet" href="../Botui/botui-theme-default.css">
<link rel="stylesheet" href="../Style/Font/font_list/iconfont.css">
<link rel="stylesheet" href="../Style/css/loveImg.css?LikeGirl=<?php echo $version ?>">
<link rel="stylesheet" href="../Style/css/list.css?LikeGirl=<?php echo $version ?>">
<link rel="stylesheet" href="../Style/toastr/toastr.css?LikeGirl=<?php echo $version ?>">
<link rel="stylesheet" href="../Style/css/loadinglike.css?LikeGirl=<?php echo $version ?>">
<script src="../Style/Font/font_leav/iconfont.js"></script>
<script src="../Botui/botui.min.js"></script>
<script src="../Style/js/vue.min.js"></script>
<script src="../Style/jquery/jquery.min.js"></script>
<script src="../Style/js/jquery.pjax.js" type="text/javascript"></script>
<script src="../Style/pagelir/spotlight.bundle.js"></script>
<script src="../Style/js/funlazy.min.js"></script>
<script src="../Style/js/loading.js?LikeGirl=<?php echo $version ?>"></script>

<?php
echo htmlspecialchars_decode($diy['headCon'], ENT_QUOTES);
if ($diy['Pjaxkg'] == "1"):
    ?>
    <script>
        $(document).pjax('a[target!=_blank]', '#pjax-container', { fragment: '#pjax-container', timeout: 15000 });
        $(document).on('pjax:send', function () {
            NProgress.start();
        });
        $(document).on('pjax:complete', function () {
            $(".love_img img,.lovelist img,.little_texts img").addClass("spotlight").each(function () {
                const self = this;
                this.onclick = function (e) {
                    e.preventDefault();

                    // 先强制替换所有懒加载图片的src
                    $('.spotlight[data-funlazy]').each(function() {
                        const $img = $(this);
                        const realSrc = $img.attr('data-funlazy');
                        if (realSrc) {
                            $img.attr('src', realSrc);
                            $img.removeAttr('data-funlazy');
                        }
                    });

                    // 等待当前图片真正加载完成
                    if (self.complete) {
                        hs.expand(self);
                    } else {
                        self.onload = function() {
                            hs.expand(self);
                        };
                    }

                    return false;
                }
            });
            NProgress.done();
            
            FunLazy({
                placeholder: "Style/img/Loading2.gif",
                effect: "show",
                strictLazyMode: false,
                useErrorImagePlaceholder: "Style/img/error.svg"
            })
            
            $('.card, .card-b').click(function() {
                var link = $(this).find('a').get(0);
                if (link) {
                    link.click();
                }
            });
            
            $('#MessageBtn').click(function() {
                var targetOffset = $('#MessageArea').offset().top;
                if ($(window).scrollTop() !== targetOffset) {
                    $('html, body').animate({
                        scrollTop: targetOffset
                    }, 800);
                }
            });
            
            
            $('video').each(function() {
                var video = $(this);
                setupVideoPlayer(video);
            });

            // PJAX 跳转后重新初始化相册模块
            if ($('#albumGallery').length > 0) {
                // 检查并加载 loveAlbum.js
                if (typeof LoveAlbum === 'undefined') {
                    // 动态加载 loveAlbum.js
                    const script = document.createElement('script');
                    script.src = 'Style/js/loveAlbum.js?t=' + Date.now();
                    script.onerror = function() {
                        console.error('❌ 相册模块加载失败');
                        $('#loading').hide();
                        $('#albumGallery').html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">模块加载失败<br><button class="btn btn-primary mt-3" onclick="location.reload()">刷新页面</button></div>');
                    };
                    document.head.appendChild(script);
                } else {
                    // 模块已加载，直接初始化
                    if (typeof initLoveAlbum === 'function') {
                        try {
                            initLoveAlbum();
                        } catch (e) {
                            console.error('❌ 相册初始化失败:', e);
                            $('#loading').hide();
                            $('#albumGallery').html('<div class="col-12 text-center" style="padding: 40px; color: #dc3545;">初始化失败: ' + e.message + '<br><button class="btn btn-primary mt-3" onclick="location.reload()">刷新页面</button></div>');
                        }
                    }
                }
            }

            initScrollButton('#MessageBtn', '#MessageArea', 800, 800);

        });
        
        
    </script>
<?php endif; ?>
<script src="../Style/js/nprogress.js?LikeGirl=<?php echo $version ?>"></script>
<link href="../Style/css/nprogress.css?LikeGirl=<?php echo $version ?>" rel="stylesheet" type="text/css">
<!-- 头部导航条 -->
<div class="header-wrap">
    <div class="header">
        <div class="logo">
            <h1><a class="alogo" href="index.php"><?php echo preg_replace('/\{([^}]+)\}/', '<b>$1</b>', $text['logo']) ?></a></h1>
        </div>
        <div class="word" data-tip="<?php echo $text['writing'] ?>" data-tip-position="bottom">
            <span class="wenan"><?php echo $text['writing'] ?></span>
        </div>
    </div>
</div>

<!-- 头像内容 -->
<div class="bg-wrap">
    <div class="bg-img">
        <div class="central central-800">
            <div
                class="middle <?php if ($text['Animation'] == "1") { ?>animated fadeInDown<?php } ?> <?php if ($diy['Blurkg'] == "2") { ?>Blurkg<?php } ?>">
                <div class="img-male">
                    <img src="https://q1.qlogo.cn/g?b=qq&nk=<?php echo $text['boyimg'] ?>&s=640" draggable="false">
                    <span><?php echo $text['boy'] ?></span>
                </div>
                <div class="love-icon">
                    <img src="Style/img/like.svg" draggable="false">
                </div>
                <div class="img-female">
                    <img src="https://q1.qlogo.cn/g?b=qq&nk=<?php echo $text['girlimg'] ?>&s=640" draggable="false">
                    <span><?php echo $text['girl'] ?></span>
                </div>
            </div>
        </div>
        <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
            viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
            <defs>
                <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
            </defs>
            <g class="parallax">
                <use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(255,255,255,0.7" />
                <use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(255,255,255,0.5)" />
                <use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(255,255,255,0.3)" />
                <use xlink:href="#gentle-wave" x="48" y="7" fill="#fff" />
            </g>
        </svg>
    </div>
</div>


<style>
    .bg-img {
        background: url(<?php echo $text['bgimg'] ?>) no-repeat center !important;
        background-size: cover !important;
    }

    .wenan {
        color: rgb(97 97 97);
        transition: all 0.2s linear;
    }

    .alogo {
        color: rgb(97 97 97);
        transition: all 0.2s linear;
    }

    /* webkit, opera, IE9 （谷歌浏览器）*/
    ::selection {
        background: #6f6f6fc7;
        color: #ffffff;
    }

    /* mozilla firefox（火狐浏览器） */
    ::-moz-selection {
        background: #6f6f6fc7;
        color: #ffffff;
    }

    .delay-03s {
        -webkit-animation-delay: .3s;
        animation-delay: .3s;
    }

    .Blurkg {
        backdrop-filter: blur(0px) !important;
        -webkit-backdrop-filter: blur(0px) !important;
        background: transparent !important;
    }

    .cpt-loading-mask.column {
        background: transparent !important;
    }
</style>
<style>
    <?php echo $diy['cssCon'] ?>
</style>
