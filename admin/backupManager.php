<?php
session_start();
include_once 'Nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">数据备份与恢复</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-3">备份管理</h4>

                    <button type="button" class="btn btn-primary mb-3" onclick="createBackup()">
                        <i class="mdi mdi-database-export"></i> 创建新备份
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>备份文件</th>
                                    <th>文件大小</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="backupList">
                                <tr>
                                    <td colspan="4" class="text-center">加载中...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../Style/toastr/toastr.min.js"></script>
<script>
function loadBackups() {
    $.get('backupList.php', function(res) {
        if (res.success) {
            let html = '';
            if (res.files.length === 0) {
                html = '<tr><td colspan="4" class="text-center">暂无备份</td></tr>';
            } else {
                res.files.forEach(file => {
                    html += `<tr>
                        <td>${file.name}</td>
                        <td>${(file.size / 1024).toFixed(2)} KB</td>
                        <td>${file.date}</td>
                        <td>
                            <a href="javascript:void(0)" onclick="restoreBackup('${file.name}')" class="action-icon">
                                <i class="mdi mdi-database-import"></i> 恢复
                            </a>
                            <a href="downloadBackup.php?filename=${file.name}" class="action-icon ml-2">
                                <i class="mdi mdi-download"></i> 下载
                            </a>
                        </td>
                    </tr>`;
                });
            }
            $('#backupList').html(html);
        }
    }, 'json');
}

function createBackup() {
    if (!confirm('确定要创建数据备份吗？')) return;

    toastr.info('正在备份，请稍候...');
    $.post('backup.php', function(res) {
        if (res.success) {
            toastr.success(res.message);
            loadBackups();
        } else {
            toastr.error(res.message);
        }
    }, 'json');
}

function restoreBackup(filename) {
    if (!confirm('确定要恢复此备份吗？\n⚠️ 当前数据将被覆盖！')) return;

    toastr.info('正在恢复，请稍候...');
    $.post('restore.php', {filename: filename}, function(res) {
        if (res.success) {
            toastr.success(res.message);
        } else {
            toastr.error(res.message);
        }
    }, 'json');
}

$(function() {
    loadBackups();
});
</script>

<?php include_once 'Footer.php'; ?>
