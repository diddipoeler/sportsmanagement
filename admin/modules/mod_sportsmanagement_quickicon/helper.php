<?php


defined('_JEXEC') or die;


abstract class ModSportsmanagementQuickIconHelper
{
	/**
	 * Stack to hold buttons
	 *
	 * @since   1.6
	 */
	protected static $buttons = array();

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
