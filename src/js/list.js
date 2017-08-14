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
		$('#files-table').bootstrapTable('load', {total: 5, rows: data});
	});

    if($.cookie('loggedInUser') != undefined) {
        $('#loggedInUserNameId').text($.cookie('loggedInUser'));
    }

});

function ajaxDelete(key) {

	if(!confirm('确认删除？')) {
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
			console.log(data);
		},
        error: function (er) {
			console.log(er);
        }
    });
}


