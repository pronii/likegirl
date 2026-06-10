/**
 * 自定义弹窗对话框系统
 * 替代原生的 alert() 和 confirm()
 * 版本: 1.0.0
 */

(function(window) {
    'use strict';

    // 创建样式
    const style = document.createElement('style');
    style.textContent = `
        /* 遮罩层 */
        .custom-dialog-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .custom-dialog-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* 对话框容器 */
        .custom-dialog {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 0;
            min-width: 340px;
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25), 0 8px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transform: scale(0.85) translateY(30px);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
        }

        .custom-dialog-overlay.show .custom-dialog {
            transform: scale(1) translateY(0);
        }

        /* 对话框头部 */
        .custom-dialog-header {
            padding: 24px 28px 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        .custom-dialog-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-right: 18px;
            flex-shrink: 0;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .custom-dialog-header:hover .custom-dialog-icon {
            transform: scale(1.1) rotate(5deg);
        }

        /* 不同类型的图标样式 - 与 toastr 保持一致 */
        .custom-dialog-icon.info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }

        .custom-dialog-icon.success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);
            box-shadow: 0 8px 24px rgba(86, 171, 47, 0.4);
        }

        .custom-dialog-icon.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            box-shadow: 0 8px 24px rgba(240, 147, 251, 0.4);
        }

        .custom-dialog-icon.error {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            box-shadow: 0 8px 24px rgba(255, 107, 107, 0.4);
        }

        .custom-dialog-icon.confirm {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }

        .custom-dialog-icon svg {
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        .custom-dialog-close {
            width: 32px;
            height: 32px;
            border: none;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #999;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
        }

        .custom-dialog-close:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: rotate(90deg);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        /* 对话框内容 */
        .custom-dialog-body {
            padding: 20px 28px 24px;
        }

        .custom-dialog-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            letter-spacing: -0.3px;
        }

        .custom-dialog-message {
            font-size: 15px;
            color: #666;
            line-height: 1.7;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        /* 对话框底部 */
        .custom-dialog-footer {
            padding: 0 28px 28px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

            .custom-dialog-btn {
            padding: 12px 28px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
            }

        .custom-dialog-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .custom-dialog-btn:hover::before {
            left: 100%;
        }

        .custom-dialog-btn.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
        }

        .custom-dialog-btn.primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.45);
        }

        .custom-dialog-btn.primary:active {
            transform: translateY(-1px);
        }

        .custom-dialog-btn.secondary {
            background: rgba(0, 0, 0, 0.05);
            color: #666;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        .custom-dialog-btn.secondary:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border-color: rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .custom-dialog-btn.danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.35);
        }

        .custom-dialog-btn.danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.45);
        }

        .custom-dialog-btn.danger:active {
            transform: translateY(-1px);
        }
        /* 装饰元素 */
        .custom-dialog::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 4px;
            background: inherit;
            border-radius: 0 0 4px 4px;
            opacity: 0.5;
            }

        /* 响应式 */
        @media (max-width: 480px) {
            .custom-dialog {
                min-width: 280px;
                max-width: 92%;
                margin: 20px;
                border-radius: 16px;
        }

            .custom-dialog-header {
                padding: 20px 24px 0;
            }

            .custom-dialog-body {
                padding: 18px 24px 20px;
            }

            .custom-dialog-footer {
                padding: 0 24px 24px;
                flex-direction: column;
            }

            .custom-dialog-btn {
                width: 100%;
                padding: 14px 20px;
            }

            .custom-dialog-icon {
                width: 48px;
                height: 48px;
                font-size: 24px;
            }
        }

        /* 动画 */
        @keyframes dialogIn {
            0% {
                opacity: 0;
                transform: scale(0.85) translateY(30px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes dialogOut {
            0% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
            100% {
                opacity: 0;
                transform: scale(0.85) translateY(30px);
            }
        }

        /* 防止滚动 */
        .custom-dialog-open {
            overflow: hidden !important;
        }
    `;
    document.head.appendChild(style);

    /**
     * 自定义 Dialog 类
     */
    class CustomDialog {
        constructor() {
            this.overlay = null;
            this.dialog = null;
        }

        /**
         * 创建对话框
         */
        createDialog(options) {
            // 隐藏已存在的对话框
            if (this.overlay) {
                this.hide();
            }

            // 创建遮罩层
            this.overlay = document.createElement('div');
            this.overlay.className = 'custom-dialog-overlay';

            // 创建对话框
            this.dialog = document.createElement('div');
            this.dialog.className = 'custom-dialog';

            // 图标 SVG
            const icons = {
                info: `<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`,
                success: `<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
                warning: `<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
                error: `<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
                confirm: `<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`
            };

            // 构建对话框 HTML
            let html = `
                <div class="custom-dialog-header">
                    <div class="d-flex align-items-center">
                        <div class="custom-dialog-icon ${options.type}">
                            ${icons[options.type] || icons.info}
                        </div>
                    </div>
                    ${options.showClose !== false ? '<button class="custom-dialog-close" onclick="CustomDialog.hide()">&times;</button>' : ''}
                </div>
                <div class="custom-dialog-body">
                    ${options.title ? `<div class="custom-dialog-title">${this.escapeHtml(options.title)}</div>` : ''}
                    <div class="custom-dialog-message">${this.escapeHtml(options.message || '')}</div>
                </div>
                <div class="custom-dialog-footer">
            `;

            // 添加按钮
            if (options.type === 'confirm') {
                html += `
                    <button class="custom-dialog-btn secondary" id="custom-dialog-cancel">${options.cancelText || '取消'}</button>
                    <button class="custom-dialog-btn danger" id="custom-dialog-confirm">${options.confirmText || '确认'}</button>
                `;
            } else {
                html += `
                    <button class="custom-dialog-btn primary" id="custom-dialog-ok">${options.okText || '确定'}</button>
                `;
            }

            html += '</div>';
            this.dialog.innerHTML = html;

            this.overlay.appendChild(this.dialog);
            document.body.appendChild(this.overlay);

            // 显示动画
            requestAnimationFrame(() => {
                this.overlay.classList.add('show');
            });

            // 点击遮罩关闭
            this.overlay.addEventListener('click', (e) => {
                if (e.target === this.overlay && options.clickOverlayClose !== false) {
                    this.hide();
                }
            });

            // 绑定按钮事件
            const okBtn = document.getElementById('custom-dialog-ok');
            if (okBtn) {
                okBtn.addEventListener('click', () => {
                    if (options.onOk) {
                        options.onOk();
                    }
                    this.hide();
                });
            }

            const cancelBtn = document.getElementById('custom-dialog-cancel');
            const confirmBtn = document.getElementById('custom-dialog-confirm');

            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => {
                    if (options.onCancel) {
                        options.onCancel();
                    }
                    this.hide();
                });
            }

            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => {
                    if (options.onConfirm) {
                        options.onConfirm();
                    }
                    this.hide();
                });
            }
        }

        /**
         * 隐藏对话框
         */
        hide() {
            if (this.overlay) {
                this.overlay.classList.remove('show');
                setTimeout(() => {
                    if (this.overlay && this.overlay.parentNode) {
                        this.overlay.parentNode.removeChild(this.overlay);
                    }
                    this.overlay = null;
                    this.dialog = null;
                }, 300);
            }
        }

        /**
         * 转义 HTML
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * 显示消息提示 (alert 替代)
         */
        alert(message, title = '', type = 'info', callback) {
            this.createDialog({
                type: type,
                title: title,
                message: message,
                okText: '确定',
                onOk: callback
            });
        }

        /**
         * 显示确认对话框 (confirm 替代)
         */
        confirm(message, title = '', onConfirm, onCancel) {
            this.createDialog({
                type: 'confirm',
                title: title,
                message: message,
                confirmText: '确认',
                cancelText: '取消',
                onConfirm: onConfirm,
                onCancel: onCancel
            });
        }

        /**
         * 显示成功提示
         */
        success(message, callback) {
            this.alert(message, '操作成功', 'success', callback);
        }

        /**
         * 显示错误提示
         */
        error(message, callback) {
            this.alert(message, '操作失败', 'error', callback);
        }

        /**
         * 显示警告提示
         */
        warning(message, callback) {
            this.alert(message, '警告', 'warning', callback);
        }
    }

    // 导出到全局
    const dialogInstance = new CustomDialog();
    window.CustomDialog = dialogInstance;

    // 关键修正：显式绑定 this 索引，防止在调用时丢失上下文
    window.CustomDialog.alert = window.CustomDialog.alert.bind(dialogInstance);
    window.CustomDialog.confirm = window.CustomDialog.confirm.bind(dialogInstance);
    window.CustomDialog.success = window.CustomDialog.success.bind(dialogInstance);
    window.CustomDialog.error = window.CustomDialog.error.bind(dialogInstance);
    window.CustomDialog.warning = window.CustomDialog.warning.bind(dialogInstance);

    // 兼容旧版本的函数调用方式
    if (typeof window.showCustomAlert === 'undefined') {
        window.showCustomAlert = function(message, title, type, callback) {
            window.CustomDialog.alert(message, title, type, callback);
        };
    }

    if (typeof window.showCustomConfirm === 'undefined') {
        window.showCustomConfirm = function(message, title, onConfirm, onCancel) {
            window.CustomDialog.confirm(message, title, onConfirm, onCancel);
        };
    }

})(window);

// --- 强制覆盖原生方法，实现全自动无感升级 ---
(function() {
    window.alert = function(message) {
        window.CustomDialog.alert(message, '提示', 'info');
    };

    window.confirm = function(message) {
        // 由于原生confirm是同步阻塞，而自定义UI是异步，
        // 这里提供一个基础覆盖。建议核心业务逻辑逐步改为回调模式。
        return new Promise((resolve) => {
            window.CustomDialog.confirm(message, '确认操作', 
                () => resolve(true), 
                () => resolve(false)
            );
        });
    };
})();