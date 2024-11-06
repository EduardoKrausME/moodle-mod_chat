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
 * Upgrade code for the chat activity
 *
 * @package   mod_chat
 * @copyright 2006 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function xmldb_chat_upgrade
 *
 * @param $oldversion
 *
 * @return bool
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_chat_upgrade($oldversion) {
    global $CFG;

    if ($oldversion < 2024110600) {

        if (isset($CFG->chat_method)) {
            set_config("method", $CFG->chat_method, "chat");
        }
        if (isset($CFG->chat_refresh_userlist)) {
            set_config("refresh_userlist", $CFG->chat_refresh_userlist, "chat");
        }
        if (isset($CFG->chat_old_ping)) {
            set_config("old_ping", $CFG->chat_old_ping, "chat");
        }
        if (isset($CFG->chat_refresh_room)) {
            set_config("refresh_room", $CFG->chat_refresh_room, "chat");
        }
        if (isset($CFG->chat_normal_updatemode)) {
            set_config("normal_updatemode", $CFG->chat_normal_updatemode, "chat");
        }
        if (isset($CFG->chat_serverhost)) {
            set_config("serverhost", $CFG->chat_serverhost, "chat");
        }
        if (isset($CFG->chat_serverip)) {
            set_config("serverip", $CFG->chat_serverip, "chat");
        }
        if (isset($CFG->chat_serverport)) {
            set_config("serverport", $CFG->chat_serverport, "chat");
        }
        if (isset($CFG->chat_servermax)) {
            set_config("servermax", $CFG->chat_servermax, "chat");
        }
        upgrade_plugin_savepoint(true, 2024110600, 'mod', 'chat');
    }
    return true;
}
