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
		$query->order('season DESC');
        $query->group('season');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		/** Merge any additional options in the XML definition. */
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}


}
