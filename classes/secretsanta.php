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

    /** Id relative to the current block instance from the table {block_secretsanta}. */
    private static int $instanceid;

    /** State of the current block instance. */
    private static int $state;

    /** Draw of the current block instance. */
    private static string $draw;

    public static function draw($courseid) {
        self::set_state($courseid, 1);
        self::save_draw($courseid, json_encode(self::compute_draw(self::get_enrolled_user_ids($courseid))));
    }

    public static function reset($courseid) {
        self::set_state($courseid, 0);
        self::clean_draw($courseid);
    }

    protected static function set_state($courseid, $state) {
        global $DB;
        $dataobject = new stdClass();
        if (empty(self::$instanceid)) {
            self::load_secretsanta($courseid);
        }
        $dataobject->id = self::$instanceid;
        $dataobject->courseid = $courseid;
        $dataobject->state = $state;
        self::$state = $state;
        $DB->update_record('block_secretsanta', $dataobject);
    }

    protected static function save_draw($courseid, $draw) {
        global $DB;
        $dataobject = new stdClass();
        if (empty(self::$instanceid)) {
            self::load_secretsanta($courseid);
        }
        $dataobject->id = self::$instanceid;
        $dataobject->courseid = $courseid;
        $dataobject->draw = $draw;
        self::$draw = $draw;
        $DB->update_record('block_secretsanta', $dataobject);
    }

    protected static function clean_draw($courseid) {
        global $DB;
        $dataobject = new stdClass();
        if (empty(self::$instanceid)) {
            self::load_secretsanta($courseid);
        }
        $dataobject->id = self::$instanceid;
        $dataobject->courseid = $courseid;
        $dataobject->draw = '';
        self::$draw = '';
        $DB->update_record('block_secretsanta', $dataobject);
    }

    /**
     * Get userids of users enrolled in course.
     * @param $courseid
     * @return int[]
     */
    public static function get_enrolled_user_ids($courseid) {
        return $userids = array_map(
            fn ($element) => (int)$element['id'],
            json_decode(json_encode(get_enrolled_users(\context_course::instance($courseid), '', 0, 'u.id')), true)
        );
    }

    /**
     * Get array of useris, firstname and lastname fields of users enrolled in course.
     */
    protected static function get_enrolled_user_infos($courseid) {
        return get_enrolled_users(\context_course::instance($courseid), '', 0, 'u.id, u.firstname, u.lastname');
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
    protected static function compute_draw($userids) {
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
     * Get the draw for the current block_secretsanta instance in course with given id.
     * @param int $courseid id of the course to which this instance of block_secresanta belongs.
     * @return string representing the draw or the empty string if no draw was saved.
     */
    public static function get_draw($courseid) {
        global $DB;
        if (empty(self::$instanceid)) {
            self::load_secretsanta($courseid);
        } // TODO make up your mind! this or self::$state
        return $DB->get_field('block_secretsanta', 'draw', ['id' => self::$instanceid, 'courseid' => $courseid], MUST_EXIST);
    }

    /**
     * Get the state for the current block_secretsanta instance in course with given id.
     * @param int $courseid id of the course to which this instance of block_secresanta belongs.
     * @return int 0 for initial, 1 for draw.
     */
    public static function get_state($courseid) {
        global $DB;
        $dataobject = new stdClass();
        if (empty(self::$instanceid)) {
            self::load_secretsanta($courseid);
        } // TODO make up your mind! this or self::$state
        return (int)$DB->get_field('block_secretsanta', 'state', ['id' => self::$instanceid, 'courseid' => $courseid], MUST_EXIST);
    }

    /**
     * Get the name of the user drawn for the current user.
     * @param int $courseid id of the course of this instance of block_secretsanta.
     * @param int $userid id of the user for which we return the drawn match.
     * @return string containing name and surname of the drawn match.
     */
    public static function get_draw_for_user($courseid, $userid) {
        $draw = self::get_draw($courseid);
        if (empty($draw)) {
            return '';
        }
        $usersinfos = json_decode(json_encode(self::get_enrolled_user_infos($courseid)), true);
        $targetuserid = (
            array_values(
                array_filter(
                    json_decode($draw, true),
                    fn ($element) => (int)$element[0] === (int)$userid
                )
            )[0]
        )[1];
        $targetuserinfos = self::get_enrolled_user_infos($courseid)[$targetuserid];
        return $targetuserinfos->firstname . ' ' . $targetuserinfos->lastname;
    }

    /**
     * Load the current data for this course from the database.
     */
    public static function load_secretsanta($courseid) {
        global $DB;
        if (empty(self::$instanceid)) {
            $result = $DB->get_record('block_secretsanta', ['courseid' => $courseid], 'id, state, draw', MUST_EXIST);
        }
        self::$instanceid = $result->id;
        self::$state = $result->state;
        self::$draw = $result->draw;
    }

}