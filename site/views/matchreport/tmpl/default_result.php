<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

defined( '_JEXEC' ) or die( 'Restricted access' ); 

//echo '<pre>',print_r($this->team1,true),'</pre><br>';
//echo '<pre>',print_r($this->team2,true),'</pre><br>';

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
                /*
                echo JoomleagueHelper::getPictureThumb($this->team1->$pic, 
                                            $this->team1->name,
                                            $this->config['team_picture_width'],
                                            $this->config['team_picture_height'],1);
                */
//                echo JHtml::image($this->team1->$pic, $this->team1->name, array('title' => $this->team1->name,'width' => $this->config['team_picture_width'] ))                            
                ?>

<a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" title="<?php echo $this->team1->name;?>" data-toggle="modal" data-target="#t<?php echo $this->team1->id;?>">
<?PHP
echo JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture, $this->team1->name, array('title' => $this->team1->name,'class' => "img-rounded",'width' => $this->config['team_picture_width'] ));      
?>
</a>                        

<div class="modal fade" id="t<?php echo $this->team1->id;?>" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<?PHP
echo JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture, $this->team1->name, array('title' => $this->team1->name,'class' => "img-rounded" ));
?>
</div>
                
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
                
			/*
			echo JoomleagueHelper::getPictureThumb($this->team2->$pic, 
										$this->team2->name,
										$this->config['team_picture_width'],
										$this->config['team_picture_height'],1);
			*/
    //  echo JHtml::image($this->team2->$pic, $this->team2->name, array('title' => $this->team2->name,'width' => $this->config['team_picture_width'] ))							
		?>
<a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" title="<?php echo $this->team2->name;?>" data-toggle="modal" data-target="#t<?php echo $this->team2->id;?>">
<?PHP
echo JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture, $this->team2->name, array('title' => $this->team2->name,'class' => "img-rounded",'width' => $this->config['team_picture_width'] ));      
?>
</a>                        

<div class="modal fade" id="t<?php echo $this->team2->id;?>" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<?PHP
echo JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture, $this->team2->name, array('title' => $this->team2->name,'class' => "img-rounded" ));
?>
</div>
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