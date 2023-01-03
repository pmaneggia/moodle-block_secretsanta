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
 * Do Secret Santa with the course participants!
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
    public function instance_create() {
        \block_secretsanta\secretsanta_dao::insert_initial($this->page->course->id);
    }

    /**
     * {@inheritDoc}
     */
    function instance_delete() {
        \block_secretsanta\secretsanta_dao::delete($this->page->course->id);
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
        global $OUTPUT;
        $this->content = new stdClass();
        $this->content->text = $OUTPUT->render_from_template('block_secretsanta/content', $this->compute_data());
        $this->content->footer = '';
        return $this->content;
    }

    protected function compute_data() {
        $courseid = $this->page->course->id;
        return $this->extract_data(
            $courseid,
            \block_secretsanta\secretsanta_dao::read_instance($courseid)
        );
    }

    protected function extract_data($courseid, $secretsanta) {
        global $USER;
        $data = new stdClass();
        $data->toofewusers = $secretsanta->has_too_few_users();
        $data->isparticipating = $secretsanta->is_participating($USER->id);
        $data->drawn = $secretsanta->is_drawn();
        $data->name = $data->toofewusers || !$data->drawn ? '' : $secretsanta->get_draw_for_user((int)$USER->id);
        $data->notargetuser = $data->name === '';
        $context = context_course::instance($courseid);
        $data->candraw = $this->can_draw($context);
        $data->drawurl = new moodle_url('/blocks/secretsanta/action_draw.php', ['courseid' => $courseid]);
        $data->reseturl = new moodle_url('/blocks/secretsanta/action_reset.php', ['courseid' => $courseid]);
        $mform = new \block_secretsanta\selectparticipants_form(
            new moodle_url('/blocks/secretsanta/action_selectparticipants.php', ['courseid' => $courseid]),
            array(
                'courseid' => $courseid,
                'selectedparticipants' => $secretsanta->get_selectedparticipants()
            )
        );
        $data->selectusersform = $mform->render();
        $data->users = print_r(
            array_map(
                fn($element) => $element['firstname'] . ' ' . $element['lastname'],
                json_decode(json_encode(get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname')), true)
            ),
            true
        );
        $data->pairs = print_r($secretsanta->get_draw(), true);
        $data->canviewresult = $this->can_view_result($context);
        return $data;
    }

    public function can_draw($context) {
        return has_capability('block/secretsanta:draw', $context);
    }

    public function can_view_result($context) {
        return has_capability('block/secretsanta:canviewresult', $context);
    }

}
