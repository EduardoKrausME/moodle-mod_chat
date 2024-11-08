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
 * Chat external API
 *
 * @package    mod_chat
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/chat/lib.php');

use core_course\external\helper_for_get_mods_by_courses;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use core_external\util;
use mod_chat\external\chat_message_exporter;

/**
 * Chat external functions
 *
 * @package    mod_chat
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_chat_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function login_user_parameters() {
        return new external_function_parameters(
            [
                'chatid' => new external_value(PARAM_INT, 'chat instance id'),
                'groupid' => new external_value(PARAM_INT, 'group id, 0 means that the function will determine the user group',
                    VALUE_DEFAULT, 0),
            ],
            );
    }

    /**
     * Log the current user into a chat room in the given chat.
     *
     * @param int $chatid  the chat instance id
     * @param int $groupid the user group id
     *
     * @return array of warnings and the chat unique session id
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function login_user($chatid, $groupid = 0) {
        global $DB;

        $params = self::validate_parameters(self::login_user_parameters(),
            [
                'chatid' => $chatid,
                'groupid' => $groupid,
            ],
            );
        $warnings = [];

        // Request and permission validation.
        $chat = $DB->get_record('chat', ['id' => $params['chatid']], '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/chat:chat', $context);

        if (!empty($params['groupid'])) {
            $groupid = $params['groupid'];
            // Determine is the group is visible to user.
            if (!groups_group_visible($groupid, $course, $cm)) {
                throw new moodle_exception('notingroup');
            }
        } else {
            // Check to see if groups are being used here.
            if ($groupmode = groups_get_activity_groupmode($cm)) {
                $groupid = groups_get_activity_group($cm);
                // Determine is the group is visible to user (this is particullary for the group 0).
                if (!groups_group_visible($groupid, $course, $cm)) {
                    throw new moodle_exception('notingroup');
                }
            } else {
                $groupid = 0;
            }
        }

        // Get the unique chat session id.
        // Since we are going to use the chat via Web Service requests we set the ajax version (since it's the most similar).
        if (!$chatsid = chat_login_user($chat->id, 'ajax', $groupid, $course)) {
            throw new moodle_exception('cantlogin', 'chat');
        }

        $result = [];
        $result['chatsid'] = $chatsid;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 3.0
     */
    public static function login_user_returns() {
        return new external_single_structure(
            [
                'chatsid' => new external_value(PARAM_ALPHANUM, 'unique chat session id'),
                'warnings' => new external_warnings(),
            ],
            );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_chat_users_parameters() {
        return new external_function_parameters(
            [
                'chatsid' => new external_value(PARAM_ALPHANUM, 'chat session id (obtained via mod_chat_login_user)'),
            ],
            );
    }

    /**
     * Get the list of users in the given chat session.
     *
     * @param int $chatsid the chat session id
     *
     * @return array of warnings and the user lists
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function get_chat_users($chatsid) {
        global $DB, $PAGE;

        $params = self::validate_parameters(self::get_chat_users_parameters(),
            [
                'chatsid' => $chatsid,
            ],
            );
        $warnings = [];

        // Request and permission validation.
        if (!$chatuser = $DB->get_record('chat_users', ['sid' => $params['chatsid']])) {
            throw new moodle_exception('notlogged', 'chat');
        }
        $chat = $DB->get_record('chat', ['id' => $chatuser->chatid], '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/chat:chat', $context);

        // First, delete old users from the chats.
        chat_delete_old_users();

        $users = chat_get_users($chatuser->chatid, $chatuser->groupid, $cm->groupingid);
        $returnedusers = [];

        foreach ($users as $user) {

            $userpicture = new user_picture($user);
            $userpicture->size = 1; // Size f1.
            $profileimageurl = $userpicture->get_url($PAGE)->out(false);

            $returnedusers[] = [
                'id' => $user->id,
                'fullname' => fullname($user),
                'profileimageurl' => $profileimageurl,
            ];
        }

        $result = [];
        $result['users'] = $returnedusers;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 3.0
     */
    public static function get_chat_users_returns() {
        return new external_single_structure(
            [
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'user id'),
                            'fullname' => new external_value(PARAM_NOTAGS, 'user full name'),
                            'profileimageurl' => new external_value(PARAM_URL, 'user picture URL'),
                        ],
                        ),
                    'list of users'
                ),
                'warnings' => new external_warnings(),
            ],
            );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function send_chat_message_parameters() {
        return new external_function_parameters(
            [
                'chatsid' => new external_value(PARAM_ALPHANUM, 'chat session id (obtained via mod_chat_login_user)'),
                'messagetext' => new external_value(PARAM_RAW, 'the message text'),
                'beepid' => new external_value(PARAM_RAW, 'the beep id', VALUE_DEFAULT, ''),
            ],
            );
    }

    /**
     * Send a message on the given chat session.
     *
     * @param int $chatsid        the chat session id
     * @param string $messagetext the message text
     * @param string $beepid      the beep message id
     *
     * @return array of warnings and the new message id (0 if the message was empty)
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function send_chat_message($chatsid, $messagetext, $beepid = '') {
        global $DB;

        $params = self::validate_parameters(self::send_chat_message_parameters(),
            [
                'chatsid' => $chatsid,
                'messagetext' => $messagetext,
                'beepid' => $beepid,
            ]);
        $warnings = [];

        // Request and permission validation.
        if (!$chatuser = $DB->get_record('chat_users', ['sid' => $params['chatsid']])) {
            throw new moodle_exception('notlogged', 'chat');
        }
        $chat = $DB->get_record('chat', ['id' => $chatuser->chatid], '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/chat:chat', $context);

        $chatmessage = clean_text($params['messagetext'], FORMAT_MOODLE);

        if (!empty($params['beepid'])) {
            $chatmessage = 'beep ' . $params['beepid'];
        }

        if (!empty($chatmessage)) {
            // Send the message.
            $messageid = chat_send_chatmessage($chatuser, $chatmessage, 0, $cm);
            // Update ping time.
            $chatuser->lastmessageping = time() - 2;
            $DB->update_record('chat_users', $chatuser);
        } else {
            $messageid = 0;
        }

        $result = [];
        $result['messageid'] = $messageid;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 3.0
     */
    public static function send_chat_message_returns() {
        return new external_single_structure(
            [
                'messageid' => new external_value(PARAM_INT, 'message sent id'),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_chat_latest_messages_parameters() {
        return new external_function_parameters(
            [
                'chatsid' => new external_value(PARAM_ALPHANUM, 'chat session id (obtained via mod_chat_login_user)'),
                'chatlasttime' => new external_value(PARAM_INT, 'last time messages were retrieved (epoch time)', VALUE_DEFAULT, 0),
            ]
        );
    }

    /**
     * Get the latest messages from the given chat session.
     *
     * @param int $chatsid      the chat session id
     * @param int $chatlasttime last time messages were retrieved (epoch time)
     *
     * @return array of warnings and the new message id (0 if the message was empty)
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function get_chat_latest_messages($chatsid, $chatlasttime = 0) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::get_chat_latest_messages_parameters(),
            [
                'chatsid' => $chatsid,
                'chatlasttime' => $chatlasttime,
            ]);
        $warnings = [];

        // Request and permission validation.
        if (!$chatuser = $DB->get_record('chat_users', ['sid' => $params['chatsid']])) {
            throw new moodle_exception('notlogged', 'chat');
        }
        $chat = $DB->get_record('chat', ['id' => $chatuser->chatid], '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/chat:chat', $context);

        $chatlasttime = $params['chatlasttime'];
        if ((time() - $chatlasttime) > get_config("chat", "old_ping")) {
            chat_delete_old_users();
        }

        // Set default chat last time (to not retrieve all the conversations).
        if ($chatlasttime == 0) {
            $chatlasttime = time() - get_config("chat", "old_ping");
        }

        if ($latestmessage = chat_get_latest_message($chatuser->chatid, $chatuser->groupid)) {
            $chatnewlasttime = $latestmessage->timestamp;
        } else {
            $chatnewlasttime = 0;
        }

        $messages = chat_get_latest_messages($chatuser, $chatlasttime);
        $returnedmessages = [];

        foreach ($messages as $message) {
            // FORMAT_MOODLE is mandatory in the chat plugin.
            [$messageformatted] = \core_external\util::format_text(
                $message->message,
                FORMAT_MOODLE,
                $context->id,
                'mod_chat',
                '',
                0
            );

            $returnedmessages[] = [
                'id' => $message->id,
                'userid' => $message->userid,
                'system' => (bool)$message->issystem,
                'message' => $messageformatted,
                'timestamp' => $message->timestamp,
            ];
        }

        // Update our status since we are active in the chat.
        $DB->set_field('chat_users', 'lastping', time(), ['id' => $chatuser->id]);

        $result = [];
        $result['messages'] = $returnedmessages;
        $result['chatnewlasttime'] = $chatnewlasttime;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 3.0
     */
    public static function get_chat_latest_messages_returns() {
        return new external_single_structure(
            [
                'messages' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'message id'),
                            'userid' => new external_value(PARAM_INT, 'user id'),
                            'system' => new external_value(PARAM_BOOL, 'true if is a system message (like user joined)'),
                            'message' => new external_value(PARAM_RAW, 'message text'),
                            'timestamp' => new external_value(PARAM_INT, 'timestamp for the message'),
                        ],
                        ),
                    'list of users'
                ),
                'chatnewlasttime' => new external_value(PARAM_INT, 'new last time'),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_chat_parameters() {
        return new external_function_parameters(
            [
                'chatid' => new external_value(PARAM_INT, 'chat instance id'),
            ]
        );
    }

    /**
     * Trigger the course module viewed event and update the module completion status.
     *
     * @param int $chatid the chat instance id
     *
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_chat($chatid) {
        global $DB;

        $params = self::validate_parameters(self::view_chat_parameters(),
            [
                'chatid' => $chatid,
            ]);
        $warnings = [];

        // Request and permission validation.
        $chat = $DB->get_record('chat', ['id' => $params['chatid']], '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/chat:chat', $context);

        // Call the url/lib API.
        chat_view($chat, $course, $cm, $context);

        $result = [];
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 3.0
     */
    public static function view_chat_returns() {
        return new external_single_structure(
            [
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            ]
        );
    }


    /**
     * Describes the parameters for get_chats_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_chats_by_courses_parameters() {
        return new external_function_parameters (
            [
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'), 'Array of course ids', VALUE_DEFAULT, []
                ),
            ]
        );
    }

    /**
     * Returns a list of chats in a provided list of courses,
     * if no list is provided all chats that the user can view will be returned.
     *
     * @param array $courseids the course ids
     *
     * @return array of chats details
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @since Moodle 3.0
     */
    public static function get_chats_by_courses($courseids = []) {
        global $CFG;

        $returnedchats = [];
        $warnings = [];

        $params = self::validate_parameters(self::get_chats_by_courses_parameters(), ['courseids' => $courseids]);

        $courses = [];
        if (empty($params['courseids'])) {
            $courses = enrol_get_my_courses();
            $params['courseids'] = array_keys($courses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = util::validate_courses($params['courseids'], $courses);

            // Get the chats in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $chats = get_all_instances_in_courses("chat", $courses);
            foreach ($chats as $chat) {
                $chatcontext = context_module::instance($chat->coursemodule);

                $chatdetails = helper_for_get_mods_by_courses::standard_coursemodule_element_values($chat, 'mod_chat');

                if (has_capability('mod/chat:chat', $chatcontext)) {
                    $chatdetails['chatmethod'] = get_config("chat", "method");
                    $chatdetails['keepdays'] = $chat->keepdays;
                    $chatdetails['studentlogs'] = $chat->studentlogs;
                    $chatdetails['chattime'] = $chat->chattime;
                    $chatdetails['schedule'] = $chat->schedule;
                }

                if (has_capability('moodle/course:manageactivities', $chatcontext)) {
                    $chatdetails['timemodified'] = $chat->timemodified;
                }
                $returnedchats[] = $chatdetails;
            }
        }
        $result = [];
        $result['chats'] = $returnedchats;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Describes the get_chats_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.0
     */
    public static function get_chats_by_courses_returns() {
        return new external_single_structure(
            [
                'chats' => new external_multiple_structure(
                    new external_single_structure(array_merge(
                        helper_for_get_mods_by_courses::standard_coursemodule_elements_returns(),
                        [
                            'chatmethod' => new external_value(PARAM_PLUGIN, 'chat method (sockets, ajax, header_js)',
                                VALUE_OPTIONAL),
                            'keepdays' => new external_value(PARAM_INT, 'keep days', VALUE_OPTIONAL),
                            'studentlogs' => new external_value(PARAM_INT, 'student logs visible to everyone', VALUE_OPTIONAL),
                            'chattime' => new external_value(PARAM_INT, 'chat time', VALUE_OPTIONAL),
                            'schedule' => new external_value(PARAM_INT, 'schedule type', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'time of last modification', VALUE_OPTIONAL),
                        ]
                    ), 'Chats')
                ),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.4
     */
    public static function get_sessions_parameters() {
        return new external_function_parameters(
            [
                'chatid' => new external_value(PARAM_INT, 'Chat instance id.'),
                'groupid' => new external_value(PARAM_INT, 'Get messages from users in this group.
                                                0 means that the function will determine the user group', VALUE_DEFAULT, 0),
                'showall' => new external_value(PARAM_BOOL, 'Whether to show completed sessions or not.', VALUE_DEFAULT, false),
            ]
        );
    }

    /**
     * Retrieves chat sessions for a given chat.
     *
     * @param int $chatid   the chat instance id
     * @param int $groupid  filter messages by this group. 0 to determine the group.
     * @param bool $showall whether to include incomplete sessions or not
     *
     * @return array of warnings and the sessions
     * @since Moodle 3.4
     * @throws moodle_exception
     */
    public static function get_sessions($chatid, $groupid = 0, $showall = false) {
        global $DB;

        $params = self::validate_parameters(self::get_sessions_parameters(),
            [
                'chatid' => $chatid,
                'groupid' => $groupid,
                'showall' => $showall,
            ]);
        $sessions = $warnings = [];

        // Request and permission validation.
        $chat = $DB->get_record('chat', ['id' => $params['chatid']], '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        if (empty($chat->studentlogs) && !has_capability('mod/chat:readlog', $context)) {
            throw new moodle_exception('nopermissiontoseethechatlog', 'chat');
        }

        if (!empty($params['groupid'])) {
            $groupid = $params['groupid'];
            // Determine is the group is visible to user.
            if (!groups_group_visible($groupid, $course, $cm)) {
                throw new moodle_exception('notingroup');
            }
        } else {
            // Check to see if groups are being used here.
            if ($groupmode = groups_get_activity_groupmode($cm)) {
                $groupid = groups_get_activity_group($cm);
                // Determine is the group is visible to user (this is particullary for the group 0).
                if (!groups_group_visible($groupid, $course, $cm)) {
                    throw new moodle_exception('notingroup');
                }
            } else {
                $groupid = 0;
            }
        }

        $messages = chat_get_session_messages($chat->id, $groupid, 0, 0, 'timestamp DESC');
        if ($messages) {
            $chatsessions = chat_get_sessions($messages, $params['showall']);
            // Format sessions for external.
            foreach ($chatsessions as $session) {
                $sessionusers = [];
                foreach ($session->sessionusers as $sessionuser => $usermessagecount) {
                    $sessionusers[] = [
                        'userid' => $sessionuser,
                        'messagecount' => $usermessagecount,
                    ];
                }
                $session->sessionusers = $sessionusers;
                $sessions[] = $session;
            }
        }

        $result = [];
        $result['sessions'] = $sessions;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 3.4
     */
    public static function get_sessions_returns() {
        return new external_single_structure(
            [
                'sessions' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'sessionstart' => new external_value(PARAM_INT, 'Session start time.'),
                            'sessionend' => new external_value(PARAM_INT, 'Session end time.'),
                            'sessionusers' => new external_multiple_structure(
                                new external_single_structure(
                                    [
                                        'userid' => new external_value(PARAM_INT, 'User id.'),
                                        'messagecount' => new external_value(PARAM_INT, 'Number of messages in the session.'),
                                    ],
                                    ), 'Session users.'
                            ),
                            'iscomplete' => new external_value(PARAM_BOOL, 'Whether the session is completed or not.'),
                        ],
                        ),
                    'list of users'
                ),
                'warnings' => new external_warnings(),
            ],
            );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.4
     */
    public static function get_session_messages_parameters() {
        return new external_function_parameters(
            [
                'chatid' => new external_value(PARAM_INT, 'Chat instance id.'),
                'sessionstart' => new external_value(PARAM_INT, 'The session start time (timestamp).'),
                'sessionend' => new external_value(PARAM_INT, 'The session end time (timestamp).'),
                'groupid' => new external_value(PARAM_INT, 'Get messages from users in this group.
                                                0 means that the function will determine the user group', VALUE_DEFAULT, 0),
            ],
            );
    }

    /**
     * Retrieves messages of the given chat session.
     *
     * @param int $chatid       the chat instance id
     * @param int $sessionstart the session start time (timestamp)
     * @param int $sessionend   the session end time (timestamp)
     * @param int $groupid      filter messages by this group. 0 to determine the group.
     *
     * @return array of warnings and the messages
     * @since Moodle 3.4
     * @throws moodle_exception
     */
    public static function get_session_messages($chatid, $sessionstart, $sessionend, $groupid = 0) {
        global $DB, $PAGE;

        $params = self::validate_parameters(self::get_session_messages_parameters(),
            [
                'chatid' => $chatid,
                'sessionstart' => $sessionstart,
                'sessionend' => $sessionend,
                'groupid' => $groupid,
            ]);
        $warnings = [];

        // Request and permission validation.
        $chat = $DB->get_record('chat', ['id' => $params['chatid']], '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($chat, 'chat');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        if (empty($chat->studentlogs) && !has_capability('mod/chat:readlog', $context)) {
            throw new moodle_exception('nopermissiontoseethechatlog', 'chat');
        }

        if (!empty($params['groupid'])) {
            $groupid = $params['groupid'];
            // Determine is the group is visible to user.
            if (!groups_group_visible($groupid, $course, $cm)) {
                throw new moodle_exception('notingroup');
            }
        } else {
            // Check to see if groups are being used here.
            if ($groupmode = groups_get_activity_groupmode($cm)) {
                $groupid = groups_get_activity_group($cm);
                // Determine is the group is visible to user (this is particullary for the group 0).
                if (!groups_group_visible($groupid, $course, $cm)) {
                    throw new moodle_exception('notingroup');
                }
            } else {
                $groupid = 0;
            }
        }

        $messages = chat_get_session_messages($chat->id, $groupid, $params['sessionstart'], $params['sessionend'],
            'timestamp ASC');
        if ($messages) {
            foreach ($messages as $message) {
                $exporter = new chat_message_exporter($message, ['context' => $context]);
                $returneditems[] = $exporter->export($PAGE->get_renderer('core'));
            }
        }

        $result = ['messages' => $messages, 'warnings' => $warnings];
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     * @since Moodle 3.4
     */
    public static function get_session_messages_returns() {
        return new external_single_structure(
            [
                'messages' => new external_multiple_structure(
                    chat_message_exporter::get_read_structure()
                ),
                'warnings' => new external_warnings(),
            ],
            );
    }
}
