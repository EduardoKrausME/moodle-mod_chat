<?php  // $Id$

    $nomoodlecookie = true;     // Session not needed!

    require('../../../config.php');
    require('../lib.php');

    $chat_sid = required_param('chat_sid', PARAM_ALPHANUM);
    $groupid = optional_param('groupid', 0, PARAM_INT);

    if (!$chatuser = get_record('chat_users', 'sid', $chat_sid)) {
        error('Not logged in!');
    }

    chat_force_language($chatuser->lang);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?php echo get_string('thischarset'); ?>" />
<title>Message Input</title>

<?php
    $focus = '';
    include("$CFG->javascript");
?>

<script type="text/javascript">
<!--

scroll_active = true;
function empty_field_and_submit() {
    var cf   = document.getElementById('sendform');
    var inpf = document.getElementById('inputform');
    cf.chat_msgidnr.value = parseInt(cf.chat_msgidnr.value) + 1;
    cf.chat_message.value = inpf.chat_message.value;
    cf.submit();
    inpf.chat_message.value='';
    inpf.chat_message.focus();
    return false;
}
function prepareusers() {
    var frm = window.parent.frames;
    for(i = 0; i < frm.length; ++i) {
        if(frm[i].name == "users") {
            window.userFrame = frm[i];
            window.userHREF  = frm[i].location.href;
            window.setTimeout("reloadusers();", <?php echo $CFG->chat_refresh_userlist; ?> * 1000);
        }
    }
}
function reloadusers() {
    if(window.userFrame) {
        window.userFrame.location.href = window.userFrame.location.href;
        window.setTimeout("reloadusers();", <?php echo $CFG->chat_refresh_userlist; ?> * 1000);
    }
}
// -->
</script>
</head>

<body bgcolor="<?php echo $THEME->body ?>" onload="document.getElementById('inputform').chat_message.focus(); prepareusers();">

<form action="../empty.php" method="get" target="empty" id="inputform" onsubmit="return empty_field_and_submit();">
&gt;&gt; <input type="text" name="chat_message" size="60" value="" />
<?php helpbutton("chatting", get_string("helpchatting", "chat"), "chat", true, false); ?>
</form>

<form action="<?php echo "http://$CFG->chat_serverhost:$CFG->chat_serverport/"; ?>" method="get" target="empty" id="sendform">
    <input type="hidden" name="win" value="message" />
    <input type="hidden" name="chat_message" value="" />
    <input type="hidden" name="chat_msgidnr" value="0" />
    <input type="hidden" name="chat_sid" value="<?php echo $chat_sid ?>" />
    <input type="hidden" name="groupid" value="<?php echo $groupid ?>" />
</form>
</body>

</html>
