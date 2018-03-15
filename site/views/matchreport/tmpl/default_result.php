<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_result.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
//echo '<pre>'.print_r($this->config,true).'</pre>';
?>

<!-- START: game result -->
<table class="table" >

    <?php
    if ($this->config['show_team_logo'] == 1)
    {
    ?>
        <tr>
            <td class="teamlogo">
                <?php 
                //dynamic object property string
                $pic = $this->config['show_picture'];
                
                if ( !JFile::exists(JPATH_SITE.DS.$this->team1->$pic) )
				{
                    $picture = sportsmanagementHelper::getDefaultPlaceholder("team");
                }
                else
                {
                    $picture = $this->team1->$pic;
                }    
                            
                ?>

<?PHP
echo sportsmanagementHelperHtml::getBootstrapModalImage('team2mare'.$this->team1->id,$picture,$this->team1->name,$this->config['team_picture_width']);                     
?>
                
		</td>
		<td>
		</td>
        <td>
		</td>
        <td>
		</td>
		<td class="teamlogo">
			<?php 
            if ( !JFile::exists(JPATH_SITE.DS.$this->team2->$pic) )
				{
                    $picture = sportsmanagementHelper::getDefaultPlaceholder("team");
                }
                else
                {
                    $picture = $this->team2->$pic;
                }   

echo sportsmanagementHelperHtml::getBootstrapModalImage('team2mare'.$this->team2->id,$picture,$this->team2->name,$this->config['team_picture_width']);                     
							
		?>
        

                   

		</td>
	</tr>

    <?php
    } // end team logo
    ?>
    
	<tr>
		<td class="team">
			<?php 
			if ( $this->config['names'] == "short_name" ) {
			    echo $this->team1->short_name; 
			}
			if ( $this->config['names'] == "middle_name" ) {
			    echo $this->team1->middle_name; 
			}
			if ( $this->config['names'] == "name" ) {
			    echo $this->team1->name; 
			}
			?>
		</td>
        <td class="resulthome">
				<?php echo $this->showMatchresult($this->match->alt_decision, 1); ?>
			</td>
		<td>
			<?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_VS') ?>
		</td>
        <td class="resultaway">
				<?php echo $this->showMatchresult($this->match->alt_decision, 2); ?>
			</td>
		<td class="team">
			<?php 
			if ( $this->config['names'] == "short_name" ) {
			    echo $this->team2->short_name; 
			}
			if ( $this->config['names'] == "middle_name" ) {
			    echo $this->team2->middle_name; 
			}
			if ( $this->config['names'] == "name" ) {
			    echo $this->team2->name; 
			}
			?>
		</td>
	</tr>
	<?php
        if ($this->config['show_period_result'] == 1)
        {
            if ( $this->showLegresult() )
            {
                ?>
                <tr>
                <td>
		</td>
                    <td class="legshome">
                        <?php echo $this->showLegresult(1); ?>
                    </td>
                    <td>
			<?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_VS') ?>
		</td>
                    <td class="legsaway">
                        <?php echo $this->showLegresult(2); ?>
                    </td>
                    <td>
		</td>
                </tr>
                <?php
            }
        }
        ?>
	
</table>

<?php
if ($this->match->cancel > 0)
{
	?>
	<table class="matchreport" border="0">
		<tr>
			<td class="result">
					<?php echo $this->match->cancel_reason; ?>
			</td>
		</tr>
	</table>
	<?php
}
else
{
	?>
	<table class="matchreport" border="0">
		
        
		<?php
        

        if ($this->config['show_overtime_result'] == 1)
        {
            if ( $this->showOvertimeResult() )
            {
                ?>
                <tr>
                    <td class="legs" colspan="2">
                        <?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_OVERTIME');
                        echo " " . $this->showOvertimeresult(); ?>
                    </td>
                </tr>
                <?php
            }
        }

        if ($this->config['show_shotout_result'] == 1)
        {
            if ( $this->showShotoutResult() )
            {
                ?>
                <tr>
                    <td class="legs" colspan="2">
                        <?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SHOOTOUT');
                        echo " " . $this->showShotoutResult(); ?>
                    </td>
                </tr>
                <?php
            }
        }
		?>
	</table>
	<?php
}

if ( $this->config['show_timeline'] && $this->config['show_timeline_under_results'] )
{
echo $this->loadTemplate('timeline');
}
?>

<!-- START of decision info -->
<?php
if ( $this->match->decision_info != '' )
{
	?>
	<table class="matchreport">
		<tr>
			<td>
				<i><?php echo $this->match->decision_info; ?></i>
			</td>
		</tr>
	</table>

	<?php
}
?>
<!-- END of decision info -->
<!-- END: game result -->
