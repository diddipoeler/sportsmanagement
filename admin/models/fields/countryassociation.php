<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');

/**
 * Session form field class
 */
class JFormFieldcountryassociation extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'countryassociation';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
    //echo 'this->element<br /><pre>~' . print_r($this->element,true) . '~</pre><br />';
		$varname = (string) $this->element['varname'];
    $vartable = (string) $this->element['targettable'];
		$select_id = JRequest::getVar($varname);
 		if (is_array($select_id)) {
 			$select_id = $select_id[0];
 		}
		
		if ($select_id)
		{		
			$db = &JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('t.id AS value, t.name AS text');
			$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_associations AS t');
 			$query->join('inner', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_'.$vartable.' AS wt ON wt.country = t.country ');
			$query->where('wt.id = '.$select_id);
			$query->order('t.name');
			$db->setQuery($query);
			$options = $db->loadObjectList();
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
