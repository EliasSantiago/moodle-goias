# Goiás theme for Moodle

A clean, modern theme based on **Boost**. It brings a graphite palette, the
**Plus Jakarta Sans** typeface, rounded cards with soft shadows, a translucent
navigation bar and **light/dark colour schemes**.

![Course page](pix/screenshot.png)

## Features

- **Light and dark mode**: a button in the navigation bar switches the scheme.
  The choice is saved as a user preference (in the browser for guests). By
  default the theme follows the operating system setting.
- **Accent colour**: graphite by default; any colour can be set and the theme
  derives hover, active and dark-mode shades from it.
- **Logos**: logo and compact logo uploaded in the theme settings (they override
  the site logos from *Appearance > Logos*).
- **Login page**: split screen with a gradient panel, logo, configurable title
  and text.
- **Self-hosted font**: no request is sent to external font services.
- Raw SCSS (before and after) for further customisation.

## Requirements

- Moodle 5.2 or later (5.2 branch).
- Boost theme (shipped with Moodle).

## Installation

1. Copy this folder to `public/theme/goias` (or install the ZIP from
   *Site administration > Plugins > Install plugins*).
2. Complete the upgrade at *Site administration > Notifications*.
3. Select the theme at *Site administration > Appearance > Themes*.

## Settings

*Site administration > Appearance > Themes > Goiás*

| Tab | Setting | Description |
| --- | --- | --- |
| General | Accent colour | Buttons, links, active navigation, login panel. |
| General | Logo / Compact logo | Login page and navigation bar. |
| Colour scheme | Allow dark mode | Shows the light/dark toggle. |
| Colour scheme | Default colour scheme | Follow the device, light or dark. |
| Login page | Title / Text | Copy of the login side panel. |
| Advanced | Raw SCSS | Extra SCSS before/after the theme styles. |

## Support

- Source code: https://github.com/EliasSantiago/moodle-goias
- Bug tracker: https://github.com/EliasSantiago/moodle-goias/issues

## Privacy

The theme stores one user preference, `theme_goias_colorscheme` (light, dark
or auto). It is described and exported by the Privacy API provider.

## License

GNU GPL v3 or later. See [LICENSE](LICENSE).

Plus Jakarta Sans is licensed under the SIL Open Font License 1.1
([fonts/OFL.txt](fonts/OFL.txt)).

## Authors

Elias Fonseca, Cesar Augusto, 2026.
