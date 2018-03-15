<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die;


/**
 * ModSportsmanagementQuickIconHelper
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
abstract class ModSportsmanagementQuickIconHelper
{
	/**
	 * Stack to hold buttons
	 *
	 * @since   1.6
	 */
	protected static $buttons = array();
    
    
    /**
     * ModSportsmanagementQuickIconHelper::getModPosition()
     * 
     * @return void
     */
    public static function getModPosition()
	{
	$query = JFactory::getDBO()->getQuery(true);
    $query->select('position');
    $query->from('#__modules');
    $query->where('module LIKE '.JFactory::getDbo()->Quote(''.'mod_sportsmanagement_quickicon'.'') );
        
	JFactory::getDBO()->setQuery($query);
	$res = JFactory::getDBO()->loadResult();
    
    return $res;   
    }   

	/**
	 * Helper method to return button list.
	 *
	 * This method returns the array by reference so it can be
	 * used to add custom buttons or remove default ones.
	 *
	 * @param   JObject  $params  The module parameters.
	 *
	 * @return  array  An array of buttons
	 *
	 * @since   1.6
	 */
	public static function &getButtons($params)
	{
		$key = (string) $params;

		if (!isset(self::$buttons[$key]))
		{
			$context = $params->get('context', 'mod_sportsmanagement_quickicon');

			if ($context == 'mod_sportsmanagement_quickicon')
			{
				// Load mod_quickicon language file in case this method is called before rendering the module
				JFactory::getLanguage()->load('mod_sportsmanagement_quickicon');

				self::$buttons[$key] = array(
					array(
						'link' => JRoute::_('index.php?option=com_sportsmanagement'),
						'image' => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
						'icon' => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
						'text' => JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LINK'),
						'access' => array('core.manage', 'com_sportsmanagement'),
						'group' => 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'
						),
                        
                        array(
						'link' => JRoute::_('index.php?option=com_sportsmanagement&view=extensions'),
						'image' => 'pencil-2',
						'icon' => '/components/com_sportsmanagement/assets/icons/extensions.png',
						'text' => JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LINK'),
						'access' => array('core.manage', 'com_sportsmanagement'),
						'group' => 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'
						),
                        
                        array(
						'link' => JRoute::_('index.php?option=com_sportsmanagement&view=projects'),
						'image' => 'pencil-2',
						'icon' => '/components/com_sportsmanagement/assets/icons/projekte.png',
						'text' => JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LINK'),
						'access' => array('core.manage', 'com_sportsmanagement'),
						'group' => 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'
						),
                        
                        array(
						'link' => JRoute::_('index.php?option=com_sportsmanagement&view=predictions'),
						'image' => 'pencil-2',
						'icon' => '/components/com_sportsmanagement/assets/icons/tippspiele.png',
						'text' => JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LINK'),
						'access' => array('core.manage', 'com_sportsmanagement'),
						'group' => 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'
						),
                        
                        array(
						'link' => JRoute::_('index.php?option=com_sportsmanagement&view=currentseasons'),
						'image' => 'pencil-2',
						'icon' => '/components/com_sportsmanagement/assets/icons/aktuellesaison.png',
						'text' => JText::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LINK'),
						'access' => array('core.manage', 'com_sportsmanagement'),
						'group' => 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'
						)
				);
			}
			else
			{
				self::$buttons[$key] = array();
			}

			// Include buttons defined by published quickicon plugins
			JPluginHelper::importPlugin('quickicon');
			$app = JFactory::getApplication();
			$arrays = (array) $app->triggerEvent('onGetIcons', array($context));

			foreach ($arrays as $response)
			{
				foreach ($response as $icon)
				{
					$default = array(
						'link' => null,
						'image' => 'cog',
						'text' => null,
						'access' => true,
						'group' => 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'
					);
					$icon = array_merge($default, $icon);

					if (!is_null($icon['link']) && !is_null($icon['text']))
					{
						self::$buttons[$key][] = $icon;
					}
				}
			}
		}

		return self::$buttons[$key];
	}

	/**
	 * Classifies the $buttons by group
	 *
	 * @param   array  $buttons  The buttons
	 *
	 * @return  array  The buttons sorted by groups
	 *
	 * @since   3.2
	 */
	public static function groupButtons($buttons)
	{
		$groupedButtons = array();

		foreach ($buttons as $button)
		{
			$groupedButtons[$button['group']][] = $button;
		}
		return $groupedButtons;
	}

	/**
	 * Get the alternate title for the module
	 *
	 * @param   JObject  $params  The module parameters.
	 * @param   JObject  $module  The module.
	 *
	 * @return  string	The alternate title for the module.
	 */
	public static function getTitle($params, $module)
	{
		$key = $params->get('context', 'mod_sportsmanagement_quickicon') . '_title';

		if (JFactory::getLanguage()->hasKey($key))
		{
			return JText::_($key);
		}
		else
		{
			return $module->title;
		}
	}
}
