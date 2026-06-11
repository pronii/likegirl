<?php
session_start();
include_once 'Nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">音乐管理</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="header-title mb-0">音乐列表</h4>
                        <button type="button" class="btn btn-primary" onclick="showAddModal()">
                            <i class="mdi mdi-plus"></i> 添加音乐
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th>封面</th>
                                    <th>歌曲名称</th>
                                    <th>艺术家</th>
                                    <th>时长</th>
                                    <th>状态</th>
                                    <th style="width: 150px;">操作</th>
                                </tr>
                            </thead>
                            <tbody id="musicList">
                                <tr>
                                    <td colspan="7" class="text-center">加载中...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="musicModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">添加音乐</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="musicForm">
                    <input type="hidden" id="musicId" name="id">

                    <div class="form-group">
                        <label for="title">歌曲名称 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="例如：告白气球">
                    </div>

                    <div class="form-group">
                        <label for="artist">艺术家</label>
                        <input type="text" class="form-control" id="artist" name="artist" placeholder="例如：周杰伦">
                    </div>

                    <div class="form-group">
                        <label for="music_url">音乐文件URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="music_url" name="music_url" required placeholder="https://example.com/music.mp3">
                    </div>

                    <div class="form-group">
                        <label for="cover_url">封面图URL</label>
                        <input type="url" class="form-control" id="cover_url" name="cover_url" placeholder="https://example.com/cover.jpg">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="duration">时长(秒)</label>
                            <input type="number" class="form-control" id="duration" name="duration" value="0" min="0">
                        </div>

                        <div class="form-group col-md-6">
                            <label for="sort_order">排序</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_enabled" name="is_enabled" checked>
                            <label class="custom-control-label" for="is_enabled">启用</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="saveMusic()">保存</button>
            </div>
        </div>
    </div>
</div>

<script src="../Style/toastr/toastr.min.js"></script>
<script>
$(document).ready(function() {
    loadMusicList();
});

function loadMusicList() {
    $.get('musicList.php', function(res) {
        if (res.success) {
            renderMusicList(res.data);
        } else {
            toastr.error(res.message || '加载失败');
        }
    }, 'json').fail(function() {
        toastr.error('网络错误');
    });
}

function renderMusicList(list) {
    if (list.length === 0) {
        $('#musicList').html('<tr><td colspan="7" class="text-center">暂无音乐</td></tr>');
        return;
    }

    let html = '';
    list.forEach(item => {
        const status = item.is_enabled == 1 ?
            '<span class="badge badge-success">启用</span>' :
            '<span class="badge badge-secondary">禁用</span>';

        const cover = item.cover_url ?
            `<img src="${item.cover_url}" alt="${item.title}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">` :
            '<div style="width: 40px; height: 40px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;"><i class="mdi mdi-music"></i></div>';

        const duration = formatDuration(item.duration);

        html += `<tr>
            <td>${item.id}</td>
            <td>${cover}</td>
            <td>${item.title}</td>
            <td>${item.artist || '-'}</td>
            <td>${duration}</td>
            <td>${status}</td>
            <td>
                <a href="javascript:void(0)" class="action-icon" onclick="editMusic(${item.id})" title="编辑">
                    <i class="mdi mdi-pencil"></i>
                </a>
                <a href="javascript:void(0)" class="action-icon ml-2" onclick="toggleStatus(${item.id}, ${item.is_enabled})" title="${item.is_enabled == 1 ? '禁用' : '启用'}">
                    <i class="mdi mdi-${item.is_enabled == 1 ? 'eye-off' : 'eye'}"></i>
                </a>
                <a href="javascript:void(0)" class="action-icon ml-2" onclick="deleteMusic(${item.id})" title="删除">
                    <i class="mdi mdi-delete text-danger"></i>
                </a>
            </td>
        </tr>`;
    });
    $('#musicList').html(html);
}

function formatDuration(seconds) {
    if (!seconds || seconds <= 0) return '-';
    const min = Math.floor(seconds / 60);
    const sec = seconds % 60;
    return `${min}:${sec.toString().padStart(2, '0')}`;
}

function showAddModal() {
    $('#modalTitle').text('添加音乐');
    $('#musicForm')[0].reset();
    $('#musicId').val('');
    $('#is_enabled').prop('checked', true);
    $('#musicModal').modal('show');
}

function editMusic(id) {
    $.get('musicGet.php', {id: id}, function(res) {
        if (res.success) {
            $('#modalTitle').text('编辑音乐');
            $('#musicId').val(res.data.id);
            $('#title').val(res.data.title);
            $('#artist').val(res.data.artist);
            $('#music_url').val(res.data.music_url);
            $('#cover_url').val(res.data.cover_url);
            $('#duration').val(res.data.duration);
            $('#sort_order').val(res.data.sort_order);
            $('#is_enabled').prop('checked', res.data.is_enabled == 1);
            $('#musicModal').modal('show');
        } else {
            toastr.error(res.message || '加载失败');
        }
    }, 'json');
}

function saveMusic() {
    const form = $('#musicForm')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const data = {
        id: $('#musicId').val(),
        title: $('#title').val(),
        artist: $('#artist').val(),
        music_url: $('#music_url').val(),
        cover_url: $('#cover_url').val(),
        duration: $('#duration').val(),
        sort_order: $('#sort_order').val(),
        is_enabled: $('#is_enabled').is(':checked') ? 1 : 0
    };

    $.post('musicSave.php', data, function(res) {
        if (res.success) {
            toastr.success(res.message);
            $('#musicModal').modal('hide');
            loadMusicList();
        } else {
            toastr.error(res.message || '保存失败');
        }
    }, 'json').fail(function() {
        toastr.error('网络错误');
    });
}

function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus == 1 ? 0 : 1;
    $.post('musicToggle.php', {id: id, is_enabled: newStatus}, function(res) {
        if (res.success) {
            toastr.success(res.message);
            loadMusicList();
        } else {
            toastr.error(res.message || '操作失败');
        }
    }, 'json');
}

function deleteMusic(id) {
    if (!confirm('确定要删除这首音乐吗？')) return;

    $.post('musicDelete.php', {id: id}, function(res) {
        if (res.success) {
            toastr.success(res.message);
            loadMusicList();
        } else {
            toastr.error(res.message || '删除失败');
        }
    }, 'json');
}
</script>

<?php
include_once 'Footer.php';
?>
