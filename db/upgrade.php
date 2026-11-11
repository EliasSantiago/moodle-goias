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
 * Upgrade steps for theme_goias.
 *
 * @package    theme_goias
 * @copyright  2026 Elias Fonseca, Cesar Augusto
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrades theme_goias.
 *
 * @param int $oldversion Version being upgraded from.
 * @return bool
 */
function xmldb_theme_goias_upgrade($oldversion) {
    if ($oldversion < 2026111101) {
        // 1.1.0 adopts the Design System Goiás: sites still on the old graphite
        // default move to the government green. Custom colours are kept.
        if (get_config('theme_goias', 'brandcolor') === '#27272a') {
            set_config('brandcolor', '#15967d', 'theme_goias');
        }
        theme_reset_all_caches();
        upgrade_plugin_savepoint(true, 2026111101, 'theme', 'goias');
    }

    return true;
}
