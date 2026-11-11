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
 * Library callbacks for theme_goias.
 *
 * @package    theme_goias
 * @copyright  2026 Elias Fonseca, Cesar Augusto
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** Default accent colour (graphite). */
define('THEME_GOIAS_DEFAULT_BRANDCOLOR', '#27272a');

/**
 * Returns the main SCSS content: Boost's default preset plus the Goiás layer.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_goias_get_main_scss_content($theme) {
    global $CFG;

    $scss = file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    $scss .= "\n" . file_get_contents($CFG->dirroot . '/theme/goias/scss/goias.scss');

    return $scss;
}

/**
 * Returns the SCSS prepended to the main content (variables).
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_goias_get_pre_scss($theme) {
    global $CFG;

    $brand = theme_goias_clean_colour($theme->settings->brandcolor ?? '') ?: THEME_GOIAS_DEFAULT_BRANDCOLOR;

    $scss = '$goias-brand: ' . $brand . ";\n";
    $scss .= file_get_contents($CFG->dirroot . '/theme/goias/scss/goias/_variables.scss');

    if (!empty($theme->settings->scsspre)) {
        $scss .= "\n" . $theme->settings->scsspre;
    }

    return $scss;
}

/**
 * Returns the SCSS appended after the main content (admin custom SCSS).
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_goias_get_extra_scss($theme) {
    return !empty($theme->settings->scss) ? $theme->settings->scss : '';
}

/**
 * Returns the precompiled CSS used when SCSS compilation is not possible.
 *
 * @return string
 */
function theme_goias_get_precompiled_css() {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/boost/style/moodle.css');
}

/**
 * Validates a hex colour coming from the settings.
 *
 * @param string $colour Raw value.
 * @return string The colour, or empty string when invalid.
 */
function theme_goias_clean_colour(string $colour): string {
    $colour = trim($colour);
    return preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $colour) ? $colour : '';
}

/**
 * Serves the files uploaded in the theme settings (logo).
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_goias_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel == CONTEXT_SYSTEM && in_array($filearea, ['logo', 'logocompact'])) {
        $theme = theme_config::load('goias');
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }
    send_file_not_found();
}

/**
 * User preferences managed by this theme (allows saving them through the web service).
 *
 * @return array[]
 */
function theme_goias_user_preferences(): array {
    return [
        \theme_goias\local\colorscheme::PREFERENCE => [
            'type' => PARAM_ALPHA,
            'null' => NULL_NOT_ALLOWED,
            'default' => \theme_goias\local\colorscheme::AUTO,
            'choices' => \theme_goias\local\colorscheme::SCHEMES,
            'permissioncallback' => [core_user::class, 'is_current_user'],
        ],
    ];
}
