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
 * PHP side implementation of the action select participants.
 *
 * @package    block_secretsanta
 * @copyright  2023 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$participants = required_param('participants', PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_secretsanta', $courseid);
}

require_login($course);

if(!$instanceid = $DB->get_record('block_secretsanta', array('courseid' => $courseid))) {
    print_error('noinstance', 'block_secretsanta', '', $instanceid);
}

$PAGE->set_url('/blocks/secretsanta/action_selectparticipants.php', array('courseid' => $courseid));

if (confirm_sesskey()) {
    $secretsanta = \block_secretsanta\secretsanta_dao::read_instance($courseid);
    $secretsanta->set_selectedparticipants($participants);
    \block_secretsanta\secretsanta_dao::update($secretsanta);
} else {
    print_error('sessionerror', 'block_simplehtml');
}
redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
