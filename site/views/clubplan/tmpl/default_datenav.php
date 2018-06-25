<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_datenav.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage clubplan
 */

defined('_JEXEC') or die('Restricted access');

//echo '<pre>',print_r($this->club,true),'</pre><br>';

?>
<form name="adminForm" id="adminForm" method="post">
	<?php $dateformat="%d-%m-%Y"; ?>
	<table class="table" >
		<tr>
        <td>
        <?php
                echo "".JHtml::_('select.genericlist', $this->lists['fromteamart'], 'teamartsel' , 'class="inputbox" size="1" onchange="hideclubplandate();" ', 'value', 'text', $this->teamartsel )."";
                //echo "".JHtml::_('select.genericlist', $this->lists['fromteamprojects'], 'teamprojectssel' , 'class="inputbox" size="1" onchange="hideclubplandate();" ', 'value', 'text', $this->teamprojectssel )."";
                ?>
                </td>
                <td>
                <?PHP
                echo "".JHtml::_('select.genericlist', $this->lists['fromteamseasons'], 'teamseasonssel' , 'class="inputbox" size="1" onchange="hideclubplandate();" ', 'value', 'text', $this->teamseasonssel )."";                
				?>
            </td>
        </tr>
        <tr>
			<td>
            <?php
				echo JHtml::calendar(sportsmanagementHelper::convertDate($this->startdate,1),'startdate','startdate',$dateformat);
                ?>
                </td>
                <td>
                <?PHP
				echo ' - '.JHtml::calendar(sportsmanagementHelper::convertDate($this->enddate,1),'enddate','enddate',$dateformat);
                ?>
                </td>
                <td>
                <?PHP
                echo "".JHtml::_('select.genericlist', $this->lists['type'], 'type' , 'class="inputbox" size="1" onchange="" ', 'value', 'text', $this->type )."";
				?>
                </td>
                <td>
                <input type="submit" class="<?PHP echo $this->config['button_style']; ?>" name="reload View" value="<?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_FILTER'); ?>" />
                </td>
			            
            <td>
            <?php
            
			if ( $this->club )
			{
				$picture = $this->club->logo_big;
                if ( !sportsmanagementHelper::existPicture($picture) )
    {
    $picture = sportsmanagementHelper::getDefaultPlaceholder('logo_big');    
    }
echo sportsmanagementHelperHtml::getBootstrapModalImage('clplan'.$this->club->id,$picture,$this->club->name,'50');

           
            }
			?>
            </td>
		</tr>
        
	</table>
	<?php echo JHtml::_('form.token')."\n"; ?>
</form><br />