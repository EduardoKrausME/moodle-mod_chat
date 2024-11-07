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

/**
 * Chat daemon
 *
 * @package    mod_chat
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/chat/lib.php');
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);
$c = optional_param('c', 0, PARAM_INT);
$edit = optional_param('edit', -1, PARAM_BOOL);

if ($id) {
    if (!$cm = get_coursemodule_from_id('chat', $id)) {
        throw new \moodle_exception('invalidcoursemodule');
    }

    if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
        throw new \moodle_exception('coursemisconf');
    }

    chat_update_chat_times($cm->instance);

    if (!$chat = $DB->get_record('chat', ['id' => $cm->instance])) {
        throw new \moodle_exception('invalidid', 'chat');
    }

} else {
    chat_update_chat_times($c);

    if (!$chat = $DB->get_record('chat', ['id' => $c])) {
        throw new \moodle_exception('coursemisconf');
    }
    if (!$course = $DB->get_record('course', ['id' => $chat->course])) {
        throw new \moodle_exception('coursemisconf');
    }
    if (!$cm = get_coursemodule_from_instance('chat', $chat->id, $course->id)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
}

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
$PAGE->set_context($context);

// Initialize $PAGE.
$courseshortname = format_string($course->shortname, true, ['context' => context_course::instance($course->id)]);
$title = $courseshortname . ': ' . format_string($chat->name);
$PAGE->set_url('/mod/chat/view.php', ['id' => $cm->id]);
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->add_body_class('limitedwidth');

// Show some info for guests.
if (isguestuser()) {
    echo $OUTPUT->header();
    echo $OUTPUT->confirm('<p>' . get_string('noguests', 'chat') . '</p>' . get_string('liketologin'),
        get_login_url(), $CFG->wwwroot . '/course/view.php?id=' . $course->id);

    echo $OUTPUT->footer();
    exit;
}

// Completion and trigger events.
chat_view($chat, $course, $cm, $context);

$strenterchat = get_string('enterchat', 'chat');
$stridle = get_string('idle', 'chat');
$strcurrentusers = get_string('currentusers', 'chat');

// Check to see if groups are being used here.
$groupmode = groups_get_activity_groupmode($cm);
$currentgroup = groups_get_activity_group($cm, true);

// URL parameters.
$params = [];
if ($currentgroup) {
    $groupselect = " AND groupid = '$currentgroup'";
    $groupparam = "_group{$currentgroup}";
    $params['groupid'] = $currentgroup;
} else {
    $groupselect = "";
    $groupparam = "";
}

// Print the page header.
echo $OUTPUT->header();

if (has_capability('mod/chat:chat', $context)) {

    $now = time();
    $chattime = $chat->chattime ?? 0;
    $span = $chattime - $now;
    if (!empty($chat->schedule) && $span > 0) {
        $attributes = ['class' => 'border bg-light rounded p-2'];
        echo html_writer::tag('p', get_string('sessionstartsin', 'chat', format_time($span)), $attributes);
    }

    if ($groupmode = groups_get_activity_groupmode($cm)) {   // Groups are being used.
        if ($groupid = groups_get_activity_group($cm)) {
            if (!$group = groups_get_group($groupid)) {
                throw new \moodle_exception('invalidgroupid');
            }
        }
    } else {
        $groupid = 0;
    }

    if (!$chatsid = chat_login_user($chat->id, 'ajax', $groupid, $course)) {
        throw new \moodle_exception('cantlogin');
    }

    echo $OUTPUT->render_from_template("mod_chat/chat", [
        "myprofile" => [
            "id" => $USER->id,
            "fullname" => fullname($USER),
            "user_picture" => $OUTPUT->user_picture($USER),
            "url" => "{$CFG->wwwroot}/user/view.php?id={$USER->id}&amp;course={$course->id}",
            "profile" => profile($course->id, $USER),
        ],
        "cfg" => [
            "timer" => 2000,
            "chat_lasttime" => get_config("chat", "lasttime"),
            "chat_lastrow" => get_config("chat", "lastrow"),
            "chatsid" => $chatsid,
        ],
    ]);

} else {
    groups_print_activity_menu($cm, "{$CFG->wwwroot}/mod/chat/view.php?id={$cm->id}");
    echo $OUTPUT->box_start('generalbox', 'notallowenter');
    echo '<p>' . get_string('notallowenter', 'chat') . '</p>';
    echo $OUTPUT->box_end();
}

chat_delete_old_users();

echo $OUTPUT->footer();
