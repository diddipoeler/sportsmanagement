<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      deafault_extended.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage globalviews
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>
<!-- EXTENDED DATA-->
<?php
if(count($this->extended->getFieldsets()) > 0)
{
	// fieldset->name is set in the backend and is localized, so we need the backend language file here
	
	foreach ($this->extended->getFieldsets() as $fieldset)
	{
		if ( isset($this->config['show_extended_geo_values']) )
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
				
<div class="row-fluid">
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<h4>
<?php 
echo Text::_($fieldset->name); 
?>
</h4>

<div class="table-responsive">    
<table class="table">			
				<?php
				foreach ($fields as $field)
				{
					$value = $field->value;
					if (!empty($value)) // && !$field->backendonly)
					{
						?>
                        <tr>
                        <td>
                        <strong><?php echo Text::_( $field->label); ?></strong>
                        </td>
                        <td>
                            <?php
                            if ( is_array($field->value) )
                            {
                            
                            foreach( $field->value as $key => $value)  
                            {
                            echo Text::_( $value ).'<br>';      
                            }
                              
                            }
                            else
                            { 
                            echo Text::_( $field->value );
                            }
                            ?>
                        </td>
                        </tr>
						<?php
					}
				}
				?>
</table>
</div>
</div>
</div>                
                
				<br/>
				<?php
			}
		}
	}
}
?>	
