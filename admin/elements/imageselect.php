<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       imageselect.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/**
 * JFormFieldImageSelect
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldImageSelect extends JFormField
{
	protected $type = 'imageselect';

	/**
	 * JFormFieldImageSelect::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		$app    = Factory::getApplication();
		$option = Factory::getApplication()->input->getCmd('option');

			  $default = $this->value;
		$arrPathes = explode('/', $default);
		$filename = array_pop($arrPathes);

		// $targetfolder = array_pop($arrPathes);
		$targetfolder = $this->element['targetfolder'];

			  $output  = ImageSelectSM::getSelector($this->name, $this->name . '_preview', $targetfolder, $this->value, $default, $this->name, $this->id);
		$output .= '<img class="imagepreview" src="' . JURI::root(true) . '/media/com_sportsmanagement/jl_images/spinner.gif" ';
		$output .= ' name="' . $this->name . '_preview" id="' . $this->id . '_preview" border="3" alt="Preview" title="Preview" />';
		$output .= '<input type="hidden" id="' . $this->id . '" name="' . $this->name . '" value="' . $this->value . '" />';

		return $output;
	}
}
