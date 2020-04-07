<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       languagecharacter.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldlanguagecharacter
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class JFormFieldlanguagecharacter extends \JFormFieldList
{
	/**
	 * field type
	 *
	 * @var string
	 */
	public $type = 'languagecharacter';

	/**
	 * Method to get the field options.
	 *
	 * @return array  The field option objects.
	 *
	 * @since 11.1
	 */
	protected function getOptions()
	{
		$app = Factory::getApplication();
		$option = $app->input->getCmd('option');
		$lang = Factory::getLanguage();
		$options = array();
		$character = array();
		$languages = $lang->getTag();

		switch ($languages)
		{
			case 'ru-RU':
				$character[] = "0410";
				$character[] = "042F";
			break;
			case 'el-GR':
				$character[] = "0391";
				$character[] = "03A9";
			break;
			default:
				$character[] = "0041";
				$character[] = "005A";
			break;
		}

		// $laenge = sizeof($character) - 1;
		$startRange = hexdec($character[0]);
		$endRange = hexdec($character[1]);

		for ($i = $startRange; $i <= $endRange; $i++)
		{
			$options[] = HTMLHelper::_('select.option', $i, '&#' . $i . ';', 'value', 'text');
		}

				 // Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
