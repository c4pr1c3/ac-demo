function listFilesByPage(page) {
    var $pagination = $('#files-pagination');
	$.ajax({
		type: 'GET', // define the type of HTTP verb we want to use (POST for our form)
		url: 'list.php?page=' + page, // the url where we want to POST
		data: '', // our data object
		dataType: 'json', // what type of data do we expect back from the server
		encode: true
	})
	// using the done promise callback
		.done(function(data) {
			// log data to the console so we can see
			console.log(data);
			pageOptions = {
				'totalPages': data['totalPages'],
				'visiblePages': data['visiblePages'],
				onPageClick: function (event, page) {
					listFilesByPage(page);
				}
			};
			$('#files-table').bootstrapTable({
				columns: [{
					field: 'id',
					title: 'ID'
				}, {
					field: 'name',
					title: '文件名'
				}, {
					field: 'sha256',
					title: 'sha256'
				}, {
					field: 'create_time',
					title: '上传时间'
				}, {
					field: 'size',
					title: '文件大小（字节）'
				}],
				data: data['content']
			});
			$pagination.twbsPagination(pageOptions);
		});
}


$(document).ready(function() {
    // ajax get files list

	$(function () {
		$('#files-table').bootstrapTable('load', {total: 5, rows: ''});
	});

    if($.cookie('loggedInUser') != undefined) {
        $('#loggedInUserNameId').text($.cookie('loggedInUser'));
    }

});

function ajaxDelete(obj) {

	tr = $(obj.parentNode.parentNode);
	console.log(tr);

	key = tr[0].childNodes[2].innerText;
	filename = tr.find('td a')[0].innerText;

	if(!confirm('确认删除文件\n ' + filename + ' ？')) {
		return;
	}

    // data you may need
    console.log(key);

    $.ajax({
        type: "POST",
        url: "delete.php",
        data: { 'key': key },
// You are expected to receive the generated JSON (json_encode($data))
        dataType: "json",
        success: function (data) {
			if(data['error']) {
				$('#delTooltip').text(filename + " 删除失败 ").addClass('alert alert-danger');
			} else {
				tr.fadeOut(2000);
				$('#delTooltip').text(filename + " 已删除 ").addClass('alert alert-success');
			}

			$("#delTooltip").fadeTo(2000, 500).slideUp(500, function(){
				$("#delTooltip").slideUp(500);
				$("#delTooltip").removeClass('alert-danger alert-success');
			});
			console.log(data);
		},
        error: function (er) {
			$('#delTooltip').text(filename + " 删除失败 ").addClass('alert alert-danger');
			$("#delTooltip").fadeTo(2000, 500).slideUp(500, function(){
				$("#delTooltip").slideUp(500);
				$("#delTooltip").removeClass('alert-danger alert-success');
			});
			console.log(er);
        }
    });
}

function ajaxShare(obj) {

	tr = $(obj.parentNode.parentNode);
	console.log(tr);

	fkey = tr[0].childNodes[2].innerText;
	fid  = tr.find('td')[0].innerText
	fsname = tr.find('td a')[0].innerText;
	fstime = tr[0].childNodes[3].innerText; 
	fsize = tr[0].childNodes[4].innerText; 

    if(fsize > 1024) {
        if(fsize > 1024 * 1024) {
            fsize = parseFloat(fsize / (1024*1024)).toFixed(2);
            fsize = fsize + " MB";
        } else {
            fsize = parseFloat(fsize / 1024).toFixed(2);
            fsize = fsize + " KB";
        }
    } else {
        fsize = fsize + " B";
    }

    $("#fid").val(fid);
    $("#fkey").val(fkey);
    $("#fsname")[0].innerText = fsname;
    $("#fstime")[0].innerText = fstime;
    $("#fsize")[0].innerText = fsize;

	// AJAX POST to get file share url
	$(function(){
		$('#shareForm').submit(function(e){
			e.preventDefault();
			$.ajax({
				url: 'share.php', //this is the submit URL
				type: 'POST', //or POST
				data: $('#shareForm').serialize(),
				dataType: 'json',
				encode: true,
				success: function(data){
                    $('#shareModal').modal('toggle');
                    $('#shareResultModal').modal('toggle');
					console.log('successfully submitted')
					console.log(data);
					console.log(data.url);
					console.log(data.access_code);
                    $('#sharedLink')[0].innerText = data.url + ' 分享码：' + data.access_code;
                    $('.panel-body').css('word-wrap', 'break-word');
				}
			});
		});
	});
}

function ajaxCheck(obj) {

	tr = $(obj.parentNode.parentNode);
	console.log(tr);

	fid  = tr.find('td')[0].innerText
	filename = tr.find('td a')[0].innerText;

	if(!confirm('要核验文件 ' + filename + ' 的完整性，请确保最近一次操作为下载该文件到本地！')) {
		return;
	}
	$.ajax({
        type: "POST",
        url: "check.php",
        data: { 'fid': fid },
		dataType: 'json',
    success: function (data) {
		if(data['error']){
			alert (filename +"核验完整性失败，文件被破坏！")
		
	}
		else{
			
			alert (filename +"核验完整性成功，文件完整！")
		}
		},
        error: function () 
		{
			alert (filename +"核验完整性失败，请稍后再试！")
        }
    });
}

