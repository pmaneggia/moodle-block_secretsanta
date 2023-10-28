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

    /** @var int State before an assignment has been drawn. */
    const STATE_INITIAL = 0;

    /** @var int State after draw. */
    const STATE_DRAWN = 1;

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

    /** Ids of the users that have been selected as participants. */
    private array $selectedparticipants;

    /**
     * Initialise the fields of this object for a given instance.
     * @param array $sectresantarecord record for this instance storing state and draw.
     * @param array $userinfos users relevant for this instance with id, firstname and lastname.
     */
    public function __construct($secretsantarecord, $userinfos) {
        $this->courseid = $secretsantarecord->courseid;
        $this->instanceid = $secretsantarecord->id;
        $this->state = $secretsantarecord->state;
        $this->draw = $secretsantarecord->draw;
        $this->enrolled_user_infos = $userinfos;
        $this->enrolled_user_ids = array_keys($userinfos);
        $this->selectedparticipants = json_decode($secretsantarecord->selectedparticipants ?? '[]');
    }

    /**
     * Data to be persisted when first creating an instance for a course.
     * In initial state there exist no draw and the selected participants are
     * by default all users enrolled in the course.
     * @param int $courseid id of the course for which an instance has to be created.
     * @param array $userids id of all users enrolled in the course.
     */
    public static function create_initial_db_row($courseid, $userids) {
        $data = new stdClass();
        $data->courseid = $courseid;
        $data->state = \block_secretsanta\secretsanta::STATE_INITIAL;
        $data->draw = '';
        $data->selectedparticipants = json_encode($userids);
        return $data;
    }

    /**
     * Draw secret santa among the selected participants.
     */
    public function draw() {
        $this->draw = json_encode($this->compute_draw($this->selectedparticipants));
        $this->state = self::STATE_DRAWN;
    }

    /**
     * Reset: draw empty, initial state, list of selected participants back
     * to the default value of containing all the users enrolled in the course.
     */
    public function reset() {
        $this->draw = '';
        $this->state = self::STATE_INITIAL;
        $this->selectedparticipants = $this->enrolled_user_ids;
    }

    /**
     * Set the list of selected participants.
     * @paran int[] $participants list of user ids.
     */
    public function set_selectedparticipants($participants) {
        // Only change selected participants if the draw was reset.
        if ($this->draw) return;
        $this->selectedparticipants = $participants;
    }

    /**
     * Give the fields needed for updating the corresponding entry
     * in the database.
     * @return stdClass data object
     */
    public function as_db_row() {
        $dataobject = new stdClass();
        $dataobject->id = $this->instanceid;
        $dataobject->courseid = $this->courseid;
        $dataobject->state = $this->state;
        $dataobject->draw = $this->draw;
        $dataobject->selectedparticipants = json_encode($this->selectedparticipants);
        return $dataobject;
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
     * @return array
     */
    protected function get_enrolled_user_infos() {
        return $this->enrolled_user_infos;
    }

    public function has_too_few_users() {
        return empty($this->selectedparticipants) || count($this->selectedparticipants) < 3;
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
     * Check wether state is STATE_DRAWN.
     * @return bool
     */
    public function is_drawn() {
        return $this->state === self::STATE_DRAWN;
    }

    /**
     * Get userids of users enrolled in the course and selected as participants.
     * @return int[]
     */
    public function get_selectedparticipants() {
        return $this->selectedparticipants;
    }

    /**
     * Get the name of the user drawn for the given user.
     * @param int userid
     * @return string containing name and surname of the drawn match.
     */
    public function get_draw_for_user($userid) {
        $draw = $this->draw;
        if (empty($draw) || !in_array($userid, $this->selectedparticipants)) {
            return '';
        }
        $targetuserid = (
            array_values(
                array_filter(
                    json_decode($draw, true),
                    fn ($element) => (int)$element[0] === $userid
                )
            )[0]
        )[1];
        // It can happen that the target user in the meantime unenrolled from the course.
        if (!array_key_exists($targetuserid, $this->enrolled_user_infos)) {
            return '';
        }
        $targetuserinfos = $this->enrolled_user_infos[$targetuserid];
        return $targetuserinfos->firstname . ' ' . $targetuserinfos->lastname;
    }

    /**
     * Is the given user among the participating ones?
     * @return boolean true if the user is participating.
     */
    public function is_participating($userid) {
        return array_search($userid, $this->selectedparticipants) !== false;
    }
}
