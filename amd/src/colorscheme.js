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
 * Colour scheme selector for theme_goias (light, dark, high contrast, follow the device).
 *
 * @module     theme_goias/colorscheme
 * @copyright  2026 Elias Fonseca, Cesar Augusto
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {setUserPreference} from 'core_user/repository';
import Notification from 'core/notification';

const PREFERENCE = 'theme_goias_colorscheme';
const SCHEMES = ['light', 'dark', 'contrast', 'auto'];
const SELECTOR = '[data-goias-scheme-option]';

let initialised = false;

/**
 * Applies a scheme to the document.
 *
 * @param {string} scheme light|dark|contrast|auto
 */
const apply = (scheme) => {
    const root = document.documentElement;
    let resolved = scheme;
    if (scheme === 'auto') {
        resolved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    root.setAttribute('data-goias-scheme', scheme);
    root.setAttribute('data-goias-theme', resolved);
    root.setAttribute('data-bs-theme', resolved === 'light' ? 'light' : 'dark');
    document.querySelectorAll(SELECTOR).forEach((option) => {
        option.setAttribute('aria-checked', option.dataset.goiasSchemeOption === scheme ? 'true' : 'false');
    });
};

/**
 * Persists the scheme (user preference for logged in users, browser storage for everyone).
 *
 * @param {string} scheme
 * @param {boolean} storeOnServer
 */
const persist = (scheme, storeOnServer) => {
    try {
        window.localStorage.setItem(PREFERENCE, scheme);
    } catch (e) {
        // Storage may be blocked (private mode); the choice still applies to this page.
    }
    if (storeOnServer) {
        setUserPreference(PREFERENCE, scheme).catch(Notification.exception);
    }
};

/**
 * Initialises the selector.
 *
 * @param {boolean} storeOnServer Whether the user preference can be saved on the server.
 */
export const init = (storeOnServer) => {
    if (initialised) {
        return;
    }
    initialised = true;

    const root = document.documentElement;
    apply(root.getAttribute('data-goias-scheme') || 'auto');

    document.addEventListener('click', (e) => {
        const option = e.target.closest(SELECTOR);
        if (!option || !SCHEMES.includes(option.dataset.goiasSchemeOption)) {
            return;
        }
        e.preventDefault();
        apply(option.dataset.goiasSchemeOption);
        persist(option.dataset.goiasSchemeOption, storeOnServer);
    });

    // Follow the operating system while the scheme is "auto".
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (root.getAttribute('data-goias-scheme') === 'auto') {
            apply('auto');
        }
    });
};
