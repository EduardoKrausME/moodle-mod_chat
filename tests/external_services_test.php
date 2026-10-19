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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Chat mobile external service regression tests.
 *
 * @package    mod_chat
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_chat;

defined("MOODLE_INTERNAL") || die();

/**
 * Tests backwards-compatible external service discovery.
 *
 * @covers \mod_chat\external\chat_services
 */
final class external_services_test extends \advanced_testcase {
    /**
     * Every legacy mobile operation must expose its original contract.
     */
    public function test_mobile_service_methods_expose_valid_contracts(): void {
        $methods = [
            "login_user",
            "get_chat_users",
            "send_chat_message",
            "get_chat_latest_messages",
            "view_chat",
            "get_chats_by_courses",
            "get_sessions",
            "get_session_messages",
        ];
        $classname = \mod_chat\external\chat_services::class;
        foreach ($methods as $method) {
            $this->assertTrue(is_callable([$classname, $method]), $method);
            $parameters = $method . "_parameters";
            $returns = $method . "_returns";
            $this->assertInstanceOf(
                \core_external\external_function_parameters::class,
                $classname::$parameters()
            );
            $this->assertInstanceOf(
                \core_external\external_description::class,
                $classname::$returns()
            );
        }
    }

    /**
     * Registered Moodle web service names and methods remain unchanged.
     */
    public function test_service_definitions_keep_mobile_api_names(): void {
        global $CFG;
        require($CFG->dirroot . "/mod/chat/db/services.php");
        $methods = [
            "mod_chat_login_user" => "login_user",
            "mod_chat_get_chat_users" => "get_chat_users",
            "mod_chat_send_chat_message" => "send_chat_message",
            "mod_chat_get_chat_latest_messages" => "get_chat_latest_messages",
            "mod_chat_view_chat" => "view_chat",
            "mod_chat_get_chats_by_courses" => "get_chats_by_courses",
            "mod_chat_get_sessions" => "get_sessions",
            "mod_chat_get_session_messages" => "get_session_messages",
        ];
        foreach ($methods as $name => $method) {
            $this->assertArrayHasKey($name, $functions);
            $this->assertSame("mod_chat\\external\\chat_services", $functions[$name]["classname"]);
            $this->assertSame($method, $functions[$name]["methodname"]);
        }
    }
}
