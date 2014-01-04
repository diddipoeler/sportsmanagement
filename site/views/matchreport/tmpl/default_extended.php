<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
<!-- EXTENDED DATA-->
<?php
if(count($this->extended->getFieldsets()) > 0)
{
	// fieldset->name is set in the backend and is localized, so we need the backend language file here
	JFactory::getLanguage()->load('com_joomleague', JPATH_ADMINISTRATOR);
	
	foreach ($this->extended->getFieldsets() as $fieldset)
	{
		$fields = $this->extended->getFieldset($fieldset->name);
		if (count($fields) > 0)
		{
			// Check if the extended data contains information 
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
			// And if so, display this information
			if ($hasData)
			{
				?>
				<h2><?php echo '&nbsp;' . JText::_($fieldset->name); ?></h2>
				<table>
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
							<td class="data"><?php if ($field->value =="foggy") {echo JText::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_FOGGY');}
                                                                                 else { if ($field->value =="rainy") {echo JText::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_RAINY');}
                                   							else { if ($field->value =="sunny") {echo JText::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_SUNNY');}
                										else { if ($field->value =="windy") {echo JText::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_WINDY');}
                											else { if ($field->value =="dry") {echo JText::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_DRY');}
                  												else { if ($field->value =="snowing") {echo JText::_('COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_SNOWING');}
                   													else { if ($field->value =="normal") {echo JText::_('COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_NORMAL');}
                    														else { if ($field->value =="wet") {echo JText::_('COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_WET');}
                     															else { if ($field->value =="fielddry") {echo JText::_('COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_DRY');}
                      																else { if ($field->value =="snow") {echo JText::_('COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_SNOW');}
                                               														else { echo $field->value;
                                               				}	}	}	}	}	}	}	}	}	} ?>                 
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
