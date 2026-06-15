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
                    <button type="button" class="btn btn-success mb-3" onclick="showUploadModal()">
                        <i class="mdi mdi-upload"></i> 上传备份
                    </button>
                    <button type="button" class="btn btn-danger mb-3" id="batchDeleteBtn" onclick="batchDeleteBackups()" disabled>
                        <i class="mdi mdi-delete"></i> 批量删除
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="50"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                                    <th>备份文件</th>
                                    <th>文件大小</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody id="backupList">
                                <tr>
                                    <td colspan="5" class="text-center">加载中...</td>
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
                html = '<tr><td colspan="5" class="text-center">暂无备份</td></tr>';
            } else {
                res.files.forEach(file => {
                    html += `<tr>
                        <td><input type="checkbox" class="backup-checkbox" value="${file.name}" onchange="updateBatchButton()"></td>
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
                            <a href="javascript:void(0)" onclick="deleteBackup('${file.name}')" class="action-icon ml-2 text-danger">
                                <i class="mdi mdi-delete"></i> 删除
                            </a>
                        </td>
                    </tr>`;
                });
            }
            $('#backupList').html(html);
            updateBatchButton();
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

// 全选/取消全选
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.backup-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    updateBatchButton();
}

// 更新批量删除按钮状态
function updateBatchButton() {
    const checkboxes = document.querySelectorAll('.backup-checkbox:checked');
    const btn = document.getElementById('batchDeleteBtn');
    const selectAll = document.getElementById('selectAll');

    if (checkboxes.length > 0) {
        btn.disabled = false;
        btn.innerHTML = `<i class="mdi mdi-delete"></i> 批量删除 (已选${checkboxes.length}项)`;
    } else {
        btn.disabled = true;
        btn.innerHTML = '<i class="mdi mdi-delete"></i> 批量删除';
    }

    // 更新全选框状态
    const allCheckboxes = document.querySelectorAll('.backup-checkbox');
    if (allCheckboxes.length > 0) {
        selectAll.checked = checkboxes.length === allCheckboxes.length;
    }
}

// 单个删除
function deleteBackup(filename) {
    if (!confirm('确定要删除备份文件 "' + filename + '" 吗？')) return;

    toastr.info('正在删除...');
    $.ajax({
        url: 'deleteBackup.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({filenames: [filename]}),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                loadBackups();
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error('删除失败');
        }
    });
}

// 批量删除
function batchDeleteBackups() {
    const checkboxes = document.querySelectorAll('.backup-checkbox:checked');
    if (checkboxes.length === 0) {
        toastr.warning('请先选择要删除的文件');
        return;
    }

    const filenames = Array.from(checkboxes).map(cb => cb.value);
    const count = filenames.length;

    if (!confirm('确定要删除 ' + count + ' 个备份文件吗？\n此操作不可恢复！')) return;

    toastr.info('正在删除...');
    $.ajax({
        url: 'deleteBackup.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({filenames: filenames}),
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                document.getElementById('selectAll').checked = false;
                loadBackups();
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error('批量删除失败');
        }
    });
}

$(function() {
    loadBackups();
});
</script>

<?php include_once 'Footer.php'; ?>
