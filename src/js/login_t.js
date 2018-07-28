
function showUploadForm() {

					$('#wrapper').animate({
						opacity: 'show',
						height:'show'
					}, 'fast');

					$('#wrapper2').animate({
						height: 'hide',
						opacity: 'hide'
					}, 'slow');
}


function checkLogin() {
    var req = $.ajax({ // 检查当前登录用户会话的有效性
        type: 'GET',
        url: '/auth.php',
        dataType: 'json', 
        encode: true
    });

    req.done(function(data) {
        if(data.error && data.error == 'login') {
            console.log('login needed');
            $.cookie('loggedInUser', null);
        } else {
            showUploadForm();
        }
    });

    req.fail(function(data) {
        if(data.status == 200) {
            showUploadForm();
        }
    });
}

$(document).ready(function() {
    if($.cookie('userName') != undefined) {
        $('#user_name').val($.cookie('userName'));
    }
    if($('#uaser_name').val() != '') {
        $('#password').focus();
    }
    if($.cookie('loggedInUser') != undefined) {
        checkLogin();
    }

    // process the form
    $('#login').click(function(event) {

        // get the form data
        // there are many ways to get this data using jQuery (you can use the class or id also)
        var formData = {
            'userName': $('#user_name').val(),
            'password': $('#password').val()
        };

        // process the form
        $.ajax({
            type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
            url: 'login.php', // the url where we want to POST
            data: formData, // our data object
            dataType: 'json', // what type of data do we expect back from the server
            encode: true
        })
        // using the done promise callback
            .done(function(data) {

                // log data to the console so we can see
                //console.log(data['has-warning']);
                if(data['has-warning'] == false) {
					alert("登录成功！");

    if($.cookie('loggedInUser') != undefined) {
		var tp_str = "你好，"+$.cookie('loggedInUser');
        $('#loggedInUserNameId').text(tp_str);
    }
					$('#wrapper').animate({
						opacity: 'hide',
						height:'hide'
					}, 'fast');

					$('#wrapper2').animate({
						height: 'show',
						opacity: 'show'
					}, 'slow');
                } else {
					alert("登录失败，请确认用户名和密码");
                }

                // here we will handle errors and validation messages
            });

        // stop the form from submitting the normal way and refreshing the page
        event.preventDefault();
    });

    setInterval(function() {
        checkLogin();
    }, 10 * 60 * 1000); // 10分钟一次，保持心跳
});
