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
 * Renderable for view_draw.php page of block secretsanta.
 *
 * @package    block_secretsanta
 * @copyright  2023 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_secretsanta\output;

use moodle_url;
use renderable;
use renderer_base;
use templatable;
use stdClass;
/**
 * Renderable for view_draw.php page of block secretsanta.
 *
 * @package    block_availdep
 * @copyright  2023 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_draw_page implements renderable, templatable {

    /**
     * Courseid of the current course.
     * @var $courseid
     */
    private int $courseid;

    /**
     * List of pairs representing the draw, json encoded.
     * @var $draw
     */
    private string $draw;

    /**
     * Construct a renderable for the page relative to the current course.
     * @param int $courseid id of the current course.
     * @param string $draw json encoded array of pairs.
     */
    public function __construct(int $courseid, string $draw) {
        $this->courseid = $courseid;
        $this->draw = $draw;
    }

    /**
     * Export data so it can be used as the context for a mustache template.
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->drawitems = $this->draw_with_names();
        $data->backtocourseurl = (new moodle_url('/course/view.php', ['id' => $this->courseid]))->out(false);
        return $data;
    }

    private function draw_with_names() {
        $userinfos = get_enrolled_users(\context_course::instance($this->courseid), '', 0, 'u.id, u.firstname, u.lastname');
        return array_map(
            fn ($e) => [
                $userinfos[$e[0]]->firstname . ' ' . $userinfos[$e[0]]->lastname,
                $userinfos[$e[1]]->firstname . ' ' . $userinfos[$e[1]]->lastname
            ],
            json_decode($this->draw)
        );
    }
}
