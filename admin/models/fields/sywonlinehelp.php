<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       sywonlinehelp.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */


defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormField;

/**
 * FormFieldSYWOnlineHelp
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class JFormFieldSYWOnlineHelp extends FormField
{
	protected $type = 'SYWOnlineHelp';

	/**
	 * FormFieldSYWOnlineHelp::getLabel()
	 *
	 * @return
	 */
	protected function getLabel()
	{

		HTMLHelper::_('stylesheet', 'syw/fonts-min.css', false, true);

		$title = $this->element['label'] ? (string) $this->element['label'] : ($this->element['title'] ? (string) $this->element['title'] : '');
		$heading = $this->element['heading'] ? (string) $this->element['heading'] : 'h4';
		$description = (string) $this->element['description'];
		$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';

		$url = (string) $this->element['url'];

		$html = array();

		$html[] = !empty($title) ? '<' . $heading . '>' . Text::_($title) . '</' . $heading . '>' : '';

		$html[] = '<table style="width: 100%"><tr>';
		$html[] = !empty($description) ? '<td>' . Text::_($description) . '</td>' : '';
		$html[] = '<td style="text-align: right"><a href="' . $url . '" target="_blank" class="btn btn-info btn-mini btn-xs"><i class="SYWicon-local-library"></i></a></td>';
		$html[] = '</tr></table>';

		return '</div><div ' . $class . '>' . implode('', $html);
	}

	/**
	 * FormFieldSYWOnlineHelp::getInput()
	 *
	 * @return
	 */
	protected function getInput()
	{
		return '';
	}

}
