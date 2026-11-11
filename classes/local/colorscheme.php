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

namespace theme_goias\local;

/**
 * Resolves the light/dark colour scheme for the current user.
 *
 * The scheme is stored as a user preference for logged in users and in the
 * browser (localStorage) for guests. "auto" follows the operating system.
 *
 * @package    theme_goias
 * @copyright  2026 Elias Fonseca, Cesar Augusto
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class colorscheme {
    /** @var string User preference name. */
    public const PREFERENCE = 'theme_goias_colorscheme';

    /** @var string Light scheme. */
    public const LIGHT = 'light';

    /** @var string Dark scheme. */
    public const DARK = 'dark';

    /** @var string Follow the operating system. */
    public const AUTO = 'auto';

    /** @var string[] Valid schemes. */
    public const SCHEMES = [self::LIGHT, self::DARK, self::AUTO];

    /**
     * Whether users may switch to the dark scheme.
     *
     * @return bool
     */
    public static function is_enabled(): bool {
        $enabled = get_config('theme_goias', 'darkmode');
        return $enabled === false || (bool) $enabled;
    }

    /**
     * Site default scheme.
     *
     * @return string
     */
    public static function get_default(): string {
        $default = (string) get_config('theme_goias', 'defaultscheme');
        return in_array($default, self::SCHEMES, true) ? $default : self::AUTO;
    }

    /**
     * Whether the current user can store the preference server-side.
     *
     * @return bool
     */
    public static function can_store_preference(): bool {
        return isloggedin() && !isguestuser();
    }

    /**
     * Scheme chosen by the current user (or the site default).
     *
     * @return string One of the SCHEMES constants.
     */
    public static function get_current(): string {
        if (!self::is_enabled()) {
            return self::LIGHT;
        }
        $default = self::get_default();
        if (!self::can_store_preference()) {
            return $default;
        }
        $scheme = (string) get_user_preferences(self::PREFERENCE, $default);
        return in_array($scheme, self::SCHEMES, true) ? $scheme : $default;
    }

    /**
     * Value rendered server-side in data-bs-theme ("auto" is resolved in the browser).
     *
     * @param string $scheme Scheme returned by get_current().
     * @return string light|dark
     */
    public static function get_rendered(string $scheme): string {
        return $scheme === self::DARK ? self::DARK : self::LIGHT;
    }
}
