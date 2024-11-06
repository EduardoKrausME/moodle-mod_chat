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
 * Library of functions for chat outside of the core api
 *
 * @package    mod_chat
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/chat/lib.php');
require_once($CFG->libdir . '/portfolio/caller.php');

/**
 * chat_portfolio_caller
 *
 * @package   mod_chat
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chat_portfolio_caller extends portfolio_module_caller_base {
    /** @var object */
    private $chat;
    /** @var int Timestamp */
    protected $start;
    /** @var int Timestamp */
    protected $end;
    /** @var array Chat messages */
    protected $messages = [];
    /** @var bool True if participated, otherwise false. */
    protected $participated;

    /**
     * Function expected_callbackargs
     *
     * @return array
     */
    public static function expected_callbackargs() {
        return ['id' => true, 'start' => false, 'end' => false];
    }

    /**
     * Function load_data
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws portfolio_caller_exception
     */
    public function load_data() {
        global $DB;

        if (!$this->cm = get_coursemodule_from_id('chat', $this->id)) {
            throw new portfolio_caller_exception('invalidid', 'chat');
        }
        $this->chat = $DB->get_record('chat', ['id' => $this->cm->instance]);
        $select = 'chatid = ?';
        $params = [$this->chat->id];
        if ($this->start && $this->end) {
            $select .= ' AND timestamp >= ? AND timestamp <= ?';
            $params[] = $this->start;
            $params[] = $this->end;
        }
        $this->messages = $DB->get_records_select(
            'chat_messages',
            $select,
            $params,
            'timestamp ASC'
        );
        $select .= ' AND userid = ?';
        $params[] = $this->user->id;
        $this->participated = $DB->record_exists_select(
            'chat_messages',
            $select,
            $params
        );
    }

    /**
     * Function base_supported_formats
     *
     * @return array|void
     */
    public static function base_supported_formats() {
        return [PORTFOLIO_FORMAT_PLAINHTML];
    }

    /**
     * Function expected_time
     *
     * @return string
     */
    public function expected_time() {
        return portfolio_expected_time_db(count($this->messages));
    }

    /**
     * Function get_sha1
     *
     * @return string
     */
    public function get_sha1() {
        $str = '';
        ksort($this->messages);
        foreach ($this->messages as $m) {
            $str .= implode('', (array)$m);
        }
        return sha1($str);
    }

    /**
     * Function check_permissions
     *
     * @return bool
     * @throws coding_exception
     */
    public function check_permissions() {
        $context = context_module::instance($this->cm->id);
        return has_capability('mod/chat:exportsession', $context)
            || ($this->participated
                && has_capability('mod/chat:exportparticipatedsession', $context));
    }

    /**
     * Function prepare_package
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function prepare_package() {
        $content = '';
        $lasttime = 0;
        foreach ($this->messages as $message) {  // We are walking FORWARDS through messages.
            $m = clone $message; // This causes the sha1 to change as chat_format_message changes what it's passed.
            $formatmessage = chat_format_message($m, $this->cm->course, $this->user);
            if (!isset($formatmessage->html)) {
                continue;
            }
            if (empty($lasttime) || (($message->timestamp - $lasttime) > CHAT_SESSION_GAP)) {
                $content .= '<hr />';
                $content .= userdate($message->timestamp);
            }
            $content .= $formatmessage->html;
            $lasttime = $message->timestamp;
        }
        $content = preg_replace('/\<img[^>]*\>/', '', $content);

        $this->exporter->write_new_file($content, clean_filename($this->cm->name . '-session.html'), false);
    }

    /**
     * Function display_name
     *
     * @return string|void
     * @throws coding_exception
     */
    public static function display_name() {
        return get_string('modulename', 'chat');
    }

    /**
     * Function get_return_url
     *
     * @return string
     */
    public function get_return_url() {
        global $CFG;

        return $CFG->wwwroot . '/mod/chat/report.php?id='
            . $this->cm->id . ((isset($this->start)) ? '&start=' . $this->start . '&end=' . $this->end : '');
    }
}
