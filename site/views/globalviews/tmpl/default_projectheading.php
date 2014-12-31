<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );


if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo 'this->overallconfig<br /><pre>~' . print_r($this->overallconfig,true) . '~</pre><br />';
echo 'this->project<br /><pre>~' . print_r($this->project,true) . '~</pre><br />';
}

$nbcols = 2;
if ( $this->overallconfig['show_project_sporttype_picture'] ) { $nbcols++; }
if ( $this->overallconfig['show_project_kunena_link'] ) { $nbcols++; }
if ( $this->overallconfig['show_project_picture'] ) { $nbcols++; }
if ( $this->overallconfig['show_project_staffel_id'] ) { $nbcols++; }
if ( $this->overallconfig['show_project_heading'] == 1 && $this->project)
{
	?>
<!--	<div class="componentheading"> -->
		<div class="container">
        <table class="table">
			<tbody>
				<?php
				if ( $this->overallconfig['show_project_country'] == 1 )
				{
					?>
				<tr class="contentheading">
					<td colspan="<?php echo $nbcols; ?>">
					<?php
					$country = $this->project->country;
					echo JSMCountries::getCountryFlag($country) . ' ' . JSMCountries::getCountryName($country);
					?>
					</td>
				</tr>
				<?php	
			   	}
				?>
				<tr class="contentheading">
					<?php
          if ( $this->overallconfig['show_project_sporttype_picture'] == 1 )
					{
						?>
						<td>

<a href="<?php echo $this->project->sport_type_picture;?>" title="<?php echo $this->project->sport_type_name;?>" data-toggle="modal" data-target="#s<?php echo $this->project->sport_type_name;?>">
<?PHP
echo JHtml::image($this->project->sport_type_picture, $this->project->sport_type_name, array('title' => $this->project->sport_type_name,'class' => "img-rounded",'width' => $this->overallconfig['picture_width'] ));      
?>
</a>                        

<div class="modal fade" id="s<?php echo $this->project->sport_type_name;?>" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<?PHP
echo JHtml::image($this->project->sport_type_picture, $this->project->sport_type_name, array('title' => $this->project->sport_type_name,'class' => "img-rounded" ));
?>
</div>
                        
                        
						<?php
                        // diddipoeler
//                        echo JHtml::image($this->project->sport_type_picture, $this->project->sport_type_name, array('title' => $this->project->sport_type_name,'width' => $this->overallconfig['picture_width'] ));
						/*
                        echo JoomleagueHelper::getPictureThumb($this->project->sport_type_picture,
																$this->project->sport_type_name,
																$this->overallconfig['picture_width'],
																$this->overallconfig['picture_height'], 
																2);
						*/
                        ?>
						</td>
					<?php	
			    	}	
			    	if ( $this->overallconfig['show_project_picture'] == 1 )
					{
						$picture = $this->project->picture;
                        if ( $picture == 'images/com_sportsmanagement/database/placeholders/placeholder_150.png' || empty($picture) )
                        {
                            $picture = $this->project->leaguepicture;
                        }
                        
                        
                        ?>
						<td>



<a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture;?>" title="<?php echo $this->project->name;?>" data-toggle="modal" data-target="#p<?php echo $this->project->name;?>">
<?PHP
echo JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture, $this->project->name, array('title' => $this->project->name,'class' => "img-rounded",'width' => $this->overallconfig['picture_width'] ));      
?>
</a>                        

<div class="modal fade" id="p<?php echo $this->project->name;?>" tabindex="-1" role="dialog" aria-labelledby="modal" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
</div>
<?PHP
echo JHtml::image(COM_SPORTSMANAGEMENT_PICTURE_SERVER.$picture, $this->project->name, array('title' => $this->project->name,'class' => "img-rounded" ));
?>
</div>





                       
						<?php
                        // diddipoeler
                        //echo JHtml::image($picture, $this->project->name, array('title' => $this->project->name,'width' => $this->overallconfig['picture_width'] ));
						/*
                        echo JoomleagueHelper::getPictureThumb($this->project->picture,
																$this->project->name,
																$this->overallconfig['picture_width'],
																$this->overallconfig['picture_height'], 
																2);
						*/
                        ?>
						</td>
					<?php	
			    	}
			    	?>
					<?php	
			    	if ( $this->overallconfig['show_project_text'] == 1 )
					{
						?>
				    	<td>
						<?php
						echo $this->project->name;
						if (isset( $this->division))
						{
							echo ' - ' . $this->division->name;
						}
						?>
						</td>
					<?php	
			    	}
			    	
			    	if ( $this->overallconfig['show_project_staffel_id'] == 1 )
					{
						?>
				    	<td>
						<?php
						//echo $this->project->staffel_id;
						echo JText::sprintf('COM_SPORTSMANAGEMENT_PROJECT_INFO_STAFFEL_ID','<i>'.$this->project->staffel_id.'</i>');
						?>
						</td>
					<?php	
			    	}
			    	
			    	?>
					<td class="buttonheading" align="right">
					<?php
						if(JRequest::getVar('print') != 1) {
							$overallconfig = $this->overallconfig;
							echo sportsmanagementHelper::printbutton(null, $overallconfig);
						}
					?>
					&nbsp;
					</td>
                    
                    <td class="buttonheading" align="right">
					<?php
					if ( $this->overallconfig['show_project_kunena_link'] == 1 && $this->project->sb_catid )
                    {
                    $link = sportsmanagementHelperRoute::getKunenaRoute( $this->project->sb_catid );
						$imgTitle = JText::_($this->project->name.' Forum');
						$desc = JHtml::image('media/com_sportsmanagement/jl_images/kunena.logo.png', $imgTitle, array('title' => $imgTitle,'width' => '100' ));
						echo '&nbsp;';
						echo JHtml::link($link, $desc);    
                    }
					?>
					&nbsp;
					</td>
                    
				</tr>
			</tbody>
		</table>
	</div>
<?php 
} else {
	if ($this->overallconfig['show_print_button'] == 1) {
	?>
		<div class="componentheading">
			<table class="contentpaneopen">
				<tbody>
					<tr class="contentheading">
						<td class="buttonheading" align="right">
						<?php 
							if(JRequest::getVar('print') != 1) {
							  echo sportsmanagementHelper::printbutton(null, $this->overallconfig);
							}
						?>
						&nbsp;
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php 
	}
}
?>