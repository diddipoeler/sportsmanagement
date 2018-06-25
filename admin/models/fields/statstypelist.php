<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');


/**
 * JFormFieldStatstypelist
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
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
				$options[] = JHtml::_('select.option', $parts[0], $parts[0]);
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
					$options[] = JHtml::_('select.option', $parts[0], $parts[0]);
				}
			}	
		}
		*/
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
