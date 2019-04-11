<script type="text/javascript">
$(document).on('ready', function() {

    /** NOTIFICATIONS **/
    // setInterval(function(){
    //     getNotifications();
    // }, 60000);

    /** CHANGE ROLE AND BRANCH **/
    $('select[name="gantiRole"').on('change', function() {
        $.ajax({
            url: '{{ URL('get-json-role-branch') }}/' + $(this).val()
        }).done(function(branchs) {
            var currentBranch = $('select[name="gantiCabang"').val();
            $('select[name="gantiCabang"').html('');
            branchs.forEach(function(branch) {
                var selected = currentBranch == branch.branch_id ? 'selected' : '';
                $('select[name="gantiCabang"]').append(
                    '<option value="' + branch.branch_id + '" ' + selected + '>' + branch.branch_name + '</option>'
                );
            });
        });
    });
});

var getNotifications = function() {
    $.ajax({
        url: '{{ URL('get-notifications') }}'
    }).done(function(data) {
        var count = data.count;
        var $count = $('#notifications').find('span.count');
        var $ul = $('#notifications').find('ul');

        $count.html(count > 0 ? count : '');
        $ul.html('');
        $ul.append('<li class="dropdown-header notif-header"><i class="icon-mail-2"></i> New Notifications</li>');
        data.notifications.forEach(function(notification) {
            $ul.append(
                '<li class="unread">' +
                '<a href="{{ URL('read-notification') }}/' + notification.notification_id + '" class="clearfix notification" >' +
                '<strong>' + notification.category + '</strong>' +
                '<i class="pull-right text-right msg-time">' +
                notification.created_at + '<br/>' +
                notification.role + '<br/>' +
                notification.branch +
                '</i>' +
                '<br />' +
                '<p>' + notification.message + '</p>' +
                '</a>' +
                '</li>'
            );
        });
        $ul.append('<li class="dropdown-footer">\
                <div class="">\
                    <a href="{{ URL('notification') }}" class="btn btn-sm btn-block btn-primary">\
                        <i class="icon-mail-2"></i> Show All\
                    </a>\
                </div>\
            </li>'
        );
    });
};
</script>
<script type="text/javascript">
    setTimeout(function(){
       window.location = '{{ URL('/') }}/logout';
    }, 120 * 60 * 1000);
</script>

@if(!empty(\Session::get('showWelcomeModal')))
<script type="text/javascript">
    $(document).on('ready', function() {
        $('#modal-welcome').modal('show');
    })
</script>
<?php \Session::forget('showWelcomeModal') ?>
@endif