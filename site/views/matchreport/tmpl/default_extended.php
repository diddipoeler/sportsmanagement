<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_extended.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
?>
<!-- EXTENDED DATA-->
<?php
if(count($this->extended->getFieldsets()) > 0)
{
	/** fieldset->name is set in the backend and is localized, so we need the backend language file here */
	Factory::getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR);
	
	foreach ($this->extended->getFieldsets() as $fieldset)
	{
		$fields = $this->extended->getFieldset($fieldset->name);
		if (count($fields) > 0)
		{
			/** Check if the extended data contains information */
			$hasData = false;
			foreach ($fields as $field)
			{
				// TODO: backendonly was a feature of JLGExtraParams, and is not yet available.
				//       (this functionality probably has to be added later)
				$value = $field->value;	// Remark: empty($field->value) does not work, using an extra local var does
				if (!empty($value)) // && !$field->backendonly
				{
					$hasData = true;
					break;
				}
			}
			/** And if so, display this information */
			if ($hasData)
			{
				?>
				<h2><?php echo '&nbsp;' . Text::_($fieldset->name); ?></h2>
				<table class="table table-responsive" >
					<tbody>
				<?php
				foreach ($fields as $field)
				{
					$value = $field->value;
					if (!empty($value)) // && !$field->backendonly)
					{
						?>
						<tr>
							<td class="label"><?php echo $field->label; ?></td>
							<td class="data"><?php 
						
						
switch ($field->value)
{
case 'foggy':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_FOGGY');		
break;
case 'foggy':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_FOGGY');		
break;
case 'rainy':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_RAINY');		
break;
case 'sunny':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_SUNNY');		
break;
case 'windy':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_WINDY');		
break;
case 'dry':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_DRY');		
break;
case 'snowing':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_SNOWING');		
break;
case 'normal':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_NORMAL');		
break;
case 'wet':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_WET');		
break;
case 'fielddry':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_DRY');		
break;
case 'snow':
echo Text::_('COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_SNOW');		
break;		
}
?>                 
                                               </td>
						<tr>
						<?php
					}
				}
				?>
					</tbody>
				</table>
				<br/>
				<?php
			}
		}
	}
}
?>	
