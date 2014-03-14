<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html.menu');
jimport('joomla.form.formfield');

class JFormFieldTemplates extends JFormFieldList
{
	public $type = 'Templates';
		
	protected function getOptions()
	{
		$options = array();
		
		$client = JApplicationHelper::getClientInfo('site', true);
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		$query->select('s.id, s.title, e.name as name, s.template');
		$query->from('#__template_styles as s');
		$query->where('s.client_id = ' . (int) $client->id);
		$query->order('template');
		$query->order('title');
		$query->join('LEFT', '#__extensions as e on e.element=s.template');
		$query->where('e.enabled=1');
		$query->where($db->quoteName('e.type') . '=' . $db->quote('template'));
	
		$db->setQuery($query);
	
		if ($error = $db->getErrorMsg()) {
			throw new Exception($error);
		} 
		
		$templates = $db->loadObjectList();			
	
		foreach ($templates as $item) {
			$options[] = JHTML::_('select.option', $item->id, $item->title);
		}	

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
