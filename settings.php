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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(
        new admin_setting_heading('chat/method_heading', get_string('generalconfig', 'chat'),
        get_string('explaingeneralconfig', 'chat')));

    $options = [];
    $options['ajax'] = get_string('methodajax', 'chat');
    $options['header_js'] = get_string('methodnormal', 'chat');
    $options['sockets'] = get_string('methoddaemon', 'chat');
    $settings->add(
        new admin_setting_configselect('chat/method', get_string('method', 'chat'),
            get_string('configmethod', 'chat'), 'ajax', $options));

    $settings->add(
        new admin_setting_configtext('chat/refresh_userlist', get_string('refreshuserlist', 'chat'),
            get_string('configrefreshuserlist', 'chat'), 10, PARAM_INT));

    $settings->add(
        new admin_setting_configtext('chat/old_ping', get_string('oldping', 'chat'),
            get_string('configoldping', 'chat'), 35, PARAM_INT));

    $settings->add(
        new admin_setting_heading('chat/normal_heading', get_string('methodnormal', 'chat'),
            get_string('explainmethodnormal', 'chat')));

    $settings->add(new admin_setting_configtext('chat/refresh_room', get_string('refreshroom', 'chat'),
        get_string('configrefreshroom', 'chat'), 5, PARAM_INT));

    $options = [];
    $options['jsupdate'] = get_string('normalkeepalive', 'chat');
    $options['jsupdated'] = get_string('normalstream', 'chat');
    $settings->add(
        new admin_setting_configselect('chat/normal_updatemode', get_string('updatemethod', 'chat'),
            get_string('confignormalupdatemode', 'chat'), 'jsupdate', $options));

    $settings->add(
        new admin_setting_heading('chat/daemon_heading', get_string('methoddaemon', 'chat'),
            get_string('explainmethoddaemon', 'chat')));

    $settings->add(
        new admin_setting_configtext('chat/serverhost', get_string('serverhost', 'chat'),
            get_string('configserverhost', 'chat'), get_host_from_url($CFG->wwwroot)));

    $settings->add(
        new admin_setting_configtext('chat/serverip', get_string('serverip', 'chat'),
            get_string('configserverip', 'chat'), '127.0.0.1'));

    $settings->add(
        new admin_setting_configtext('chat/serverport', get_string('serverport', 'chat'),
            get_string('configserverport', 'chat'), 9111, PARAM_INT));

    $settings->add(
        new admin_setting_configtext('chat/servermax', get_string('servermax', 'chat'),
            get_string('configservermax', 'chat'), 100, PARAM_INT));
}
