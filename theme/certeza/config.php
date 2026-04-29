<?php
defined('MOODLE_INTERNAL') || die();

$THEME->name             = 'certeza';
$THEME->parents          = ['boost'];
$THEME->sheets           = [];
$THEME->usefallback      = true;
$THEME->rendererfactory  = 'theme_overridden_renderer_factory';

$THEME->scss = function($theme) {
    return theme_certeza_get_main_scss_content($theme);
};
$THEME->prescsscallback   = 'theme_certeza_get_pre_scss';
$THEME->extrascsscallback = 'theme_certeza_get_extra_scss';
