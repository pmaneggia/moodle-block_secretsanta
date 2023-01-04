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
 * Renderer for page view.php of block availdep.
 *
 * @package    block_secretsanta
 * @copyright  2023 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_secretsanta\output;

use plugin_renderer_base;

/**
 * Renderer for page view_draw.php of block secretsanta.
 *
 * @package    block_secretsanta
 * @copyright  2023 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Defer to template.
     * @param view_draw_page $renderable
     * @return string html for the page
     */
    public function render_view_draw_page($renderable) {
        $data = $renderable->export_for_template($this);
        return parent::render_from_template('block_secretsanta/view_draw_page', $data);
    }
}
