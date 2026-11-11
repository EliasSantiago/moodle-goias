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

namespace theme_goias;

use theme_goias\local\colorscheme;

/**
 * Tests for the colour scheme resolution.
 *
 * @package    theme_goias
 * @category   test
 * @copyright  2026 Elias Fonseca, Cesar Augusto
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \theme_goias\local\colorscheme
 */
final class colorscheme_test extends \advanced_testcase {
    /**
     * Guests and users without preference get the site default.
     */
    public function test_default_scheme(): void {
        $this->resetAfterTest();

        set_config('defaultscheme', 'dark', 'theme_goias');
        $this->assertSame(colorscheme::DARK, colorscheme::get_current());

        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertSame(colorscheme::DARK, colorscheme::get_current());
    }

    /**
     * The user preference wins over the site default; invalid values are ignored.
     */
    public function test_user_preference(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());
        set_config('defaultscheme', 'auto', 'theme_goias');

        set_user_preference(colorscheme::PREFERENCE, 'light');
        $this->assertSame(colorscheme::LIGHT, colorscheme::get_current());

        set_user_preference(colorscheme::PREFERENCE, 'purple');
        $this->assertSame(colorscheme::AUTO, colorscheme::get_current());
    }

    /**
     * Disabling dark mode forces the light scheme.
     */
    public function test_dark_mode_disabled(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());
        set_user_preference(colorscheme::PREFERENCE, 'dark');

        set_config('darkmode', 0, 'theme_goias');
        $this->assertFalse(colorscheme::is_enabled());
        $this->assertSame(colorscheme::LIGHT, colorscheme::get_current());
    }

    /**
     * Only "dark" is rendered as dark on the server ("auto" is resolved in the browser).
     */
    public function test_rendered_value(): void {
        $this->assertSame('dark', colorscheme::get_rendered(colorscheme::DARK));
        $this->assertSame('light', colorscheme::get_rendered(colorscheme::LIGHT));
        $this->assertSame('light', colorscheme::get_rendered(colorscheme::AUTO));
    }

    /**
     * Accent colour validation.
     */
    public function test_clean_colour(): void {
        global $CFG;
        require_once($CFG->dirroot . '/theme/goias/lib.php');

        $this->assertSame('#27272a', theme_goias_clean_colour(' #27272a '));
        $this->assertSame('#fff', theme_goias_clean_colour('#fff'));
        $this->assertSame('', theme_goias_clean_colour('red; } body { display:none'));
    }
}
