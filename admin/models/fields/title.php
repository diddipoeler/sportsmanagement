<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       title.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */


defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
jimport('joomla.form.formfield');

/**
 * FormFieldTitle
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldTitle extends FormField
{
	public $type = 'Title';

	/**
	 * FormFieldTitle::getLabel()
	 *
	 * @return
	 */
	protected function getLabel()
	{
		$value = trim($this->element['title']);
		$image_src = $this->element['imagesrc']; // Path ex: ../modules/mod_latestnews/images/icon.png (16x16)
		$icon = $this->element['icon'];

		$color = $this->element['color'];

		if (empty($color))
		{
			$color = '#e65100';
		}

		$html = '</div>';

		// #bdbdbd
		$inline_style = 'background: ' . $color . '; ';
		$inline_style .= 'background: linear-gradient(to right, ' . $color . ' 0%, #fff 100%); ';
		$inline_style .= 'color: #fff; ';
		$inline_style .= 'border-radius: 3px; ';
		$inline_style .= 'font-family: "Courier New", Courier, monospace; ';
		$inline_style .= 'margin: 5px 0; ';
		$inline_style .= 'text-transform: uppercase; ';
		$inline_style .= 'letter-spacing: 3px; ';
		$inline_style .= 'font-weight: bold; ';
		$inline_style .= 'padding: 5px 5px 5px 10px; ';

		$html .= '<div style=\'' . $inline_style . '\'>';

		if ($image_src)
		{
			$html .= '<img style="margin: -1px 4px 0 0; float: left; padding: 0px; width: 16px; height: 16px" src="' . $image_src . '">';
		}
		elseif ($icon)
		{
			HTMLHelper::_('stylesheet', 'syw/fonts-min.css', false, true);
			$html .= '<i style="font-size: inherit; vertical-align: baseline" class="SYWicon-' . $icon . '">&nbsp;</i>';
		}

		if ($value)
		{
			$html .= Text::_($value);
		}

		return $html;
	}

	/**
	 * FormFieldTitle::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		return '';
	}

}
