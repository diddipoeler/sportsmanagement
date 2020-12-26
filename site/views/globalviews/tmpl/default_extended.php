<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       deafault_extended.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
    <h4>
		<?php
		if ($this->config['show_extended_text'])
		{
			echo Text::_('COM_SPORTSMANAGEMENT_EXT_EXTENDED_PREFERENCES');
		}
		?>
    </h4>

<?php
          
?>

<?php      
if ( $this->extended2 )
{
//echo 'extended2<pre>'.print_r($this->extended2,true).'</pre>';          
//echo $this->extended2->renderFieldset('COM_SPORTSMANAGEMENT_EXT_EXTENDED_PREFERENCES');          
foreach($this->extended2->getFieldset('COM_SPORTSMANAGEMENT_EXT_EXTENDED_PREFERENCES') as $field) : ?>
<div class="row">            
<div class="col-sm-3"><label for="<?php echo $field->name; ?>"><?php echo $field->label; ?></label></div>


<?php
switch ( $field->type )
{
case 'Url':
echo HTMLHelper::_('link', $field->value, $field->value, array("target" => "_blank"));
break;    
default:
?>
<div class="col-sm-9"><?php echo $field->value; ?></div>
<?php
break;    
}




?>

  
  
  </div>
  <?php
endforeach;  
}
     ?>
  
<?php
/*          
foreach ($this->extended as $key => $value)
{
	if ($value)
	{
		?>
        <div class="<?php echo $this->divclassrow; ?>">
            <div class="col-xs-<?php echo $this->config['extended_cols']; ?> col-sm-<?php echo $this->config['extended_cols']; ?> col-md-<?php echo $this->config['extended_cols']; ?> col-lg-<?php echo $this->config['extended_cols']; ?>">
                <div class="col-xs-<?php echo $this->config['extended_description_cols']; ?> col-sm-<?php echo $this->config['extended_description_cols']; ?> col-md-<?php echo $this->config['extended_description_cols']; ?> col-lg-<?php echo $this->config['extended_description_cols']; ?>">
                    <strong>
						<?php
						$keytext   = $key;
						$valuetext = $value;

						switch ($keytext)
						{
							case 'formation1':
								$keytext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_FORMATION_HOME';
								break;
							case 'formation2':
								$keytext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_FORMATION_AWAY';
								break;
							case 'Weather':
								$keytext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER';
								break;
						}

						switch ($valuetext)
						{
							case 'foggy':
								$valuetext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_FOGGY';
								break;
							case 'rainy':
								$valuetext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_RAINY';
								break;
							case 'sunny':
								$valuetext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_SUNNY';
								break;
							case 'windy':
								$valuetext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_WINDY';
								break;
							case 'dry':
								$valuetext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_DRY';
								break;
							case 'snowing':
								$valuetext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_WEATHER_SNOWING';
								break;
							case 'normal':
								$valuetext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_NORMAL';
								break;
							case 'wet':
								$valuetext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_WET';
								break;
							case 'fielddry':
								$valuetext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_DRY';
								break;
							case 'snow':
								$valuetext = 'COM_SPORTSMANAGEMENT_EXT_MATCH_FIELDCONDITION_SNOW';
								break;
						}

						echo Text::_($keytext) . ':';
						?>
                    </strong>
                </div>

                <div class="col-xs-<?php echo $this->config['extended_value_cols']; ?> col-sm-<?php echo $this->config['extended_value_cols']; ?> col-md-<?php echo $this->config['extended_value_cols']; ?> col-lg-<?php echo $this->config['extended_value_cols']; ?>">
					<?php
					echo Text::_($valuetext);
					?>
                </div>

            </div>
        </div>
		<?php
	}
}
*/

return;
?>
    <!-- EXTENDED DATA-->
<?php
if (count($this->extended->getFieldsets()) > 0)
{
	// Fieldset->name is set in the backend and is localized, so we need the backend language file here

	foreach ($this->extended->getFieldsets() as $fieldset)
	{
		if (isset($this->config['show_extended_geo_values']))
		{
			$fields = $this->extended->getFieldset($fieldset->name);
		}
		else
		{
			$fields = $this->extended->getFieldset('COM_SPORTSMANAGEMENT_EXT_EXTENDED_PREFERENCES');
		}

		if (count($fields) > 0)
		{
			// Check if the extended data contains information
			$hasData = false;

			foreach ($fields as $field)
			{
				// TODO: backendonly was a feature of JLGExtraParams, and is not yet available.
				//       (this functionality probably has to be added later)
				$value = $field->value;

				// Remark: empty($field->value) does not work, using an extra local var does

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


                <h4>
					<?php
					if ($this->config['show_extended_text'])
					{
						echo Text::_($fieldset->name);
					}
					?>
                </h4>

				<?php
				foreach ($fields as $field)
				{
					$value = $field->value;
					$label = $field->label;


					if (!empty($value)) // && !$field->backendonly)
					{
						$field->description = '';
						?>
                        <div class="<?php echo $this->divclassrow; ?>">
                            <div class="col-xs-<?php echo $this->config['extended_cols']; ?> col-sm-<?php echo $this->config['extended_cols']; ?> col-md-<?php echo $this->config['extended_cols']; ?> col-lg-<?php echo $this->config['extended_cols']; ?>">
                                <div class="col-xs-<?php echo $this->config['extended_description_cols']; ?> col-sm-<?php echo $this->config['extended_description_cols']; ?> col-md-<?php echo $this->config['extended_description_cols']; ?> col-lg-<?php echo $this->config['extended_description_cols']; ?>">

                                    <strong><?php echo Text::_($label) . ':'; ?></strong>
                                </div>
                                <div class="col-xs-<?php echo $this->config['extended_value_cols']; ?> col-sm-<?php echo $this->config['extended_value_cols']; ?> col-md-<?php echo $this->config['extended_value_cols']; ?> col-lg-<?php echo $this->config['extended_value_cols']; ?>">
									<?php
									if (is_array($field->value))
									{
										foreach ($field->value as $key => $value)
										{
											echo Text::_($value) . '<br>';
										}
									}
									else
									{
										switch ($field->value)
										{
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
											default:
												$value = $field->value;
												echo Text::_($value);
												break;
										}
									}
									?>
                                </div>
                            </div>
                        </div>
						<?php
					}
				}
				?>


                <br/>
				<?php
			}
		}
	}
}
