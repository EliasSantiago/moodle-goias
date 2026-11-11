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
 * Adds the light/dark colour scheme (html attribute, early head script and
 * navbar toggle) and the logos configured in the theme settings.
 *
 * @package    theme_goias
 * @copyright  2026 Elias Fonseca, Cesar Augusto
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {
    /**
     * Adds the colour scheme attributes to the <html> tag.
     *
     * @return string
     */
    public function htmlattributes() {
        $attributes = parent::htmlattributes();
        $scheme = colorscheme::get_current();
        $attributes .= ' data-bs-theme="' . colorscheme::get_rendered($scheme) . '"';
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
     * Prepends a tiny blocking script that resolves "auto" before the CSS loads (avoids a flash).
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
            . colorscheme::PREFERENCE . "');if(l==='light'||l==='dark'||l==='auto'){s=l;d.setAttribute('data-goias-scheme',l);}}}"
            . "catch(e){}if(s==='auto'){s=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches"
            . "?'dark':'light';}d.setAttribute('data-bs-theme',s==='dark'?'dark':'light');})();";
        return \html_writer::tag('script', $script) . "\n" . $output;
    }

    /**
     * Adds the colour scheme toggle to the navbar (before notifications and messages).
     *
     * @return string
     */
    public function navbar_plugin_output() {
        $output = parent::navbar_plugin_output();
        if (!colorscheme::is_enabled()) {
            return $output;
        }

        $this->page->requires->js_call_amd('theme_goias/colorscheme', 'init', [colorscheme::can_store_preference()]);
        $toggle = $this->render_from_template('theme_goias/colorscheme_toggle', []);

        return $toggle . $output;
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
