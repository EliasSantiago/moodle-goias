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
 * Light/dark colour scheme toggle for theme_goias.
 *
 * @module     theme_goias/colorscheme
 * @copyright  2026 Elias Fonseca, Cesar Augusto
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {setUserPreference} from 'core_user/repository';
import Notification from 'core/notification';

const PREFERENCE = 'theme_goias_colorscheme';
const SELECTOR = '[data-action="theme_goias/toggle-colorscheme"]';

let initialised = false;

/**
 * Applies a scheme to the document.
 *
 * @param {string} scheme light|dark|auto
 */
const apply = (scheme) => {
    const root = document.documentElement;
    let resolved = scheme;
    if (scheme === 'auto') {
        resolved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    root.setAttribute('data-goias-scheme', scheme);
    root.setAttribute('data-bs-theme', resolved === 'dark' ? 'dark' : 'light');
    document.querySelectorAll(SELECTOR).forEach((button) => {
        button.setAttribute('aria-pressed', resolved === 'dark' ? 'true' : 'false');
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
 * Initialises the toggle.
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
        if (!e.target.closest(SELECTOR)) {
            return;
        }
        e.preventDefault();
        const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        apply(next);
        persist(next, storeOnServer);
    });

    // Follow the operating system while the scheme is "auto".
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (root.getAttribute('data-goias-scheme') === 'auto') {
            apply('auto');
        }
    });
};
