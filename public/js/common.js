$(document).ready(function() {
    $('.datatables').DataTable({});

    var subscribers = $('.datatablesssp').DataTable({
        serverSide: true,
        ajax: '/admin/getsubscribers'
    });

    var pending_notifications = $('.datatablespn').DataTable({
        serverSide: true,
        ajax: '/admin/notifications/getPendingNotifications'
    });


    var notifications_log = $('.datatablesnl').DataTable({
        serverSide: true,
        ajax: '/admin/notifications/getNotificationsLog'
    });


    var users = $('.datatablesUsers').DataTable({
        serverSide: true,
        ajax: '/admin/getUsers'
    });


    setInterval(function(){
        pending_notifications.ajax.reload( null, false );
        notifications_log.ajax.reload( null, false );
    }, 5000);


    /*
    start timer when page loads
    any mouse movement or key down will reset the timer
    timer = 15 min
    if timer < 1 minute
      show pop up with the remaining time counting down
       if they click continue close window and restart timer
        fire get  request to /heartbeat
       If nothing go to /admin/logout
     */
var LOGOUT_DURATION = 840000;
var POPUP_DURATION = 10000;


$(window).on('mousemove',function(){
  window.clearTimeout(timer);
  timer = setTimeout(popup, LOGOUT_DURATION );



});

function setTimer(){
return setTimeout(popup, LOGOUT_DURATION );

}
timer = setTimer();

function popup() {
  logoutDialog = bootbox.dialog({
    message: "<p> you will logged out in one minute</p>",
    title: '<i class="fa fa-time"></i> inactivity',
    backdrop: true,
    buttons:{
      Assign: {
        label: 'Stay Logged In',
        className: 'btn-primary',
        callback: function() {
          doAjaxCall('get', '/admin/heartbeat', 'json', {}, function (response) {
            window.clearTimeout(popUpTimer);


          });
        }
      }
    }
  });

 var popUpTimer = setTimeout(function() {
window.location.href = "/admin/logout";
  },POPUP_DURATION);




}

    $('.container-fluid').on('click', '.showSubscriberModal', function(){
        var subscriber_id = $(this).attr('data-subscriber-id');
        sss.openComments(subscriber_id);
    });


    $('.container-fluid').on('click', '#addnotification', function(){
        sss.createNotificationModal();
    });


    $('.container-fluid').on('click', '#createuser', function(){
        sss.createUserModal();
    });

    $('.container-fluid').on('click', '#edituser', function(){
        var id = $(this).attr('data-user-id');
        if( id !== undefined && id !== null && id != '' ) {
            sss.editUserModal(id);
        }else{
            $.bootstrapGrowl("Unable to edit user!", {type: 'danger'});
        }
    });

    $('.container-fluid').on('click', '#deleteuser', function(){
        var id = $(this).attr('data-user-id');
        if( id !== undefined && id !== null && id != '' ) {
            bootbox.confirm({
                message: "Are you sure you what to delete this user?",
                callback: function (result) {
                    if( result ) {
                        sss.deleteUser(id);
                    }
                }
            });
        }else{
            $.bootstrapGrowl("Unable to delete user!", {type: 'danger'});
        }
    });

    $('.container-fluid').on('click', '#sendNotification', function(){
        var id = $(this).attr('data-id');
        if( id !== undefined && id !== null && id != '' ) {
            bootbox.confirm({
                message: "Are you sure you what to send this notification now?",
                callback: function (result) {
                    if( result ){
                        sss.sendNotification(id);
                    }
                }
            });
        }else{
            $.bootstrapGrowl("Unable to delete notification!", {type: 'danger'});
        }
    });

    $('.container-fluid').on('click', '#editNotification', function(){
        var id = $(this).attr('data-id');
        if( id !== undefined && id !== null && id != '' ) {
            sss.editNotification(id);
        }else{
            $.bootstrapGrowl("Unable to edit notification!", {type: 'danger'});
        }
    });


    $('.container-fluid').on('click', '#deleteNotification', function(){
        var id = $(this).attr('data-id');
        if( id !== undefined && id !== null && id != '' ) {
            bootbox.confirm({
                message: "Are you sure you what to delete this notification?",
                callback: function (result) {
                    if( result ) {
                        sss.deleteNotification(id);
                    }
                }
            });
        }else{
            $.bootstrapGrowl("Unable to delete notification!", {type: 'danger'});
        }
    });


    var sss = {
        openComments: function( subscriber_id ){
            var dialog;
            doAjaxCall('get', '/admin/getsubscribertemplate/' + subscriber_id, 'html', {}, function(result){
                dialog = bootbox.dialog({
                    message: result,
                    title: '<i class="fa fa-user"></i> Subscriber #'+ subscriber_id +' Comments',
                    buttons:{
                        Assign: {
                            label: 'Save Comment',
                            className: 'btn-primary',
                            callback: function() {
                                var comment = $('#comment').val();
                                if( comment !== undefined && comment !== null && comment != '' ) {
                                    doAjaxCall('post', '/admin/savecomment', 'json', {comment: comment, subscriber_id: subscriber_id}, function (response) {
                                        if( response.status == 'success' ){
                                            doAjaxCall('get', '/admin/getsubscribertemplate/' + subscriber_id, 'html', {}, function(result){
                                                $('.summernote').summernote('destroy');
                                                $('.modal-body').html(result);
                                            });
                                            subscribers.ajax.reload( null, false );
                                            $.bootstrapGrowl("Comment added!", {type: 'success'});
                                        }else{
                                            $.bootstrapGrowl("Unable to add comment!", {type: 'danger'});
                                        }
                                    });
                                }
                                else{
                                    $.bootstrapGrowl("Must add comment text!", {type: 'danger'});
                                }
                                return false;
                            }
                        },
                        Close: {
                            label: 'Close',
                            className: 'btn-danger'
                        }
                    }
                });
            });
        },

        createUserModal: function(){
            var dialog;
            doAjaxCall('get', '/admin/usertemplate', 'html', {}, function(result){
                dialog = bootbox.dialog({
                    message: result,
                    title: '<i class="fa fa-user"></i> Create User',
                    buttons:{
                        Assign: {
                            label: 'Create User',
                            className: 'btn-success',
                            callback: function() {
                                var error = [];
                                var checkInput = ['first_name', 'last_name', 'email', 'password', 'password2', 'role', 'is_active'];
                                var vardata = {};
                                $.each(checkInput, function(index, input){
                                    var inputv = $('#' + input).val();
                                    if( ! inputv || inputv == null )
                                    {
                                        error.push({input: input, message: input + ' is required!'});
                                    } else {
                                        vardata[input] = inputv;
                                    }
                                });


                                if( ! error.length ) {
                                    doAjaxCall('post', '/admin/createuser', 'json', vardata, function (response) {
                                        if( response.status == 'success' ){
                                            users.ajax.reload( null, false ); //datatables reload!
                                            dialog.modal('hide');
                                            $.bootstrapGrowl("User added!!", {type: 'success'});

                                        }else{
                                            if( typeof response.message === 'object' ){
                                                $.each(response.message, function(i, message){
                                                    $.bootstrapGrowl(message, {type: 'danger'});
                                                });
                                            } else {
                                                $.bootstrapGrowl("Unable to add user! - " + response.message, {type: 'danger'});
                                            }
                                        }
                                    });
                                }
                                else{
                                    //focus input
                                    $('#' + error[0]['input']).focus();
                                    $.each(error, function(i, error){
                                        $.bootstrapGrowl(error['message'], {type: 'danger'});
                                    });

                                }
                                return false;
                            }
                        },
                        Close: {
                            label: 'Cancel',
                            className: 'btn-danger'
                        }
                    }
                });
            });
        },
        editUserModal: function(id){
            var dialog;
            doAjaxCall('get', '/admin/usertemplate', 'html', {id: id}, function(result){
                dialog = bootbox.dialog({
                    message: result,
                    title: '<i class="fa fa-user"></i> Edit User',
                    buttons:{
                        Assign: {
                            label: 'Edit User',
                            className: 'btn-success',
                            callback: function() {
                                var error = [];
                                var checkInput = ['first_name', 'last_name', 'email', 'role', 'is_active'];
                                var vardata = {};
                                $.each(checkInput, function(index, input){
                                    var inputv = $('#' + input).val();
                                    if( ! inputv || inputv == null )
                                    {
                                        error.push({input: input, message: input + ' is required!'});
                                    } else {
                                        vardata[input] = inputv;
                                    }
                                });


                                if( ! error.length ) {
                                    vardata['id'] = id;
                                    vardata['password'] = $('#password').val();
                                    vardata['password2'] = $('#password2').val();
                                    doAjaxCall('post', '/admin/edituser', 'json', vardata, function (response) {
                                        if( response.status == 'success' ){
                                            users.ajax.reload( null, false ); //datatables reload!
                                            dialog.modal('hide');
                                            $.bootstrapGrowl("User Saved!!", {type: 'success'});

                                        }else{
                                            if( typeof response.message === 'object' ){
                                                $.each(response.message, function(i, message){
                                                    $.bootstrapGrowl(message, {type: 'danger'});
                                                });
                                            } else {
                                                $.bootstrapGrowl("Unable to save user! - " + response.message, {type: 'danger'});
                                            }
                                        }
                                    });
                                }
                                else{
                                    //focus input
                                    $('#' + error[0]['input']).focus();
                                    $.each(error, function(i, error){
                                        $.bootstrapGrowl(error['message'], {type: 'danger'});
                                    });

                                }
                                return false;
                            }
                        },
                        Close: {
                            label: 'Cancel',
                            className: 'btn-danger'
                        }
                    }
                });
            });
        },
        deleteUser: function(id){
            doAjaxCall('post', '/admin/deleteuser', 'json', {id: id}, function (response) {
                if( response.status == 'success' )
                {
                    $.bootstrapGrowl("User Deleted!", {type: 'success'});
                } else {
                    $.bootstrapGrowl("Unable to delete User!", {type: 'danger'});
                }
                users.ajax.reload( null, false );
            });
        },
        createNotificationModal: function(){
            var dialog;
            doAjaxCall('get', '/admin/notifications/notificationTemplate', 'html', {}, function(result){
                dialog = bootbox.dialog({
                    message: result,
                    title: '<i class="fa fa-comment"></i> Create Notification',
                    buttons:{
                        Assign: {
                            label: 'Create Notification',
                            className: 'btn-success',
                            callback: function() {
                                var error = [];
                                var checkInput = ['cleaning_date', 'send_date', 'area', 'route', 'type'];
                                var vardata = {};
                                $.each(checkInput, function(index, input){
                                    var inputv = $('#' + input).val();
                                    if( ! inputv || inputv == null )
                                    {
                                        error.push({input: input, message: input + ' is required!'});
                                    } else {
                                        vardata[input] = inputv;
                                    }
                                });


                                if( ! error.length ) {
                                    doAjaxCall('post', '/admin/notifications/savenotification', 'json', vardata, function (response) {
                                        if( response.status == 'success' ){
                                            pending_notifications.ajax.reload( null, false ); //datatables reload!
                                            dialog.modal('hide');
                                            $.bootstrapGrowl("Notification added!!", {type: 'success'});

                                        }else{
                                            $.bootstrapGrowl("Unable to add Notification!", {type: 'danger'});
                                        }
                                    });
                                }
                                else{
                                    //focus input
                                    $('#' + error[0]['input']).focus();
                                    $.each(error, function(i, error){
                                        $.bootstrapGrowl(error['message'], {type: 'danger'});
                                    });

                                }
                                return false;
                            }
                        },
                        Close: {
                            label: 'Cancel',
                            className: 'btn-danger'
                        }
                    }
                });
            });
        },
        editNotification: function(id){
            var dialog;
            doAjaxCall('get', '/admin/notifications/notificationTemplate', 'html', {id: id}, function(result){
                dialog = bootbox.dialog({
                    message: result,
                    title: '<i class="fa fa-comment"></i> Edit Notification',
                    buttons:{
                        Assign: {
                            label: 'Save Notification',
                            className: 'btn-success',
                            callback: function() {
                                var error = [];
                                var checkInput = ['cleaning_date', 'send_date', 'area', 'route', 'type'];
                                var vardata = {};
                                $.each(checkInput, function(index, input){
                                    var inputv = $('#' + input).val();
                                    if( ! inputv || inputv == null )
                                    {
                                        error.push({input: input, message: input + ' is required!'});
                                    } else {
                                        vardata[input] = inputv;
                                    }
                                });


                                if( ! error.length ) {
                                    vardata['id'] = id;
                                    doAjaxCall('post', '/admin/notifications/editnotification', 'json', vardata, function (response) {
                                        if( response.status == 'success' ){
                                            pending_notifications.ajax.reload( null, false ); //datatables reload!
                                            dialog.modal('hide');
                                            $.bootstrapGrowl("Notification updated!", {type: 'success'});

                                        }else{
                                            $.bootstrapGrowl("Unable to update Notification!", {type: 'danger'});
                                        }
                                    });
                                }
                                else{
                                    //focus input
                                    $('#' + error[0]['input']).focus();
                                    $.each(error, function(i, error){
                                        $.bootstrapGrowl(error['message'], {type: 'danger'});
                                    });

                                }
                                return false;
                            }
                        },
                        Close: {
                            label: 'Cancel',
                            className: 'btn-danger'
                        }
                    }
                });
            });
        },
        deleteNotification: function(id){
            doAjaxCall('post', '/admin/notifications/deletenotification', 'json', {id: id}, function (response) {
                if( response.status == 'success' )
                {
                    $.bootstrapGrowl("Notification Deleted!", {type: 'success'});
                } else {
                    $.bootstrapGrowl("Unable to delete notification!", {type: 'danger'});
                }
                pending_notifications.ajax.reload( null, false );
                notifications_log.ajax.reload( null, false );
            });
        },
        sendNotification: function(id){
            doAjaxCall('get', '/admin/notifications/sendnotification', 'json', {id: id}, function (response) {
                if( response.status == 'success' )
                {
                    $.bootstrapGrowl("Notification Sent!", {type: 'success'});
                } else {
                    $.bootstrapGrowl("Unable to send notification!", {type: 'danger'});
                }
                pending_notifications.ajax.reload( null, false );
                notifications_log.ajax.reload( null, false );
            });
        }
    }
});




function doAjaxCall(type, url, dataType, data, success, async){
    $.ajax({
        type: type,
        url: url,
        dataType: dataType,
        async: true,
        cache: true,
        data: data,
        success: success
    });
}

function randomPassword(length) {
    var chars = "abcdefghijklmnopqrstuvwxyz!@#$%^&*()-+<>ABCDEFGHIJKLMNOP1234567890";
    var pass = "";
    for (var x = 0; x < length; x++) {
        var i = Math.floor(Math.random() * chars.length);
        pass += chars.charAt(i);
    }
    return pass;
}

function generate() {
    adduser.password.value = randomPassword(adduser.pwlength.value);
}

function deleteComment(comment_id) {
    doAjaxCall('post', '/admin/deletecomment', 'json', {comment_id: comment_id}, function (response) {
        if (response.status == 'success') {
            $.bootstrapGrowl("Comment deleted!", {type: 'success'});
        } else {
            $.bootstrapGrowl("Unable to delete comment!", {type: 'danger'});
        }
    });
}