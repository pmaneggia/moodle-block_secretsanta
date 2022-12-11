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
 * Play Secret Santa with the course participants!
 *
 * @package    block_secretsanta
 * @copyright  2022 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/modinfolib.php");

/**
 * Play Secret Santa in a course.
 */
class block_secretsanta extends block_base {

    /** Id relative to the current block instance from the table {block_secretsanta}. */
    private int $instanceid;

    /** State of the current block instance. */
    private int $state;

    /** Draw of the current block instance. */
    private string $draw;

    /**
     * {@inheritDoc}
     */
    public function init() {
        $this->title = get_string('secretsanta', 'block_secretsanta');
    }

    /**
     * {@inheritDoc}
     */
    public function applicable_formats() {
        return array('course' => true);
    }

    /**
     * {@inheritDoc}
     */
    public function instance_create() {
        // Add an entry to the table {block_secretsanta} representing an instance in initial state.
        global $DB;
        $course = $this->page->course;
        $data = new stdClass();
        $data->courseid = $course->id;
        $data->state = $this->state = 0;
        $data->draw = $this->draw = '';
        $this->instanceid = $DB->insert_record('block_secretsanta', $data);
    }

    /**
     * {@inheritDoc}
     */
    function instance_delete() {
        global $DB;
        $DB->delete_records('block_secretsanta', ['courseid' => $this->page->course->id]);
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function get_content() {
        // If content is cached.
        if ($this->content !== null) {
            return $this->content;
        }

        global $OUTPUT, $DB;

        // Load the state from the database.
        $courseid = $this->page->course->id;
        if (empty($this->instanceid)) {
            $result = $DB->get_record('block_secretsanta', ['courseid' => $courseid], 'id, state, draw', MUST_EXIST);
        }
        $this->instanceid = $result->id;
        $this->state = $result->state;
        $this->draw = $result->draw;

        // Get the users enrolled in the course.
        $context = context_course::instance($courseid);
        $userobjects = enrol_get_course_users($courseid);
        $usersarray = json_decode(json_encode($userobjects), true);
        $userids = array_keys($usersarray);
        $users = $userids;
        $pairs = $this->compute_draw($userids);

        $data = new stdClass();
        $data->toofewusers = empty($userobjects) || count($userobjects) < 3;
        $data->name = 'A name';
        $data->drawn = $this->state === 1;
        $data->candraw = $this->can_draw($context);
        $data->users = print_r($users, true);
        $data->pairs = print_r($pairs, true);
        $data->canviewresult = $this->can_view_result($context);

        $this->content = new stdClass();
        $this->content->text = $OUTPUT->render_from_template('block_secretsanta/content', $data);;
        $this->content->footer = '';

        return $this->content;
    }

    public function can_draw($context) {
        return has_capability('block/secretsanta:draw', $context);
    }

    public function can_view_result($context) {
        return has_capability('block/secretsanta:canviewresult', $context);
    }

    /**
     * Draw secret santa among the given userids.
     * The result is a array of pairs of id [from, to].
     * It is always going to correspond to a full length cycle:
     * Draw the first user and remove it from the input;
     * while some users are still left, draw a further user;
     * this will be the target of the previously drawn user.
     * The last user left closes the cycle being paired with the first one.
     * @param array $userids array of userids.
     * @return array<int[]> array of pairs if int [from, to].
     */
    public function compute_draw($userids) {
        if(empty($userids) || !count($userids) > 1) {
            debugging('block Secret Santa: not enough users to play');
            return [];
        }
        $result = [];
        $currentdrawkey = array_rand($userids);
        $firstdraw = $userids[$currentdrawkey];
        $currentdraw = $firstdraw;
        unset($userids[$currentdrawkey]);
        $remaining = $userids;
        while (count($remaining) > 0) {
            $newdrawindex = array_rand($remaining);
            $newdraw = $remaining[$newdrawindex];
            array_push($result, [$currentdraw, $newdraw]);
            unset($remaining[$newdrawindex]);
            $currentdrawkey = $newdrawindex;
            $currentdraw = $newdraw;
        }
        array_push($result, [$currentdraw, $firstdraw]);
        return $result;
    }

}
