<?php
/**
 * New Template System
 *
 * @author	Bastian Krones
 * @date 03.01.2017
 */

require_once '/home/elearning-www/public_html/elearning/ilias-5.1/Customizing/global/include/fpraktikum/new/templates/template.class.php';

$tpl = new Template();

/**
 * Loads template-mask "registration" of Fpraktikum
 */
$tpl->load("registerMask.tpl");

/**
 * Specify language of FPraktikum's template: 'de','en' 
 */
$lang = $tpl->loadLanguage("en");

/**
 * Assigning placeholders {%PLACEHOLDER} using language variables
 * Placesholders are set by '{%VARIABLE}'
 * Language variables are defined in Languages/{language}.php files
 */
$tpl->assign("test", $lang["test"]);

/**
 * Displays the template
 */
$html = $tpl->display();