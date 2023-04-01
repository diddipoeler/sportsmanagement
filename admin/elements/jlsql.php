<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage elements
 * @file       jlsql.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

// Welche version
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	HTMLHelper::_('behavior.framework', true);
}
else
{
	HTMLHelper::_('behavior.mootools');
}


/**
 * JFormFieldJLSQL
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldJLSQL extends JFormField
{
	/**
	 * Element name
	 *
	 * @accessprotected
	 * @varstring
	 */
	protected $type = 'JLSQL';

	/**
	 * JFormFieldJLSQL::getInput()
	 *
	 * @return
	 */
	function getInput()
	{
		$db = sportsmanagementHelper::getDBConnection();
		$db->setQuery($this->elements['query']);
		$key     = ($this->elements['key_field'] ? $this->elements['key_field'] : 'value');
		$val     = ($this->elements['value_field'] ? $this->elements['value_field'] : $this->name);
		$doc     = Factory::getDocument();
		$updates = $this->elements['updates'];
		$depends = $this->elements['depends'];

		if ($updates)
		{
			$view = $this->elements['rawview'];
			$doc->addScriptDeclaration(
				"function update_" . $updates . "()
			{
				$('" . $control_name . $updates . "').onclick = function () { return false;};
				var combo = $('" . $control_name . $this->name . "');
				var value = combo.options[combo.selectedIndex].value;
				var postStr  = '';
				var url = '" . JURI::base() . "' + 'index.php?option=com_sportsmanagement&view=" . $view . "&format=raw&" . $this->name . "='+value;
				var theAjax = new Ajax(url, {
					method: 'post',
					postBody : postStr
					});
				theAjax.addEvent('onSuccess', function(html) {
					var comboToUpdate = $('" . $control_name . $updates . "');
					var previousValue = comboToUpdate.selectedIndex>0 ? comboToUpdate.options[comboToUpdate.selectedIndex].value : -1;
					var msie = navigator.userAgent.toLowerCase().match(/msie\s+(\d)/);
					if(msie) {
						comboToUpdate.empty();
						comboToUpdate.outerHTML='<SELECT id=\"" . $control_name . $updates . "\" name=\"" . $control_name . "[" . $updates . "]\">'+html+'</SELECT>';
					}
					else {
						comboToUpdate.empty().setHTML(html);
					}
					if(previousValue!=-1){
						for (var i=0; i<comboToUpdate.options.length;i++) {
			 				if (comboToUpdate.options[i].value==previousValue) {
								comboToUpdate.selectedIndex = i;
								break;
			 				}
		  				}
	  				}
				});
				theAjax.request();
			}"
			);
		}

		$html = HTMLHelper::_('select.genericlist', $db->loadObjectList(), $this->name, 'class="inputbox"' . ($updates ? ' onchange="javascript:update_' . $updates . '()"' : '') . ($depends ? ' onclick="javascript:update_' . $this->name . '()"' : ''), $key, $val, $this->value, $this->name);

		return $html;
	}
}
