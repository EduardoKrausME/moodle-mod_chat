<?php  // $Id$

    $nomoodlecookie = true;     // Session not needed!

    require('../../../config.php');
    require('../lib.php');

    $chat_sid = required_param('chat_sid', PARAM_ALPHANUM);

    if (!$chatuser = get_record('chat_users', 'sid', $chat_sid)) {
        error('Not logged in!');
    }

    //Get the course theme
    $COURSE = get_record('course','id',$chatuser->course);

    //Get the user theme
    $USER = get_record('user','id',$chatuser->userid);

    //Adjust the prefered theme (main, course, user)
    theme_setup();

    chat_force_language($chatuser->lang);

    ob_start();
    ?>
    <script type="text/javascript">
    //<![CDATA[
    var waitFlag = false;
    function empty_field_and_submit() {
        if(waitFlag) return false;
        waitFlag = true;
        var input_chat_message = document.getElementById('input_chat_message');
        getElementById('sendForm').chat_message.value = input_chat_message.value;
        input_chat_message.value = '';
        input_chat_message.className = 'wait';
        getElementById('sendForm').submit();
        enableForm();
        return false;
    }

    function enableForm() {
        var input_chat_message = document.getElementById('input_chat_message');
        waitFlag = false;
        input_chat_message.className = '';
        input_chat_message.focus();
    }

    //]]>
    </script>
    <?php

    $meta = ob_get_clean();
    print_header('', '', '', 'input_chat_message', $meta, false);

?>
    <form action="../empty.php" method="post" target="empty" id="inputForm"
          onsubmit="return empty_field_and_submit()">
        &gt;&gt;<input type="text" id="input_chat_message" name="chat_message" size="60" value="" />
        <?php helpbutton('chatting', get_string('helpchatting', 'chat'), 'chat', true, false); ?>
    </form>

    <form action="insert.php" method="post" target="empty" id="sendForm">
        <input type="hidden" name="chat_sid" value="<?php echo $chat_sid ?>" />
        <input type="hidden" name="chat_message" />
    </form>
</div>
</div>
</body>
</html>
