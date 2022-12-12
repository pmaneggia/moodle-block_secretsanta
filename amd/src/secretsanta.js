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
 * Actions for block_secretsanta.
 *
 * @copyright  2022 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @module     block/secretsanta
 */

import Ajax from 'core/ajax';

const registerEventListener = () => {
    document.querySelector('.block_secretsanta [data-action=draw]').addEventListener('click', block_secretsanta_draw);
    document.querySelector('.block_secretsanta [data-action=reset]').addEventListener('click', block_secretsanta_reset);
}

export const init = (courseid) => {
    Promise.all(
        Ajax.call(
            [
                {
                    methodname: 'block_secretsanta_draw',
                    args: {courseid: courseid}
                },
                {
                    methodname: 'block_secretsanta_reset',
                    args: {courseid: courseid}
                }
            ]
        )
    ).then(() => {
            registerEventListener();
            return;
        }).catch();
};