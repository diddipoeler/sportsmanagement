<?php
/**
 * @copyright	Copyright (C) 2005-2013 fussballineuropa.de. All rights reserved.
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
class JFormFieldStatstypelist extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'statstypelist';

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

		// Initialize some field attributes.
		//$filter = (string) $this->element['filter'];
		//$exclude = (string) $this->element['exclude'];
		//$hideNone = (string) $this->element['hide_none'];
		//$hideDefault = (string) $this->element['hide_default'];

		// Get the path in which to search for file options.
		$files = JFolder::files(JPATH_COMPONENT_ADMINISTRATOR.DS.'statistics', 'php$');
		$options = array();
		foreach ($files as $file)
		{
			$parts = explode('.', $file);
			if ($parts[0] != 'base') {
				$options[] = JHTML::_('select.option', $parts[0], $parts[0]);
			}
		}
		
		/*
		// check for statistic in extensions
		$extensions = sportsmanagementHelper::getExtensions(0);		
		foreach ($extensions as $type)
		{
			$path = JLG_PATH_SITE.DS.'extensions'.DS.$type.DS.'admin'.DS.'statistics';
			if (!file_exists($path)) {
				continue;
			}
			$files = JFolder::files($path, 'php$');
			foreach ($files as $file)
			{
				$parts = explode('.', $file);
				if ($parts[0] != 'base') {
					$options[] = JHTML::_('select.option', $parts[0], $parts[0]);
				}
			}	
		}
		*/
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
