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

namespace theme_goias\output;

use context_system;
use moodle_url;
use theme_goias\local\colorscheme;

/**
 * Core renderer for theme_goias.
 *
 * Adds the colour scheme (html attributes, early head script and selector), the
 * LogoMark of the navbar, the login layout data and the logos configured in the
 * theme settings.
 *
 * @package    theme_goias
 * @copyright  2026 Elias Fonseca, Cesar Augusto
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {
    /** @var string[][] Heroicons (outline) paths of the scheme options. */
    protected const SCHEME_ICONS = [
        colorscheme::LIGHT => [
            'M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591'
                . 'M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z',
        ],
        colorscheme::DARK => [
            'M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752'
                . 'A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z',
        ],
        colorscheme::CONTRAST => [
            'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178'
                . '.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z',
            'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
        ],
        colorscheme::AUTO => [
            'M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15'
                . 'a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25'
                . 'A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25',
        ],
    ];

    /** @var bool Whether the colour scheme selector JavaScript was already requested. */
    protected $goiasschemejs = false;

    /**
     * Adds the colour scheme attributes to the <html> tag.
     *
     * data-goias-scheme is the user choice (light|dark|contrast|auto), data-goias-theme the
     * resolved scheme used by the CSS tokens and data-bs-theme the Bootstrap colour mode.
     *
     * @return string
     */
    public function htmlattributes() {
        $attributes = parent::htmlattributes();
        $scheme = colorscheme::get_current();
        $attributes .= ' data-bs-theme="' . colorscheme::get_rendered($scheme) . '"';
        $attributes .= ' data-goias-theme="' . colorscheme::get_resolved($scheme) . '"';
        if (colorscheme::is_enabled()) {
            $attributes .= ' data-goias-scheme="' . $scheme . '"';
            if (!colorscheme::can_store_preference()) {
                // Guests keep their choice in the browser.
                $attributes .= ' data-goias-local="1"';
            }
        }
        return $attributes;
    }

    /**
     * Prepends a tiny blocking script that resolves "auto" and the guest choice before the CSS
     * loads (avoids a flash of the wrong scheme).
     *
     * @return string
     */
    public function standard_head_html() {
        $output = parent::standard_head_html();
        if (!colorscheme::is_enabled()) {
            return $output;
        }
        $script = "(function(){var d=document.documentElement,s=d.getAttribute('data-goias-scheme');"
            . "try{if(d.getAttribute('data-goias-local')==='1'){var l=window.localStorage.getItem('"
            . colorscheme::PREFERENCE . "');if(['light','dark','contrast','auto'].indexOf(l)>-1){s=l;"
            . "d.setAttribute('data-goias-scheme',l);}}}catch(e){}"
            . "if(s==='auto'){s=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches"
            . "?'dark':'light';}if(['light','dark','contrast'].indexOf(s)<0){s='light';}"
            . "d.setAttribute('data-goias-theme',s);d.setAttribute('data-bs-theme',s==='light'?'light':'dark');})();";
        return \html_writer::tag('script', $script) . "\n" . $output;
    }

    /**
     * Adds the colour scheme selector to the navbar (before notifications and messages).
     *
     * @return string
     */
    public function navbar_plugin_output() {
        return $this->goias_colorscheme_selector() . parent::navbar_plugin_output();
    }

    /**
     * Colour scheme selector (light, dark, high contrast, follow the device).
     *
     * @return string Empty when the site does not let users choose.
     */
    public function goias_colorscheme_selector(): string {
        if (!colorscheme::is_enabled()) {
            return '';
        }
        if (!$this->goiasschemejs) {
            $this->page->requires->js_call_amd('theme_goias/colorscheme', 'init', [colorscheme::can_store_preference()]);
            $this->goiasschemejs = true;
        }

        $schemes = [];
        foreach ([colorscheme::LIGHT, colorscheme::DARK, colorscheme::CONTRAST, colorscheme::AUTO] as $scheme) {
            $schemes[] = [
                'value' => $scheme,
                'label' => get_string('scheme_' . $scheme, 'theme_goias'),
                'paths' => self::SCHEME_ICONS[$scheme],
            ];
        }

        return $this->render_from_template('theme_goias/colorscheme_toggle', [
            'uniqid' => \html_writer::random_id('goias-colorscheme'),
            'schemes' => $schemes,
        ]);
    }

    /**
     * Organisation name shown in the LogoMark, the government bar and the login panel.
     *
     * @return string Formatted name, or empty string.
     */
    public function goias_organization(): string {
        $organization = get_config('theme_goias', 'organization');
        if ($organization === false) {
            $organization = get_string('organization_default', 'theme_goias');
        }
        return format_string((string) $organization, true, ['context' => context_system::instance()]);
    }

    /**
     * Context for the LogoMark of the navbar (theme_boost/navbar override).
     *
     * @return array
     */
    public function goias_brand(): array {
        global $SITE;

        $name = trim(format_string($SITE->shortname, true, ['context' => context_system::instance()]));
        return [
            'initial' => \core_text::strtoupper(\core_text::substr($name, 0, 1)),
            'organization' => $this->goias_organization(),
        ];
    }

    /**
     * Context for the login layout (core/login_layout override): government bar,
     * back to home link and the colour scheme selector.
     *
     * @return array
     */
    public function goias_auth(): array {
        $govbarurl = (string) get_config('theme_goias', 'govbarurl');
        $showgovbar = get_config('theme_goias', 'showgovbar');
        $organization = $this->goias_organization();
        $host = $govbarurl !== '' ? (string) parse_url($govbarurl, PHP_URL_HOST) : '';

        return [
            'showgovbar' => ($showgovbar === false || (bool) $showgovbar) && $organization !== '',
            'organization' => $organization,
            'govbarurl' => $host !== '' ? $govbarurl : '',
            'govbarhost' => preg_replace('/^www\./', '', $host),
            'homeurl' => (new moodle_url('/'))->out(false),
            'colorscheme' => $this->goias_colorscheme_selector(),
        ];
    }

    /**
     * Context for the left panel of the login page (used by core/login_panel).
     *
     * @return array
     */
    public function goias_login_panel(): array {
        global $SITE;

        $title = (string) get_config('theme_goias', 'logintitle');
        $text = (string) get_config('theme_goias', 'logintext');
        $context = context_system::instance();
        $logo = $this->get_theme_logo_url('logocompact') ?: $this->get_theme_logo_url('logo');

        return [
            'eyebrow' => $this->goias_organization(),
            'title' => format_string($title !== '' ? $title : $SITE->fullname, true, ['context' => $context]),
            'text' => $text !== ''
                ? format_text($text, FORMAT_HTML, ['context' => $context])
                : \html_writer::tag('p', get_string('logintext_default', 'theme_goias')),
            'logourl' => $logo ? $logo->out(false) : '',
        ];
    }

    /**
     * Logo shown on the login page: theme logo, falling back to the site logo.
     *
     * @param int|null $maxwidth
     * @param int $maxheight
     * @return moodle_url|false
     */
    public function get_logo_url($maxwidth = null, $maxheight = 200) {
        return $this->get_theme_logo_url('logo') ?: parent::get_logo_url($maxwidth, $maxheight);
    }

    /**
     * Logo shown on the navbar: theme compact logo, then theme logo, then the site compact logo.
     *
     * @param int $maxwidth
     * @param int $maxheight
     * @return moodle_url|false
     */
    public function get_compact_logo_url($maxwidth = 300, $maxheight = 300) {
        return $this->get_theme_logo_url('logocompact')
            ?: $this->get_theme_logo_url('logo')
            ?: parent::get_compact_logo_url($maxwidth, $maxheight);
    }

    /**
     * URL of a logo uploaded in the theme settings.
     *
     * @param string $setting Setting (and file area) name.
     * @return moodle_url|false
     */
    protected function get_theme_logo_url(string $setting) {
        $filepath = get_config('theme_goias', $setting);
        if (empty($filepath)) {
            return false;
        }
        return moodle_url::make_pluginfile_url(
            context_system::instance()->id,
            'theme_goias',
            $setting,
            theme_get_revision(),
            '/',
            ltrim($filepath, '/')
        );
    }
}
