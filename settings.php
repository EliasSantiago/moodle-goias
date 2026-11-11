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
 * Admin settings for theme_goias.
 *
 * @package    theme_goias
 * @copyright  2026 Elias Fonseca, Cesar Augusto
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettinggoias', get_string('configtitle', 'theme_goias'));

    // General.
    $page = new admin_settingpage('theme_goias_general', get_string('generalsettings', 'theme_goias'));

    $setting = new admin_setting_configcolourpicker(
        'theme_goias/brandcolor',
        get_string('brandcolor', 'theme_goias'),
        get_string('brandcolor_desc', 'theme_goias'),
        THEME_GOIAS_DEFAULT_BRANDCOLOR
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $setting = new admin_setting_configstoredfile(
        'theme_goias/logo',
        get_string('logo', 'theme_goias'),
        get_string('logo_desc', 'theme_goias'),
        'logo',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['web_image']]
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $setting = new admin_setting_configstoredfile(
        'theme_goias/logocompact',
        get_string('logocompact', 'theme_goias'),
        get_string('logocompact_desc', 'theme_goias'),
        'logocompact',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['web_image']]
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    // Colour scheme.
    $page = new admin_settingpage('theme_goias_colorscheme', get_string('colorschemesettings', 'theme_goias'));

    $page->add(new admin_setting_configcheckbox(
        'theme_goias/darkmode',
        get_string('darkmode', 'theme_goias'),
        get_string('darkmode_desc', 'theme_goias'),
        1
    ));

    $page->add(new admin_setting_configselect(
        'theme_goias/defaultscheme',
        get_string('defaultscheme', 'theme_goias'),
        get_string('defaultscheme_desc', 'theme_goias'),
        'auto',
        [
            'auto' => get_string('scheme_auto', 'theme_goias'),
            'light' => get_string('scheme_light', 'theme_goias'),
            'dark' => get_string('scheme_dark', 'theme_goias'),
        ]
    ));

    $settings->add($page);

    // Login page.
    $page = new admin_settingpage('theme_goias_login', get_string('loginsettings', 'theme_goias'));

    $page->add(new admin_setting_configtext(
        'theme_goias/logintitle',
        get_string('logintitle', 'theme_goias'),
        get_string('logintitle_desc', 'theme_goias'),
        '',
        PARAM_TEXT
    ));

    $page->add(new admin_setting_confightmleditor(
        'theme_goias/logintext',
        get_string('logintext', 'theme_goias'),
        get_string('logintext_desc', 'theme_goias'),
        ''
    ));

    $settings->add($page);

    // Advanced.
    $page = new admin_settingpage('theme_goias_advanced', get_string('advancedsettings', 'theme_goias'));

    $setting = new admin_setting_scsscode(
        'theme_goias/scsspre',
        get_string('rawscsspre', 'theme_goias'),
        get_string('rawscsspre_desc', 'theme_goias'),
        '',
        PARAM_RAW
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $setting = new admin_setting_scsscode(
        'theme_goias/scss',
        get_string('rawscss', 'theme_goias'),
        get_string('rawscss_desc', 'theme_goias'),
        '',
        PARAM_RAW
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
}
