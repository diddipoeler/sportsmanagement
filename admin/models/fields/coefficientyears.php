<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage fields
 * @file       coefficientyears.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');

/**
 * JFormFieldcoefficientyears
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2022
 * @version $Id$
 * @access public
 */
class JFormFieldcoefficientyears extends \JFormFieldList
{
	protected $type = 'coefficientyears';

	/**
	 * FormFieldcoefficientyears::getOptions()
	 *
	 * @return
	 */
	protected function getOptions()
	{
		$options = array();
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('season AS value, season AS text');
		$query->from('#__sportsmanagement_uefawertung');
        $query->group('season');
		$query->order('season DESC');
        
        try
		{
		$db->setQuery($query);
		$options = $db->loadObjectList();

		/** Merge any additional options in the XML definition. */
		$options = array_merge(parent::getOptions(), $options);
        }
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
		
		}

		return $options;
	}


}
