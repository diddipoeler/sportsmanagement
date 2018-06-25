<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_projectheading.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage globalviews
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

//echo 'this->project<br /><pre>~' . print_r($this->project,true) . '~</pre><br />';

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
//echo 'this->overallconfig<br /><pre>~' . print_r($this->overallconfig,true) . '~</pre><br />';
//echo 'this->project<br /><pre>~' . print_r($this->project,true) . '~</pre><br />';
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
<!--		<div class="container"> -->
		<table class="table">
<!--				<tbody> -->
				<?php
				if ( $this->overallconfig['show_project_country'] )
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
		if ( $this->overallconfig['show_project_sporttype_picture'] )
					{
						?>
						<td>

<?PHP
if ( !sportsmanagementHelper::existPicture( $this->project->sport_type_picture ) )
{
$this->project->sport_type_picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
}

echo sportsmanagementHelperHtml::getBootstrapModalImage('sporttype_picture',
$this->project->sport_type_picture,
$this->project->sport_type_name,
$this->overallconfig['picture_width'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']							
);
?>
          

						</td>
					<?php
			    	}
			    if ( $this->overallconfig['show_project_picture'] )
				{
				$picture = $this->project->picture;
				$copyright = $this->project->cr_picture;
                if ( $picture == 'images/com_sportsmanagement/database/placeholders/placeholder_150.png' || empty($picture) )
				{
				$picture = $this->project->leaguepicture;
				$copyright = $this->project->cr_leaguepicture;
				}

					?>
						<td>


<?php
if ( !sportsmanagementHelper::existPicture( $picture ) )
{
$picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig"); 
}
echo sportsmanagementHelperHtml::getBootstrapModalImage('project_picture',
$picture,
$this->project->name,
$this->overallconfig['picture_width'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']
);

if ( $copyright )
{
echo JText::sprintf('COM_SPORTSMANAGEMENT_COPYRIGHT_INFO','<i>'.$copyright.'</i>');
}
?>

						</td>
					<?php	
			    	}
			    	?>
					<?php
			    	if ( $this->overallconfig['show_project_text'] )
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
			    	
			    	if ( $this->overallconfig['show_project_staffel_id'] )
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
						if(JFactory::getApplication()->input->getVar('print') != 1) {
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
<!--				</tbody> -->
		</table>
<!--	</div> -->
<?php 
}
else
	{
	if ( $this->overallconfig['show_print_button'] )
	{
?>
		<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
			<table class="table">
	<!--				<tbody> -->
					<tr class="contentheading">
						<td class="buttonheading" align="right">
						<?php 
							if(JFactory::getApplication()->input->getVar('print') != 1)
							{
								echo sportsmanagementHelper::printbutton(null, $this->overallconfig);
							}
						?>
						&nbsp;
						</td>
					</tr>
		<!--			</tbody> -->
			</table>
		</div>
<?php
	}
}
?>
