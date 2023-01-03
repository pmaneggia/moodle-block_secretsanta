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
 * Form to select participants for block_secretsanta.
 *
 * @package    block_secretsanta
 * @copyright  2022 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_secretsanta;

require_once("$CFG->libdir/formslib.php");

class selectparticipants_form extends \moodleform {

    function definition() {
        $mform = $this->_form;
        $participants = array_map(
            function ($element) {
                $e = json_decode(json_encode($element), true);
                return $e['firstname'] . ' ' . $e['lastname'];
            },
            get_enrolled_users(\context_course::instance($this->_customdata['courseid']), '', 0, 'u.id, u.firstname, u.lastname')
        );
        print_r($participants);

        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('selectparticipants', 'block_secretsanta'),
        );
        $mform->addElement('autocomplete', 'selectparticipants', get_string('selectparticipants', 'block_secretsanta'), $participants, $options);

        $select = $mform->addElement(
            'select',
            'participants',
            get_string('selectparticipants', 'block_secretsanta'),
            $participants,
            []
        );
        $select->setMultiple(true);
        //$mform->setDefault('email',$this->_customdata['email'])
        $mform->addElement('submit', 'submitbutton', get_string('selectparticipantssubmit', 'block_secretsanta'));
    }
}
