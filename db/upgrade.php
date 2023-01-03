<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Upgrade functions for block_secretsanta.
 *
 * @package    block_secretsanta
 * @copyright  2022 Paola Maneggia
 * @author     Paola Maneggia <paola.maneggia@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define upgrade steps to be performed to upgrade the plugin from the old version to the current one.
 *
 * @param int $oldversion Version number the plugin is being upgraded from.
 */
function xmldb_block_secretsanta_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2022121100) {

        // Define table block_secretsanta to be created.
        $table = new xmldb_table('block_secretsanta');

        // Adding fields to table block_secretsanta.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('state', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        $table->add_field('draw', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table block_secretsanta.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for block_secretsanta.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Secretsanta savepoint reached.
        upgrade_block_savepoint(true, 2022121100, 'secretsanta');
    }

    if ($oldversion < 2023010300) {

        // Define field selectedparticipants to be added to block_secretsanta.
        $table = new xmldb_table('block_secretsanta');
        $field = new xmldb_field('selectedparticipants', XMLDB_TYPE_TEXT, null, null, null, null, null, 'draw');

        // Conditionally launch add field selectedparticipants.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Secretsanta savepoint reached.
        upgrade_block_savepoint(true, 2023010300, 'secretsanta');
    }

    return true;
}
