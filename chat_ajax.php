<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');

$action = optional_param('action', '', PARAM_ALPHANUM);
$beep_id      = optional_param('beep', '', PARAM_RAW);
$chat_sid     = required_param('chat_sid', PARAM_ALPHANUM);
$chat_message = optional_param('chat_message', '', PARAM_RAW);
$chat_lasttime = optional_param('chat_lasttime', 0, PARAM_INT);
$chat_lastrow  = optional_param('chat_lastrow', 1, PARAM_INT);


if (!$chatuser = $DB->get_record('chat_users', array('sid'=>$chat_sid))) {
    chat_print_error('ERROR', get_string('notlogged','chat'));
}
if (!$chat = $DB->get_record('chat', array('id'=>$chatuser->chatid))) {
    chat_print_error('ERROR', get_string('invalidcoursemodule', 'error'));
}
if (!$course = $DB->get_record('course', array('id'=>$chat->course))) {
    chat_print_error('ERROR', get_string('invaliduserid', 'error'));
}
if (!$cm = get_coursemodule_from_instance('chat', $chat->id, $course->id)) {
    chat_print_error('ERROR', get_string('invalidcoursemodule', 'error'));
}
if (isguest()) {
    chat_print_error('ERROR', get_string('notlogged','chat'));
}

// setup $PAGE so that format_text will work properly
$PAGE->set_cm($cm, $course, $chat);

ob_start();
header('Expires: Sun, 28 Dec 1997 09:32:45 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Content-Type: text/html; charset=utf-8');
header('X-Powered-By: MOODLE-Chat-V2');

switch ($action) {
case 'init':
    if($CFG->chat_use_cache){
        $cache = new file_cache();
        $users = $cache->get('user');
        if(empty($users)) {
            $users = chat_get_users($chatuser->chatid, $chatuser->groupid, $cm->groupingid);
            $cache->set('user', $users);
        }
    } else {
        $users = chat_get_users($chatuser->chatid, $chatuser->groupid, $cm->groupingid);
    }
    $users = chat_format_userlist($users, $course);
    $response['users'] = $users;
    echo json_encode($response);
    break;
case 'chat':
    session_get_instance()->write_close();
    chat_delete_old_users();
    $chat_message = clean_text($chat_message, FORMAT_MOODLE);

    if (!empty($beep_id)) {
        $chat_message = 'beep '.$beep_id;
    }

    if (!empty($chat_message)) {
        $message = new object();
        $message->chatid    = $chatuser->chatid;
        $message->userid    = $chatuser->userid;
        $message->groupid   = $chatuser->groupid;
        $message->message   = $chat_message;
        $message->timestamp = time();

        $chatuser->lastmessageping = time() - 2;
        $DB->update_record('chat_users', $chatuser);

        if (!($DB->insert_record('chat_messages', $message) && $DB->insert_record('chat_messages_current', $message))) {
            chat_print_error('ERROR', get_string('cantlogin','chat'));
        } else {
            echo 200;
        }
        add_to_log($course->id, 'chat', 'talk', "view.php?id=$cm->id", $chat->id, $cm->id);

        ob_end_flush();
    }
    break;
case 'update':
    if ((time() - $chat_lasttime) > $CFG->chat_old_ping) {
        chat_delete_old_users();
    }

    if ($latest_message = chat_get_latest_message($chatuser->chatid, $chatuser->groupid)) {
        $chat_newlasttime = $latest_message->timestamp;
    } else {
        $chat_newlasttime = 0;
    }

    if ($chat_lasttime == 0) {
        $chat_lasttime = time() - $CFG->chat_old_ping;
    }

    $params = array('groupid'=>$chatuser->groupid, 'chatid'=>$chatuser->chatid, 'lasttime'=>$chat_lasttime);

    $groupselect = $chatuser->groupid ? " AND (groupid=".$chatuser->groupid." OR groupid=0) " : "";

    $messages = $DB->get_records_select('chat_messages_current',
        'chatid = :chatid AND timestamp > :lasttime '.$groupselect, $params,
        'timestamp ASC');

    if (!empty($messages)) {
        $num = count($messages);
    } else {
        $num = 0;
    }
    $chat_newrow = ($chat_lastrow + $num) % 2;
    $send_user_list = false;
    if ($messages && ($chat_lasttime != $chat_newlasttime)) {
        foreach ($messages as $n => &$message) {
            $tmp = new stdclass;
            // when somebody enter room, user list will be updated
            if($message->system == 1){
                $send_user_list = true;
                $tmp->type = 'system';
                $users = chat_format_userlist(chat_get_users($chatuser->chatid, $chatuser->groupid, $cm->groupingid), $course);
            }
            if ($html = chat_format_message($message, $chatuser->course, $USER, $chat_lastrow)) {
                if ($html->beep) {
                    $tmp->type = 'beep';
                } elseif (empty($tmp->type)) {
                    $tmp->type = 'user';
                }
                $tmp->mymessage = ($USER->id == $message->userid);
                $tmp->msg  = $html->html;
                $message = $tmp;
            } else {
                unset($message);
            }
        }
    }

    if(!empty($users) && $send_user_list){
        // return users when system message coming
        $response['users'] = $users;
    }

    $DB->set_field('chat_users', 'lastping', time(), array('id'=>$chatuser->id));

    $response['lasttime'] = $chat_newlasttime;
    $response['lastrow']  = $chat_newrow;
    if($messages){
        $response['msgs'] = $messages;
    }

    echo json_encode($response);
    header('Content-Length: ' . ob_get_length() );

    ob_end_flush();
    break;
default:
    break;
}
