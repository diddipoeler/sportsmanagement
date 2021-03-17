<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_switcher3.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;


$class   = "btn-group btn-group-yesno";
$html   = array();
$html[] = '<fieldset id="' . $this->switcher_name. '" class="' . $class . '" >';

foreach ($this->switcher_options as $in => $option)
{
$checked = ($option->value == $this->switcher_value) ? ' checked="checked"' : '';
$btn     = ($option->value == $this->switcher_value && $this->switcher_value) ? ' active btn-success' : ' ';
$btn     = ($option->value == $this->switcher_value && !$this->switcher_value) ? ' active btn-danger' : $btn;
$html[]   = '<input type="radio" style="display:none;" id="' . $this->switcher_name . $in . '" name="' . $this->switcher_name . '" value="'
		. $option->value . '"' . $this->switcher_onchange . ' />';
$html[] = '<label for="' . $this->switcher_name . $in . '"' . $checked . ' class="btn' . $btn . '" >'
		. Text::_($option->text) . '</label>';
}

echo implode($html);