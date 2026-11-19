<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   theme_mb2nl
 * @copyright 2017 - 2025 Mariusz Boloz (lmsstyle.com)
 * @license   PHP and HTML: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later. Other parts: http://themeforest.net/licenses
 *
 */


/**
 *
 * Method to get theme scripts
 *
 */
function theme_mb2nl_scripts() {
    global $PAGE;

    // Tb = 1 -> in the head tag. This is required on the builder editingpage.
    // Tb = 0 -> at the end of the body tag.
    $tb = preg_match('@mb2builder-customize@', $PAGE->pagetype) ? 1 : 0;
    $themedir = theme_mb2nl_themedir();

    // theme_mb2nl_theme_setting() treats '0' as "not set", so read the value
    // directly to allow explicitly disabling an optional library.
    $libraryenabled = function($name) use ($PAGE) {
        $value = isset($PAGE->theme->settings->$name) ? $PAGE->theme->settings->$name : get_config('theme_mb2nl', $name);
        return $value !== '0' && $value !== 0;
    };

    $PAGE->requires->jquery();

    // We need these scripts on all pages.
    $PAGE->requires->js($themedir . '/mb2nl/script/mb2nl_helper.js', $tb);
    $PAGE->requires->js($themedir . '/mb2nl/script/inview/inview.js', $tb);

    // Optional builder libraries. Defaults are 1 so existing sites keep
    // working; switch them off when their builder elements are unused.
    if ($libraryenabled('ljs_swiper')) {
        $PAGE->requires->js($themedir . '/mb2nl/script/swiper/swiper.js', $tb);
    }
    if ($libraryenabled('ljs_jarallax')) {
        $PAGE->requires->js($themedir . '/mb2nl/script/jarallax/jarallax.js', $tb);
    }
    $PAGE->requires->js($themedir . '/mb2nl/script/magnific-popup/magnific-popup.js', $tb);
    if ($libraryenabled('ljs_typed')) {
        $PAGE->requires->js($themedir . '/mb2nl/script/typed/typed.js', $tb);
    }

    // Main slider script.
    // Load it only if the front page slider is enabled.
    if (theme_mb2nl_theme_setting($PAGE, 'slider')) {
        $PAGE->requires->js($themedir . '/mb2nl/script/lightslider/lightslider.js', $tb);
    }

    // jQuery UI and Spectrum are only needed by the builder and the theme
    // settings forms, not by every page visited by an administrator.
    $needs_colorpicker = $tb || preg_match('@^admin-setting-(themesettingmb2nl|theme_mb2nl)@', $PAGE->pagetype);
    if ($needs_colorpicker) {
        $PAGE->requires->jquery_plugin('ui'); // This is require for page builder,
        // this is required twice (here and in the builder lib.php file).
        $PAGE->requires->js($themedir . '/mb2nl/script/spectrum/spectrum.js', $tb);
        $PAGE->requires->css($themedir . '/mb2nl/script/spectrum/spectrum.css');
    }

    // Scripts for all pages EXCLUDING page builder.
    // We don't need layzyload and plugin init scipts on the page builder editing page.
    // For each plugin page builder loads own init script.
    if ($PAGE->pagelayout !== 'mb2builder') {
        $PAGE->requires->js($themedir . '/mb2nl/script/lazyload/lazyload.js', $tb);
        $PAGE->requires->js($themedir . '/mb2nl/script/mb2nl_plugins.js', $tb);
    }

    $PAGE->requires->js($themedir . '/mb2nl/script/mb2nl.js', $tb);

}
