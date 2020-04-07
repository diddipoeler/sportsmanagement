<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_quickicon
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * ModSportsmanagementQuickIconHelper
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
abstract class ModSportsmanagementQuickIconHelper
{
	/**
	 * Stack to hold buttons
	 *
	 * @since 1.6
	 */
	protected static $buttons = array();


	/**
	 * ModSportsmanagementQuickIconHelper::getModPosition()
	 *
	 * @return void
	 */
	public static function getModPosition()
	{
		$query = Factory::getDBO()->getQuery(true);
		$query->select('position');
		$query->from('#__modules');
		$query->where('module LIKE ' . Factory::getDbo()->Quote('' . 'mod_sportsmanagement_quickicon' . ''));

			  Factory::getDBO()->setQuery($query);
		$res = Factory::getDBO()->loadResult();

		return $res;
	}

	/**
	 * Helper method to return button list.
	 *
	 * This method returns the array by reference so it can be
	 * used to add custom buttons or remove default ones.
	 *
	 * @param   JObject $params The module parameters.
	 *
	 * @return array  An array of buttons
	 *
	 * @since 1.6
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
				Factory::getLanguage()->load('mod_sportsmanagement_quickicon');

				self::$buttons[$key] = array(
				 array(
				  'link' => Route::_('index.php?option=com_sportsmanagement'),
				  'image' => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
				  'icon' => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
				  'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LINK'),
				  'access' => array('core.manage', 'com_sportsmanagement'),
				  'group' => 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'
				  ),

											  array(
				  'link' => Route::_('index.php?option=com_sportsmanagement&view=extensions'),
				  'image' => 'pencil-2',
				  'icon' => '/components/com_sportsmanagement/assets/icons/extensions.png',
				  'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LINK'),
				  'access' => array('core.manage', 'com_sportsmanagement'),
				  'group' => 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'
				  ),

											  array(
				  'link' => Route::_('index.php?option=com_sportsmanagement&view=projects'),
				  'image' => 'pencil-2',
				  'icon' => '/components/com_sportsmanagement/assets/icons/projekte.png',
				  'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LINK'),
				  'access' => array('core.manage', 'com_sportsmanagement'),
				  'group' => 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'
				  ),

											  array(
				  'link' => Route::_('index.php?option=com_sportsmanagement&view=predictions'),
				  'image' => 'pencil-2',
				  'icon' => '/components/com_sportsmanagement/assets/icons/tippspiele.png',
				  'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LINK'),
				  'access' => array('core.manage', 'com_sportsmanagement'),
				  'group' => 'MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'
				  ),

											  array(
				  'link' => Route::_('index.php?option=com_sportsmanagement&view=currentseasons'),
				  'image' => 'pencil-2',
				  'icon' => '/components/com_sportsmanagement/assets/icons/aktuellesaison.png',
				  'text' => Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LINK'),
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
			PluginHelper::importPlugin('quickicon');
			$app = Factory::getApplication();
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
	 * @param   array $buttons The buttons
	 *
	 * @return array  The buttons sorted by groups
	 *
	 * @since 3.2
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
	 * @param   JObject $params The module parameters.
	 * @param   JObject $module The module.
	 *
	 * @return string    The alternate title for the module.
	 */
	public static function getTitle($params, $module)
	{
		$key = $params->get('context', 'mod_sportsmanagement_quickicon') . '_title';

		if (Factory::getLanguage()->hasKey($key))
		{
			return Text::_($key);
		}
		else
		{
			return $module->title;
		}
	}
}
