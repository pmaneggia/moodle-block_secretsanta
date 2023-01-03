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
 * Data access for block_secretsanta.
 *
 * @package    block_secretsanta
 * @copyright  2022-23 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_secretsanta;

class secretsanta_dao {

    public static function read_instance($courseid) {
        global $DB;
        $secretsantarecord = $DB->get_record(
            'block_secretsanta',
            ['courseid' => $courseid],
            'id, courseid, state, draw, selectedparticipants',
            MUST_EXIST
        );
        $userinfos = get_enrolled_users(\context_course::instance($courseid), '', 0, 'u.id, u.firstname, u.lastname');
        return new \block_secretsanta\secretsanta($secretsantarecord, $userinfos);
    }

    public static function update($secretsanta) {
        global $DB;
        $DB->update_record('block_secretsanta', $secretsanta->as_db_row());
    }

    public static function insert_initial($courseid) {
        // Add an entry to the table {block_secretsanta} representing an instance in initial state.
        global $DB;
        $DB->insert_record(
            'block_secretsanta',
            \block_secretsanta\secretsanta::create_initial_db_row(
                $courseid,
                array_keys(get_enrolled_users(\context_course::instance($courseid), '', 0, 'u.id'))
            )
        );
    }

    public static function delete($courseid) {
        global $DB;
        $DB->delete_records('block_secretsanta', ['courseid' => $courseid]);
    }

}
