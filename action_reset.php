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
 * PHP side implementation of the reset action.
 *
 * @package    block_secretsanta
 * @copyright  2022 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_secretsanta', $courseid);
}

require_login($course);

if(!$instanceid = $DB->get_record('block_secretsanta', array('courseid' => $courseid))) {
    print_error('noinstance', 'block_secretsanta', '', $instanceid);
}

$site = get_site();
$PAGE->set_url('/blocks/secretsanta/action_reset.php', array('courseid' => $courseid));
$heading = $site->fullname . ' :: ' . $course->shortname . ' :: reset';
$PAGE->set_heading($heading);
$PAGE->set_title('Secret Santa reset');
$PAGE->set_secondary_navigation(false);

if (!$confirm) {
    $optionsno = new moodle_url('/course/view.php', array('id' => $courseid));
    $optionsyes = new moodle_url('/blocks/secretsanta/action_reset.php', array('courseid' => $courseid, 'confirm' => 1, 'sesskey' => sesskey()));
    echo $OUTPUT->confirm(get_string('reset', 'block_secretsanta'), $optionsyes, $optionsno);
} else {
    if (confirm_sesskey()) {
        (new \block_secretsanta\secretsanta($courseid))->reset();
    } else {
        print_error('sessionerror', 'block_simplehtml');
    }
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
}

echo $OUTPUT->header();
echo $OUTPUT->footer();