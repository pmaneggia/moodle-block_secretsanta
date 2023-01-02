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
 * Main functions for block_secretsanta.
 *
 * @package    block_secretsanta
 * @copyright  2022 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_secretsanta;

use stdClass;

defined('MOODLE_INTERNAL') || die();

class secretsanta {

    /** Id of the course this block instance has been added to. */
    private int $courseid;

    /** Id relative to the current block instance from the table {block_secretsanta}. */
    private int $instanceid;

    /** State of the current block instance. */
    private int $state;

    /** Draw of the current block instance. */
    private string $draw;

    /** Ids of users enrolled in the course. */
    private array $enrolled_user_ids;

    /** Infos (id, firstname, lastname) of users enrolled in the course. */
    private array $enrolled_user_infos;

    /**
     * Initialise the fields of this object for a given courseid.
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;
        $this->load_data();
        $this->populate_user_fields();
    }

    public function draw() {
        $this->save_draw(json_encode($this->compute_draw($this->enrolled_user_ids)));
    }

    public function reset() {
        $this->clean_draw();
    }

    protected function save_draw($draw) {
        global $DB;
        $dataobject = new stdClass();
        if (empty($this->instanceid)) {
            $this->load_data();
        }
        $dataobject->id = $this->instanceid;
        $dataobject->courseid = $this->courseid;
        $dataobject->draw = $draw;
        $dataobject->state = 1;
        $this->draw = $draw;
        $DB->update_record('block_secretsanta', $dataobject);
    }

    protected function clean_draw() {
        global $DB;
        $dataobject = new stdClass();
        $dataobject->id = $this->instanceid;
        $dataobject->courseid = $this->courseid;
        $dataobject->draw = '';
        $dataobject->state = 0;
        $this->draw = '';
        $DB->update_record('block_secretsanta', $dataobject);
    }

    /**
     * Get userids of users enrolled in the course this instance belongs to.
     * @return int[]
     */
    public function get_enrolled_user_ids() {
        return $this->enrolled_user_ids;
    }

    /**
     * Get array of userids, firstname and lastname fields of users enrolled in course.
     */
    protected function get_enrolled_user_infos() {
        return $this->enrolled_user_infos;
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
    protected function compute_draw($userids) {
        $result = [];

        if (empty($userids) || !count($userids) > 1) {
            debugging('block Secret Santa: not enough users to play');
            return $result;
        }

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
     * Get the draw for the current block_secretsanta instance.
     * @return string representing the draw or the empty string if no draw was saved.
     */
    public function get_draw() {
        return $this->draw;
    }

    /**
     * Get the state for the current block_secretsanta instance.
     * @return int 0 for initial, 1 for draw.
     */
    public function get_state() {
        return $this->state;
    }

    /**
     * Get the name of the user drawn for the current user.
     * @return string containing name and surname of the drawn match.
     */
    public function get_draw_for_current_user() {
        global $USER;
        $draw = $this->draw;
        if (empty($draw)) {
            return '';
        }
        $targetuserid = (
            array_values(
                array_filter(
                    json_decode($draw, true),
                    fn ($element) => (int)$element[0] === (int)$USER->id
                )
            )[0]
        )[1];
        $targetuserinfos = $this->enrolled_user_infos[$targetuserid];
        return $targetuserinfos->firstname . ' ' . $targetuserinfos->lastname;
    }

    /**
     * Load the current data for this instance from the database.
     */
    public function load_data() {
        global $DB;
        $data = $DB->get_record('block_secretsanta', ['courseid' => $this->courseid], 'id, state, draw', MUST_EXIST);
        $this->instanceid = $data->id;
        $this->state = $data->state;
        $this->draw = $data->draw;
    }

    /**
     * Populate user fields relevant for the draw.
     */
    private function populate_user_fields() {
        $this->enrolled_user_infos = get_enrolled_users(\context_course::instance($this->courseid), '', 0, 'u.id, u.firstname, u.lastname');
        $this->enrolled_user_ids = array_keys($this->enrolled_user_infos);
    }

}