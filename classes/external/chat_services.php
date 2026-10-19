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
 * Namespaced adapter for legacy Chat web services.
 *
 * @package    mod_chat
 * @copyright  2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_chat\external;

defined("MOODLE_INTERNAL") || die();

require_once(__DIR__ . "/../external.php");

/**
 * Keeps existing mobile service implementations and signatures compatible.
 *
 * Moodle can resolve this class by namespace without rewriting the
 * public methods in the existing mod_chat_external class.
 */
class chat_services extends \mod_chat_external {
}
