// 原生相册筛选功能
$(document).ready(function() {
    // 相册筛选
    $('#albumFilter').on('change', function() {
        const albumId = $(this).val();
        const rows = $('#photoTbody tr');

        if (albumId === '') {
            rows.show();
            $('#filterInfo').text('');
        } else {
            rows.each(function() {
                const rowAlbumId = $(this).data('album-id').toString();
                if ((albumId === 'null' && rowAlbumId === '0') || rowAlbumId === albumId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            const albumName = $(this).find('option:selected').text();
            $('#filterInfo').text(`(${albumName})`);
        }

        updateSerialNumbers();
    });

    // 更新可见行的序号
    function updateSerialNumbers() {
        let num = 0;
        $('#photoTbody tr:visible').each(function() {
            num++;
            $(this).find('.SerialNumber').text(num);
        });
    }
});
