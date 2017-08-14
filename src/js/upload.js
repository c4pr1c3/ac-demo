// ref: http://plugins.krajee.com/file-input

$("#uploadFiles").fileinput({
		language: 'zh',
		uploadUrl: '/upload.php',
		uploadAsync: true,
		showUpload: true,
		minFileCount: 1,
		maxFileCount: 5,
		mainClass: "input-group-lg",
        maxFileSize: "10240" // float, the maximum file size for upload in KB. If greater than this, a validation error is thrown using the msgSizeTooLarge setting. If set to 0, it means size allowed is unlimited. Defaults to 0.
});
$('#uploadFiles').on('filepreajax', function(event, previewId, index) {
    console.log('File pre ajax triggered');
});
$('#uploadFiles').on('filepreupload', function(event, data, previewId, index) {
    var form = data.form, files = data.files, extra = data.extra,
    response = data.response, reader = data.reader;
	// TODO 文件摘要和在客户端进行计算
	// TODO 在文件传输之前先传输校验和
    console.log('File pre upload triggered');
});
$('#uploadFiles').on('fileuploaded', function(event, data, previewId, index) {
    var form = data.form, files = data.files, extra = data.extra,
    response = data.response, reader = data.reader;
    console.log('File uploaded triggered');
});


