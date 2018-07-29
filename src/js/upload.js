// ref: http://plugins.krajee.com/file-input

$("#uploadFiles").fileinput({
		language: 'zh',
		uploadUrl: '/upload.php',
		uploadAsync: true,
		showUpload: true,
		minFileCount: 1,
		maxFileCount: 10,
		mainClass: "input-group-lg", //文件输入框为大型号
		allowedFileExtensions: ["doc", "docx", "xls", "xlsx", "ppt", "pptx", "zip", "pdf", "png", "jpg", "jpeg", "gif", "bmp", "svg", "ico"],
        maxFileSize: 10240 , // float, the maximum file size for upload in KB. If greater than this, a validation error is thrown using the msgSizeTooLarge setting. If set to 0, it means size allowed is unlimited. Defaults to 0.
        initialPreviewAsData: true , // identify if you are sending preview data only and not the markup
        // uploadExtraData: {hash: hashArray}
});
$('#uploadFiles').on('filepreajax', function(event, previewId, index) {
    console.log('File pre ajax triggered');
});
$('#uploadFiles').on('filepreupload', function(event, data, previewId, index) {
    var form = data.form, files = data.files, response = data.response, reader = data.reader, 
    extra = data.extra;
	// TODO 文件摘要和在客户端进行计算，在文件传输之前先传输校验和
	var hashArray = new Array();
	for (var i = 0; i < data.filescount; i++) {
    	reader.readAsText(data.files[i]);
    	reader.onload = function(e) {
		    var rawLog = reader.result;
		    var h = sodium.crypto_generichash(32, rawLog);
			hashArray[i] = sodium.to_hex(h);
			console.log(sodium.to_hex(h));
		};
	}
    console.log('File pre upload triggered');
});
$('#uploadFiles').on('fileuploaded', function(event, data, previewId, index) {
    var form = data.form, files = data.files, extra = data.extra,
    response = data.response, reader = data.reader;
    console.log('File uploaded triggered');
});