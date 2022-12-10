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
    public function get_content() {
        global $OUTPUT;
        $courseid = $this->page->course->id;
        $context = context_course::instance($courseid);
        $userobjects = enrol_get_course_users($courseid);
        $usersarray = json_decode(json_encode($userobjects), true);
        $userids = array_keys($usersarray);
        $users = $userids;
        $pairs = $this->compute_draw($userids);

        // If content is cached.
        if ($this->content !== null) {
            return $this->content;
        }

        $data = new stdClass();
        $data->toofewusers = empty($userobjects) || count($userobjects) < 3;
        $data->name = 'A name';
        $data->drawn = false;
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

    /**
     * Retrieve draw from DB, now just compute on the fly
     */
    public function get_draw() {

    }

    /**
     * Save draw in the DB.
     */
    public function save_draw() {

    }

    /**
     * Delete draw from the DB.
     */
    public function delete_draw() {

    }
}
