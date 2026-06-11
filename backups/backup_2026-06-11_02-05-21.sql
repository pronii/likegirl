-- LikeGirl Database Backup
-- Date: 2026-06-11 02:05:21

SET FOREIGN_KEY_CHECKS=0;

-- Table: about
DROP TABLE IF EXISTS `about`;
CREATE TABLE `about` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL COMMENT '标题',
  `aboutimg` varchar(100) NOT NULL COMMENT '背景图片',
  `info1` varchar(50) NOT NULL COMMENT '对话1',
  `info2` varchar(50) NOT NULL COMMENT '对话2',
  `info3` varchar(50) NOT NULL COMMENT '对话3',
  `btn1` varchar(30) NOT NULL COMMENT '按钮确定',
  `btn2` varchar(30) NOT NULL COMMENT '按钮取消',
  `infox1` varchar(30) NOT NULL COMMENT 'x2',
  `infox2` varchar(30) NOT NULL COMMENT 'x2',
  `infox3` varchar(30) NOT NULL COMMENT 'x2',
  `infox4` varchar(30) NOT NULL COMMENT 'x2',
  `infox5` varchar(30) NOT NULL COMMENT 'x2',
  `infox6` varchar(30) NOT NULL COMMENT 'x2',
  `btnx2` varchar(30) NOT NULL COMMENT 'btnx2',
  `infof1` varchar(30) NOT NULL COMMENT 'f3',
  `infof2` varchar(30) NOT NULL COMMENT 'f3',
  `infof3` varchar(30) NOT NULL COMMENT 'f3',
  `infof4` varchar(30) NOT NULL COMMENT 'f3',
  `btnf3` varchar(30) NOT NULL COMMENT 'btnf3',
  `infod1` varchar(30) NOT NULL COMMENT 'd4',
  `infod2` varchar(30) NOT NULL COMMENT 'd4',
  `infod3` varchar(30) NOT NULL COMMENT 'd4',
  `infod4` varchar(30) NOT NULL COMMENT 'd4',
  `infod5` varchar(30) NOT NULL COMMENT 'd4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

INSERT INTO `about` VALUES('1','Ki_About','https://ice.frostsky.com/2024/11/06/570374efdc2bb75a8b722c969118afb5.webp','Hi, 欢迎你的来访','愿得一人心 白首不相离','记录日常生活 留住感动','听我介绍','结束介绍','情侣小站Like Girl是 Ki 的原创项目','在2022年暑假的假期最后几天里发布了1.0版本','最新版本为 v5.2.0 亦是最终版本 目前已开源到码云','PHP确实是 “世界上最好的语言”  我非常喜欢（痛苦','在开发过程中遇到了许多奇葩问题 也是只能自己探索解决...','喜欢探索编程领域 热爱学习新知识 热爱开源文化','为什么叫 Ki？','不知道你有没有看过《比悲伤更悲伤的故事》','嗨，我是k，如果有下辈子的话，','“我想当戒指，眼镜，床和笔记本，这样的话，我就可以...”','当然跟这个没有关系哈哈','本站前端所有页面','首页 index','点点滴滴 little','留言板 leaving','关于 about','欢迎您的来访 IP已记录 请尽情浏览本站～');

-- Table: article
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articletext` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `articletime` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `articletitle` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `articlename` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `article` VALUES('1','<quote>引用内容样式</quote>\n\n<hr>\n\n\n<h1>H1文字大小演示</h1>\n\n<hr>\n\n\n<h2>H2文字大小演示</h2>\n\n<hr>\n\n\n<h3>H3文字大小演示</h3>\n\n<hr>\n\n\n<h4>H4文字大小演示</h4>\n\n<hr>\n\n\n<h5>H5文字大小演示</h5>\n\n<hr>\n\n\n<h6>H6文字大小演示</h6>\n\n<hr>\n\n\n<b>加粗字体</b>\n<s>删除线字体</s>\n<i>斜体</i>\n<code>强调内容</code>\n\n<center>文本居中</center>\n\n\n<!--分割线-->\n<hr>\n<quote>插入图片</quote>\n<img alt=\"\" src=\"https://lovey.kikiw.cn/Style/img/Cover.webp\">\n<!--分割线-->\n<hr>\n<quote>插入视频</quote>\n\n<video src=\"https://classpic.kikiw.cn/video/fengjing.mp4\" controls></video>\n\n<!--分割线-->\n<hr>','2022-11-20','Like_Girl 默认文章语法','Ki.');

-- Table: diyset
DROP TABLE IF EXISTS `diyset`;
CREATE TABLE `diyset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `headCon` text NOT NULL,
  `footerCon` text NOT NULL,
  `cssCon` text NOT NULL,
  `Pjaxkg` varchar(1) NOT NULL COMMENT 'pjax开关',
  `Blurkg` varchar(1) NOT NULL COMMENT '高斯模糊开关',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

INSERT INTO `diyset` VALUES('1','','&lt;!--&lt;script src=&quot;https://img-love.kikiw.cn/jsxg/yh/yinghua.js&quot;&gt;&lt;/script&gt;--&gt;','','1','1');

-- Table: iperror
DROP TABLE IF EXISTS `iperror`;
CREATE TABLE `iperror` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipAdd` varchar(100) NOT NULL COMMENT 'ip归属地',
  `Time` varchar(200) NOT NULL COMMENT '时间',
  `State` text NOT NULL COMMENT '拉黑ip',
  `text` varchar(100) NOT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Table: leaving
DROP TABLE IF EXISTS `leaving`;
CREATE TABLE `leaving` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户名字',
  `QQ` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'QQ号码',
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '留言内容',
  `time` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'ip记录',
  `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '省/城市',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `leaving` VALUES('1','Ki.','3439780232','Like Girl 5.2.1-Stable 默认留言','1756830249','223.104.79.236','广东');

-- Table: leavset
DROP TABLE IF EXISTS `leavset`;
CREATE TABLE `leavset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jiequ` varchar(10) NOT NULL COMMENT '截取长度',
  `lanjie` varchar(500) NOT NULL COMMENT '违禁符号',
  `lanjiezf` varchar(500) NOT NULL COMMENT '违禁词',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

INSERT INTO `leavset` VALUES('1','100','`~!@#$^&*()=|{}\':;\',\\\\[\\\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“\'。，、？','操垃圾傻逼妈');

-- Table: login
DROP TABLE IF EXISTS `login`;
CREATE TABLE `login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(100) NOT NULL COMMENT '登录用户名',
  `pw` char(32) NOT NULL COMMENT '登录密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

INSERT INTO `login` VALUES('1','admin','8c3ee407c836e339d9ec61a43d1dcaeb');

-- Table: love_album
DROP TABLE IF EXISTS `love_album`;
CREATE TABLE `love_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_name` varchar(100) NOT NULL,
  `album_cover` varchar(500) DEFAULT NULL,
  `album_desc` varchar(200) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `create_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

INSERT INTO `love_album` VALUES('22','test','','','0','');
INSERT INTO `love_album` VALUES('23','521','','','0','');

-- Table: loveimg
DROP TABLE IF EXISTS `loveimg`;
CREATE TABLE `loveimg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) DEFAULT '0',
  `imgDatd` varchar(100) NOT NULL COMMENT '日期',
  `imgText` varchar(200) NOT NULL COMMENT '描述',
  `imgUrl` varchar(500) NOT NULL COMMENT '外链',
  PRIMARY KEY (`id`),
  KEY `idx_album_id` (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4;

INSERT INTO `loveimg` VALUES('66','0','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(1).png');
INSERT INTO `loveimg` VALUES('67','0','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(35).png');
INSERT INTO `loveimg` VALUES('68','0','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(52).png');
INSERT INTO `loveimg` VALUES('69','0','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(69).png');
INSERT INTO `loveimg` VALUES('70','0','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(70).png');
INSERT INTO `loveimg` VALUES('71','0','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(71).png');
INSERT INTO `loveimg` VALUES('72','0','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(72).png');
INSERT INTO `loveimg` VALUES('73','0','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(73).png');
INSERT INTO `loveimg` VALUES('74','23','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(74).png');
INSERT INTO `loveimg` VALUES('75','23','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(75).png');
INSERT INTO `loveimg` VALUES('76','23','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(76).png');
INSERT INTO `loveimg` VALUES('77','23','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(77).png');
INSERT INTO `loveimg` VALUES('78','23','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(78).png');
INSERT INTO `loveimg` VALUES('79','23','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(79).png');
INSERT INTO `loveimg` VALUES('80','23','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/1%20(80).png');
INSERT INTO `loveimg` VALUES('81','23','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/20260529024459957_1779993901.png');
INSERT INTO `loveimg` VALUES('82','23','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/20260529024635623_1779993997.png');
INSERT INTO `loveimg` VALUES('83','23','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/20260529024801090_1779994082.png');
INSERT INTO `loveimg` VALUES('84','22','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/20260529040843222_1779998925.png');
INSERT INTO `loveimg` VALUES('85','22','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/IMG_0155.jpeg');
INSERT INTO `loveimg` VALUES('86','22','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/IMG_0166.png');
INSERT INTO `loveimg` VALUES('87','22','2026-06-11','','https://alist.ll.993209.xyz:888/d/onedrive/图床/IMG_3258.JPG');

-- Table: lovelist
DROP TABLE IF EXISTS `lovelist`;
CREATE TABLE `lovelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icon` int(1) NOT NULL COMMENT '是否完成',
  `eventname` varchar(200) CHARACTER SET utf8mb4 NOT NULL COMMENT '事件内容',
  `imgurl` varchar(300) COLLATE utf8_unicode_ci NOT NULL COMMENT '图片地址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `lovelist` VALUES('1','0','一起去电影院看一场电影🎬','0');
INSERT INTO `lovelist` VALUES('2','0','一起穿情侣装逛街🧡','0');
INSERT INTO `lovelist` VALUES('3','0','一起去一趟迪士尼游乐园🎡','0');
INSERT INTO `lovelist` VALUES('4','0','一起去游泳🏊','0');
INSERT INTO `lovelist` VALUES('5','0','一起唱次歌并且录下来🎤','0');
INSERT INTO `lovelist` VALUES('6','0','一起在厨房做次饭🍳','0');
INSERT INTO `lovelist` VALUES('7','0','一起过次烛光晚餐🍷','0');
INSERT INTO `lovelist` VALUES('8','0','一起过生日🎂','0');
INSERT INTO `lovelist` VALUES('9','0','一起打扫卫生🧹','0');
INSERT INTO `lovelist` VALUES('10','0','一起给对方写信，然后读给对方听💌','0');
INSERT INTO `lovelist` VALUES('11','0','一起去一次鬼屋👻','0');
INSERT INTO `lovelist` VALUES('12','0','一起去蹦极🪂','0');
INSERT INTO `lovelist` VALUES('13','0','一起养一只宠物🐶','0');
INSERT INTO `lovelist` VALUES('14','0','一起研究口红色号💄','0');
INSERT INTO `lovelist` VALUES('15','0','一起给对方化妆💅','0');
INSERT INTO `lovelist` VALUES('16','0','一起为对方抹指甲油💅','0');
INSERT INTO `lovelist` VALUES('17','0','一起去做次陶艺🏺','0');
INSERT INTO `lovelist` VALUES('18','0','一起去吃一次全家桶🍗','0');
INSERT INTO `lovelist` VALUES('19','0','一起熬夜通宵跨年🎇','0');
INSERT INTO `lovelist` VALUES('20','0','一起去旅游✈️','0');
INSERT INTO `lovelist` VALUES('21','0','一起去爬山⛰','0');
INSERT INTO `lovelist` VALUES('22','0','一起坐一次摩天轮🎡','0');
INSERT INTO `lovelist` VALUES('23','0','一起拍视频记录生活📹','0');
INSERT INTO `lovelist` VALUES('24','0','一起为对方刷牙，然后亲亲😘','0');
INSERT INTO `lovelist` VALUES('25','0','一起去看一次海，去沙滩🏖','0');
INSERT INTO `lovelist` VALUES('26','0','互穿对方的衣服，拍照留念📸','0');
INSERT INTO `lovelist` VALUES('27','0','一起逛超市买好吃的🛒','0');
INSERT INTO `lovelist` VALUES('28','0','一起坐一次热气球🎈','0');
INSERT INTO `lovelist` VALUES('29','0','一起看书，分享自己喜欢的书籍📖','0');
INSERT INTO `lovelist` VALUES('30','0','一起在下雨天追剧📺','0');
INSERT INTO `lovelist` VALUES('31','0','一起做一次蛋糕甜点🍰','0');
INSERT INTO `lovelist` VALUES('32','0','一起看日出看日落🌅','0');
INSERT INTO `lovelist` VALUES('33','0','一起上下班，坐地铁🚇','0');
INSERT INTO `lovelist` VALUES('34','0','一起坐一次飞机✈️','0');
INSERT INTO `lovelist` VALUES('35','0','一起种花草🌱','0');
INSERT INTO `lovelist` VALUES('36','0','一起用情侣手机壳📱','0');
INSERT INTO `lovelist` VALUES('37','0','一起去一次海底世界🐠','0');
INSERT INTO `lovelist` VALUES('38','0','一起喝醉一次🍻','0');
INSERT INTO `lovelist` VALUES('39','0','一起打扑克牌🃏','0');
INSERT INTO `lovelist` VALUES('40','0','一起修理电器🔧','0');
INSERT INTO `lovelist` VALUES('41','0','一起看烟花🎆','0');
INSERT INTO `lovelist` VALUES('42','0','一起吃火锅🍲','0');
INSERT INTO `lovelist` VALUES('43','0','一起庆祝恋爱纪念日💖','0');
INSERT INTO `lovelist` VALUES('44','0','一起看雪，堆雪人⛄','0');
INSERT INTO `lovelist` VALUES('45','0','一起和朋友们去吃饭🍽','0');
INSERT INTO `lovelist` VALUES('46','0','一起跳舞💃','0');
INSERT INTO `lovelist` VALUES('47','0','一起听音乐，听同一首歌🎵','0');
INSERT INTO `lovelist` VALUES('48','0','一起坐一次船⛵','0');
INSERT INTO `lovelist` VALUES('49','0','一起露营，住一次帐篷🏕','0');
INSERT INTO `lovelist` VALUES('50','0','一起DIY手工🎨','0');
INSERT INTO `lovelist` VALUES('51','0','给对方准备礼物🎁','0');
INSERT INTO `lovelist` VALUES('52','0','一起去我们上过的小学，中学，大学🏫','0');
INSERT INTO `lovelist` VALUES('53','0','一起在沙发上躺着🛋','0');
INSERT INTO `lovelist` VALUES('54','0','一起睡个懒觉，赖个床🛏','0');
INSERT INTO `lovelist` VALUES('55','0','偷偷为对方买喜欢又舍不得的东西🎁','0');
INSERT INTO `lovelist` VALUES('56','0','一起坐一次巴士，在没去过的地方下车🚌','0');
INSERT INTO `lovelist` VALUES('57','0','一起为布置小家出主意🏠','0');
INSERT INTO `lovelist` VALUES('58','0','一起在午夜看一次恐怖片🎃','0');
INSERT INTO `lovelist` VALUES('59','0','一起去挑选一束花💐','0');
INSERT INTO `lovelist` VALUES('60','0','一起去跳一次广场舞🕺','0');
INSERT INTO `lovelist` VALUES('61','0','一起为对方按摩一次💆','0');
INSERT INTO `lovelist` VALUES('62','0','一起放一次风筝🪁','0');
INSERT INTO `lovelist` VALUES('63','0','一起吐槽一次对方的缺点😆','0');
INSERT INTO `lovelist` VALUES('64','0','接对方下班一次🚗','0');
INSERT INTO `lovelist` VALUES('65','0','当陌生人一天，不许交流🤫','0');
INSERT INTO `lovelist` VALUES('66','0','为对方做便当🍱','0');
INSERT INTO `lovelist` VALUES('67','0','一起存钱💰','0');
INSERT INTO `lovelist` VALUES('68','0','一起去看樱花🌸','0');
INSERT INTO `lovelist` VALUES('69','0','一起敷面膜🧖','0');
INSERT INTO `lovelist` VALUES('70','0','一起去一次动物园🐼','0');
INSERT INTO `lovelist` VALUES('71','0','一起骑行车🚴','0');
INSERT INTO `lovelist` VALUES('72','0','一起拍照洗照片贴房间🖼','0');
INSERT INTO `lovelist` VALUES('73','0','一起听一次演唱会🎤','0');
INSERT INTO `lovelist` VALUES('74','0','一起去一次酒吧🍹','0');
INSERT INTO `lovelist` VALUES('75','0','一起去听一次相声😂','0');
INSERT INTO `lovelist` VALUES('76','0','一起玩一次真心话大冒险🎭','0');
INSERT INTO `lovelist` VALUES('77','0','一起去许愿池许个愿🙏','0');
INSERT INTO `lovelist` VALUES('78','0','一起入住一次五星级酒店，看夜景🏨','0');
INSERT INTO `lovelist` VALUES('79','0','一起去见父母👨‍👩‍👧‍👦','0');
INSERT INTO `lovelist` VALUES('80','0','一起挑选戒指💍','0');
INSERT INTO `lovelist` VALUES('81','0','一起挑选婚纱👗','0');
INSERT INTO `lovelist` VALUES('82','0','一起为我们的小家添置东西🏠','0');
INSERT INTO `lovelist` VALUES('83','0','一起期待未来甜蜜小生活💑','0');

-- Table: text
DROP TABLE IF EXISTS `text`;
CREATE TABLE `text` (
  `id` int(11) NOT NULL,
  `boy` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT '男name',
  `girl` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT '女name',
  `title` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '网站标题',
  `logo` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '网站logo',
  `writing` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '网站文案',
  `boyimg` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '男QQ',
  `girlimg` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '女QQ',
  `startTime` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '开始时间',
  `icp` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '网站备案号',
  `Copyright` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '网站版权',
  `card1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `card2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `card3` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `deci1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `deci2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `deci3` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `bgimg` varchar(200) COLLATE utf8_unicode_ci NOT NULL COMMENT '首页背景图片地址',
  `userQQ` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT '站长QQ',
  `userName` varchar(30) COLLATE utf8_unicode_ci NOT NULL COMMENT 'userName',
  `Animation` int(1) NOT NULL COMMENT '动画开关',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `text` VALUES('1','Ki','Li','Like_Girl v5.2.3','Like_Girl {v5.2.0}','爱晨雾漫过青瓦，爱暮色染透篱笆，更爱与君并肩立，看遍这人间烟火里的朝暮与年华。','647159607','917640289','2022-06-05T00:07','粤ICP备2021037776号','Copyright © 2022 - 2025 Like_Girl All Rights Reserved.','点点滴滴','留言板','关于我们','有人愿意听你碎碎念念也很浪漫','在这里写下我们的留言祝福','我们之间认识的经历回忆','Style/img/bgCover.png','3439780232','Ki','1');

-- Table: warning
DROP TABLE IF EXISTS `warning`;
CREATE TABLE `warning` (
  `id` int(11) NOT NULL,
  `ip` varchar(50) NOT NULL COMMENT 'ip地址',
  `gsd` varchar(50) NOT NULL COMMENT '归属地',
  `time` varchar(80) NOT NULL COMMENT '时间',
  `file` varchar(100) NOT NULL COMMENT '路径',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `warning` VALUES('0','::1','未知','2026-06-02/11:35:46','/loveImg.php');

SET FOREIGN_KEY_CHECKS=1;
