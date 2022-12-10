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
        $users = enrol_get_course_users($courseid);

        // If content is cached.
        if ($this->content !== null) {
            return $this->content;
        }

        $data = new stdClass();
        $data->name = 'A name';
        $data->drawn = false;
        $data->candraw = $this->can_draw($context);
        $data->users = print_r($users, true);

        $this->content = new stdClass();
        $this->content->text = $OUTPUT->render_from_template('block_secretsanta/content', $data);;
        $this->content->footer = '';

        return $this->content;
    }

    public function can_draw($context) {
        return has_capability('block/secretsanta:draw', $context);
    }
}
