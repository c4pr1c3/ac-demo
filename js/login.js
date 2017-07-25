$(document).ready(function() {
	
  $('#iUserName').val($.cookie('userName'));
  if($('#iUserName').val() != '') {
    $('#iPassword').focus();
  }
  if($.cookie('loggedInUser') != undefined) {
    $('#loginFormDiv').addClass('hidden');
    $('#mainFormDiv').removeClass('hidden').addClass('container');
    $('#loggedInUserNameId').text($.cookie('loggedInUser'));
  }
  // process the form
  $('#loginForm').submit(function(event) {

    // get the form data
    // there are many ways to get this data using jQuery (you can use the class or id also)
    var formData = {
      'userName': $('#iUserName').val(),
      'password': $('#iPassword').val()
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
        console.log(data['has-warning']);
        if(data['has-warning'] == false) {
          $('#loginFormDiv').addClass('hidden');
          $('#mainFormDiv').removeClass('hidden').addClass('container');
        } else {
          $('#loginFormDiv').addClass('has-warning');
          $('#login2prompt').text(data['msg']);
        }

        // here we will handle errors and validation messages
      });

    // stop the form from submitting the normal way and refreshing the page
    event.preventDefault();
  });

});
