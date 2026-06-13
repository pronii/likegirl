#!/bin/bash
# 宝塔面板一键部署脚本
# 使用方法：上传到服务器后执行 bash bt_deploy.sh

echo "=========================================="
echo "   LikeGirl 宝塔面板一键部署脚本"
echo "=========================================="
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 配置变量
SITE_PATH="/www/wwwroot/likegirl"
DB_NAME="likegirl"
DB_USER="likegirl"

# 检查是否为root用户
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}[错误]${NC} 请使用 root 用户执行此脚本"
    echo "使用命令: sudo bash bt_deploy.sh"
    exit 1
fi

echo -e "${GREEN}[1/8]${NC} 检查宝塔面板..."
if [ ! -d "/www/server/panel" ]; then
    echo -e "${RED}[错误]${NC} 未检测到宝塔面板，请先安装宝塔面板"
    echo "安装命令: wget -O install.sh http://download.bt.cn/install/install_6.0.sh && bash install.sh"
    exit 1
fi
echo -e "${GREEN}✓${NC} 宝塔面板已安装"

echo ""
echo -e "${GREEN}[2/8]${NC} 克隆代码仓库..."
if [ -d "$SITE_PATH" ]; then
    read -p "站点目录已存在，是否覆盖？(y/n): " confirm
    if [ "$confirm" != "y" ]; then
        echo "已取消"
        exit 0
    fi
    rm -rf "$SITE_PATH"
fi

mkdir -p "$SITE_PATH"
cd "$SITE_PATH"
git clone https://github.com/pronii/likegirl.git .

if [ $? -ne 0 ]; then
    echo -e "${RED}[错误]${NC} Git 克隆失败"
    exit 1
fi
echo -e "${GREEN}✓${NC} 代码克隆成功"

echo ""
echo -e "${GREEN}[3/8]${NC} 设置文件权限..."
chown -R www:www "$SITE_PATH"
chmod -R 755 "$SITE_PATH"
chmod -R 777 "$SITE_PATH/uploads"
echo -e "${GREEN}✓${NC} 权限设置完成"

echo ""
echo -e "${GREEN}[4/8]${NC} 配置数据库..."
echo "请输入数据库密码（将创建数据库 $DB_NAME）:"
read -s DB_PASSWORD
echo ""

# 创建数据库
mysql -uroot -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
mysql -uroot -p"$DB_PASSWORD" -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';" 2>/dev/null
mysql -uroot -p"$DB_PASSWORD" -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';" 2>/dev/null
mysql -uroot -p"$DB_PASSWORD" -e "FLUSH PRIVILEGES;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} 数据库创建成功"
else
    echo -e "${YELLOW}[警告]${NC} 数据库可能已存在，跳过创建"
fi

echo ""
echo -e "${GREEN}[5/8]${NC} 修改数据库配置文件..."
cat > "$SITE_PATH/admin/Config_DB.php" << EOF
<?php
/*
 * @Version：Like Girl 5.2.1-Stable
 * @Author: Ki.
 */

header("Content-Type:text/html; charset=utf8");

// 数据库地址
\$db_address = "localhost";

// 数据库用户名
\$db_username = "$DB_USER";

// 数据库密码
\$db_password = "$DB_PASSWORD";

// 数据库表名
\$db_name = "$DB_NAME";
EOF

echo -e "${GREEN}✓${NC} 数据库配置文件已更新"

echo ""
echo -e "${GREEN}[6/8]${NC} 配置 Nginx 性能优化..."

# 备份原配置
if [ -f "/www/server/nginx/conf/nginx.conf" ]; then
    cp /www/server/nginx/conf/nginx.conf /www/server/nginx/conf/nginx.conf.backup.$(date +%Y%m%d%H%M%S)
fi

# 检查并添加性能优化配置（如果不存在）
if ! grep -q "keepalive_requests" /www/server/nginx/conf/nginx.conf; then
    sed -i '/keepalive_timeout/a\    keepalive_requests 100;\n    tcp_nopush on;\n    tcp_nodelay on;' /www/server/nginx/conf/nginx.conf
fi

# 优化 Gzip 配置
if grep -q "gzip_comp_level 1" /www/server/nginx/conf/nginx.conf; then
    sed -i 's/gzip_comp_level 1/gzip_comp_level 6/' /www/server/nginx/conf/nginx.conf
fi

echo -e "${GREEN}✓${NC} Nginx 配置优化完成"

echo ""
echo -e "${GREEN}[7/8]${NC} 重启服务..."
systemctl reload nginx
systemctl restart php-fpm

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} 服务重启成功"
else
    echo -e "${YELLOW}[警告]${NC} 服务重启可能失败，请手动检查"
fi

echo ""
echo -e "${GREEN}[8/8]${NC} 导入数据库..."
if [ -f "$SITE_PATH/love_db.sql" ]; then
    mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$SITE_PATH/love_db.sql"
    echo -e "${GREEN}✓${NC} 数据库导入成功"
else
    echo -e "${YELLOW}[警告]${NC} 未找到 love_db.sql，请手动导入数据库"
fi

echo ""
echo "=========================================="
echo -e "${GREEN}   部署完成！${NC}"
echo "=========================================="
echo ""
echo "访问信息："
echo "  - 前台: http://$(hostname -I | awk '{print $1}')/"
echo "  - 后台: http://$(hostname -I | awk '{print $1}')/admin/"
echo ""
echo "数据库信息："
echo "  - 数据库名: $DB_NAME"
echo "  - 用户名: $DB_USER"
echo "  - 密码: $DB_PASSWORD"
echo ""
echo "后续操作："
echo "  1. 如需配置域名，请在宝塔面板修改"
echo "  2. 建议配置 SSL 证书（Let's Encrypt 免费）"
echo "  3. 定期备份数据库和 uploads 目录"
echo ""
echo "详细文档: 宝塔面板迁移指南.md"
echo "=========================================="
