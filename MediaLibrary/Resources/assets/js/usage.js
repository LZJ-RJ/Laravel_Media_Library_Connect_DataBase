import $ from 'jquery';
window.jQuery = $;
window.$ = $;
$(() => {

    /** 媒體庫 開始 **/
    if($('.mediaSelect').length){
        var input_select_column = '';
        var media_id = '';

        //找尋`div.mediaSelect`來顯示button。
        $.each( $('.mediaSelect'), function (key ,value) {
            $(value).find('button').remove();
            let button_html = '<button class="btn btn-primary">'+$(value).attr('data-button-text')+'</button>';
            $(value).append(button_html);
            if($(value).find('input[type="hidden"]').val()){
                media_id = $(value).find('input[type="hidden"]').val();
            }else{
                media_id = '';
            }
            if(media_id != ''){
                //若該input有value，就根據此value(media ID)去讀取media資料。
                $.ajax({
                    url :location.origin + '/getMediaData',
                    method: 'post',
                    data: {
                        'id': media_id,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success(msg){
                        if(msg.id){
                            $(value).append('<a href="javascript:void(0);">'+msg.name+'</a>');

                            //把新增的a連結跟button的點擊事件之間作連結。
                            let aElement = $(value).find('a');
                            $(aElement).off('click').on('click', function (e) {
                                aElement.parent('div').find('button').click();
                            });
                        }
                    }
                });
            }
        });

        //觸發bootstrap modal
        $(document).on('click', '.mediaSelect button', function (e) {
            e.preventDefault();
            $('.btn.btn-success.modalSelectButton').show();
            input_select_column = $(this);
            $('#mediaLibraryContainer .modal-body').html('');
            //藉由MediaLibraryController去得到Media Component的HTML，並塞到bootstrap的modal-body裡面。
            $.ajax({
                url :location.origin + '/getComponentMediaLibrary',
                method: 'get',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success(msg){
                    $('#mediaLibraryContainer .modal-body').html(msg);
                        if($('#app').length){
                            //用Vue JS觸發
                            new Vue({
                                el: '#app'
                            });
                        }
                        $("#mediaLibraryContainer").modal({
                            backdrop: 'static',
                            keyboard: false
                        });
                        $('#mediaLibraryContainer').modal('show');

                        $('#mediaLibraryContainer').css('overflow-y', 'hidden');
                }, fail(msg){}
            });
        });

        $(document).on('click', '#mediaLibraryContainer .modal-content .modal-footer .modalSelectButton', function (e) {
            if($('#mediaLibraryContainer .modal-content .modal-body #app .__file-box.mm-animated.bulk-selected img').length > 1 ||
                $('#mediaLibraryContainer .modal-content .modal-body #app .__file-box.mm-animated.selected img').length > 1){
                alert('(失敗)一個欄位套用一張圖片，所以只能單選。');
                return;
            }

            let value = $('#mediaLibraryContainer .modal-content .modal-body #app .__file-box.mm-animated.selected  img').attr('src');
            if(value !== undefined){
                value = value.split(location.origin+'/storage/mediaLibrary');
            }
            if(value !== undefined){
                value = value[1];
                $.ajax({
                    url :location.origin + '/getMediaData',
                    method: 'post',
                    data: {
                        'path': value,
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success(msg){
                        input_select_column.parent('div').find('input[type="hidden"]').val(msg['id']);
                        input_select_column.text('已變更');
                        input_select_column.parent('div').find('a').remove();
                        input_select_column.parent('div').append('<a href="javascript:void(0);">'+msg['name']+'</a>');

                        //把新增的a連結跟button的點擊事件之間作連結。
                        let aElement =  input_select_column.parent('div').find('a');
                        $(aElement).off('click').on('click', function (e) {
                            aElement.parent('div').find('button').click();
                        });
                    }
                });
            }
        });

        //在隱藏bootstrap的modal時，一併把modal-body的html內容清空。
        $(document).on('hidden.bs.modal', '#mediaLibraryContainer', function (e) {
            $(this).find('.modal-body').html('');
        });

    }
    /** 媒體庫 結束 **/

});
