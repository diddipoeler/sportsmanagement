<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       default_projectheading.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$document = Factory::getDocument();
//$document->addScript('components/com_sportsmanagement/views/globalviews/tmpl/mapdata.js');
//$document->addScript('components/com_sportsmanagement/views/globalviews/tmpl/countrymap.js');
?>
<script>
	console.log("jquery version : " + jQuery().jquery);
	//    console.log("bootstrap version : " + jQuery.fn.tooltip.Constructor.VERSION);
	//
	//    if (typeof jQuery.fn.tooltip.Constructor.VERSION === 'undefined' || jQuery.fn.tooltip.Constructor.VERSION === null) {
	//        console.log("bootstrap version ist nicht vorhanden");
	//		<?php
				//		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/com_sportsmanagement/assets/css/jsmbootstrap.css' . '" type="text/css" />' . "\n";
				//		$document->addCustomTag($stylelink);
				//		
				?>
	//    }
</script>
<?php

$nbcols = 2;
$ausgabe = '';

if (!empty($this->overallconfig))
{
	//echo '<pre>'.print_r($this->overallconfig,true).'</pre>';
    //echo '<pre>'.print_r($this->config,true).'</pre>';
    if ($this->overallconfig['show_project_sporttype_picture'] && isset($this->overallconfig['show_project_sporttype_picture']))
	{
		$nbcols++;
	}

	if ($this->overallconfig['show_project_kunena_link'])
	{
		$nbcols++;
	}

	if ($this->overallconfig['show_project_picture'])
	{
		$nbcols++;
	}

	if ($this->overallconfig['show_project_staffel_id'])
	{
		$nbcols++;
	}

	if ($this->overallconfig['show_project_heading'] == 1 && $this->project)
	{
		?>
		<div class="<?php echo $this->divclassrow; ?>" id="projectheading" itemscope="itemscope" itemtype="http://schema.org/SportsOrganization">
			<table class="table">
				<?php
                if ($this->overallconfig['show_project_extrafield'])
				{
                $this->extrafields = sportsmanagementHelper::getUserExtraFields($this->project->league_id, 'frontend', 0,Factory::getApplication()->input->get('view'));
                $title = $this->project->league_name;
                foreach ($this->extrafields as $field ) if ( $field->fvalue )
	{
		$value      = $field->fvalue;
		$field_type = $field->field_type;
                   $ausgabe .= '<tr>';          
$ausgabe .= '<td>'.Text::_($field->name).'</td>';
switch ($field_type)
					{
						case 'link':
							$ausgabe .= '<td>'. HTMLHelper::_('link', $field->fvalue, $title, array("target" => "_blank")).'</td>';
							break;
						default:
							$ausgabe .= '<td>'. Text::_($field->fvalue).'</td>';
							break;
					}          
        
$ausgabe .= '</tr>';       
                   
                   
                   
                   
                    
                }
                echo $ausgabe;
                }
                
                
				if ($this->overallconfig['show_project_country'])
				{
				?>
					<tr class="contentheading">
						<td colspan="<?php echo $nbcols; ?>">
							<?php
							$country = $this->project->country;
							echo JSMCountries::getCountryFlag($country) . ' ' . JSMCountries::getCountryName($country);
/**
							$country_info = JSMCountries::getCountry($country);

							//echo '<pre>'.print_r($country_info,true).'</pre>';

							$javascript = "\n";
							$javascript .= $country_info->countrymap_mapdata;
							$document->addScriptDeclaration($javascript);

							$javascript = "\n";
							$javascript .= file_get_contents('components/com_sportsmanagement/views/globalviews/tmpl/simplemaps1.js');
							$document->addScriptDeclaration($javascript);

							//$document->addScript('components/com_sportsmanagement/views/globalviews/tmpl/simplemaps1.js');

							$javascript = "\n";
							$javascript .= $country_info->countrymap_mapinfo;
							$document->addScriptDeclaration($javascript);

							//$document->addScript('components/com_sportsmanagement/views/globalviews/tmpl/simplemaps2.js');

							$javascript = "\n";
							$javascript .= file_get_contents('components/com_sportsmanagement/views/globalviews/tmpl/simplemaps2.js');
							$document->addScriptDeclaration($javascript);
*/
							/*
							$document->addScript('components/com_sportsmanagement/views/globalviews/tmpl/simplemaps1.js');
							$javascript = "\n";
							$javascript .= $country_info->countrymap_mapinfo;
							$document->addScriptDeclaration($javascript);
							$document->addScript('components/com_sportsmanagement/views/globalviews/tmpl/simplemaps2.js');
							*/
							?>
						</td>

						<td>
							<div id="map"></div>
						</td>

					</tr>
				<?php
				}
				?>
				<tr class="contentheading">
					<?php
					if ($this->overallconfig['show_project_sporttype_picture'])
					{
					?>
						<td>

							<?PHP
							//if (!sportsmanagementHelper::existPicture($this->project->sport_type_picture))
							if (!curl_init(COM_SPORTSMANAGEMENT_PICTURE_SERVER . $this->project->sport_type_picture))
							{
								$this->project->sport_type_picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
							}

							echo sportsmanagementHelperHtml::getBootstrapModalImage(
								'sporttype_picture',
								COM_SPORTSMANAGEMENT_PICTURE_SERVER . $this->project->sport_type_picture,
								Text::_($this->project->sport_type_name),
								$this->overallconfig['picture_width'],
								'',
								$this->modalwidth,
								$this->modalheight,
								$this->overallconfig['use_jquery_modal'],
								'itemprop',
								'image'
							);
							?>


						</td>
					<?php
					}
					if ($this->overallconfig['show_project_picture'])
					{
						$picture   = $this->project->picture;
						$copyright = $this->project->cr_picture;
						if ($picture == 'images/com_sportsmanagement/database/placeholders/placeholder_150.png' || empty($picture))
						{
							$picture   = $this->project->leaguepicture;
							$copyright = $this->project->cr_leaguepicture;
						}

					?>
						<td>


							<?php
							//if (!sportsmanagementHelper::existPicture($picture))
							if (!curl_init(COM_SPORTSMANAGEMENT_PICTURE_SERVER . $picture))
							{
								$picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
							}
							echo sportsmanagementHelperHtml::getBootstrapModalImage(
								'project_picture',
								COM_SPORTSMANAGEMENT_PICTURE_SERVER . $picture,
								$this->project->name,
								$this->overallconfig['picture_width'],
								'',
								$this->modalwidth,
								$this->modalheight,
								$this->overallconfig['use_jquery_modal'],
								'itemprop',
								'image'
							);

							if ($copyright)
							{
								echo Text::sprintf('COM_SPORTSMANAGEMENT_COPYRIGHT_INFO', '<i>' . $copyright . '</i>');
							}
							?>

						</td>
					<?php
					}
					?>
					<?php
					if ($this->overallconfig['show_project_text'])
					{
					?>
						<td class="contentheading">
							<span itemprop="name">
								<?php
								echo $this->project->name;
								?>
							</span>
							<?php
							if (isset($this->division))
							{
								echo ' - ' . $this->division->name;
							}
							?>
						</td>
					<?php
					}

					if ($this->overallconfig['show_project_staffel_id'])
					{
					?>
						<td>
							<?php
							//echo $this->project->staffel_id;
							echo Text::sprintf('COM_SPORTSMANAGEMENT_PROJECT_INFO_STAFFEL_ID', '<i>' . $this->project->staffel_id . '</i>');
							?>
						</td>
					<?php
					}

					?>
					<td class="buttonheading" align="right">
						<?php
						if (Factory::getApplication()->input->getVar('print') != 1)
						{
							$overallconfig = $this->overallconfig;
							echo sportsmanagementHelper::printbutton(null, $overallconfig);
						}
						?>
						&nbsp;
					</td>

					<td class="buttonheading" align="right">
						<?php
						if ($this->overallconfig['show_project_kunena_link'] == 1 && $this->project->sb_catid)
						{
							$link     = sportsmanagementHelperRoute::getKunenaRoute($this->project->sb_catid);
							$imgTitle = Text::_($this->project->name . ' Forum');
							$desc     = HTMLHelper::image('media/com_sportsmanagement/jl_images/kunena.logo.png', $imgTitle, array('title' => $imgTitle, 'width' => '100'));
							echo '&nbsp;';
							echo HTMLHelper::link($link, $desc);
						}
						?>
						&nbsp;
					</td>

				</tr>
				<!--                </tbody> -->
			</table>
		</div>
		<?php
	}
	else
	{
		if ($this->overallconfig['show_print_button'])
		{
		?>
			<div class="<?php echo $this->divclassrow; ?>">
				<table class="table">
					<!--                <tbody> -->
					<tr class="contentheading">
						<td class="buttonheading" align="right">
							<?php
							if (Factory::getApplication()->input->getVar('print') != 1)
							{
								echo sportsmanagementHelper::printbutton(null, $this->overallconfig);
							}
							?>
							&nbsp;
						</td>
					</tr>
					<!--            </tbody> -->
				</table>
			</div>
		<?php
		}
	}
	
	if (isset($this->view))
	{
		switch ( $this->view )
		{
			case 'ranking':
			?>
			<div class="row">

			<div class="col-sm-6 text-left">
			<?php  
			//echo 'navigation <pre>'. print_r($this->config['show_project_navigation'],true) .'</pre>';  
			if ( !array_key_exists('show_project_navigation', $this->config) ) {
				$this->config['show_project_navigation'] = 1;
			}			
						
			$this->config['show_project_navigation'] = $this->config['show_project_navigation'] = '' ? 1 : $this->config['show_project_navigation'];  
			if ( $this->config['show_project_navigation'] )  
			{
				//echo 'name <pre>'. print_r($this->project->name,true) .'</pre>';    
				//echo 'league_id <pre>'. print_r($this->project->league_id,true) .'</pre>';   

				$nextproject = sportsmanagementModelProject::getnextproject($this->project->name, $this->project->league_id);  
				//echo 'league_id <pre>'. print_r($nextproject,true) .'</pre>';   
				$prevproject = sportsmanagementModelProject::getprevproject($this->project->name, $this->project->league_id);  
				//echo 'league_id <pre>'. print_r($prevproject,true) .'</pre>';   
				if ( $prevproject )
				{
					$routeparameter                       = array();
					$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
					$routeparameter['s']                  = $prevproject->season_id;
					$routeparameter['p']                  = $prevproject->slug;
					$routeparameter['type']               = 0;
					$routeparameter['r']                  = 0;
					$routeparameter['from']               = 0;
					$routeparameter['to']                 = 0;
					$routeparameter['division']           = 0;
					$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);  
						?>
					<a href="<?php echo $link; ?>" class="btn btn-primary btn-sm active" role="button" aria-pressed="true"><< <?php echo $prevproject->name; ?></a> 
					<?php
				}
				?>


				</div>
				<div class="col-sm-6 text-right">
				<?php
				if ( $nextproject )
				{
					$routeparameter                       = array();
					$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
					$routeparameter['s']                  = $nextproject->season_id;
					$routeparameter['p']                  = $nextproject->slug;
					$routeparameter['type']               = 0;
					$routeparameter['r']                  = 0;
					$routeparameter['from']               = 0;
					$routeparameter['to']                 = 0;
					$routeparameter['division']           = 0;
					$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);  
						?>
					<a href="<?php echo $link; ?>" class="btn btn-primary btn-sm active" role="button" aria-pressed="true"><?php echo $nextproject->name; ?> >></a> 
					<?php
				}
			}

			?>

			</div>
			</div>
			<?php  	
			break;
		}	
	}
	
	
	
	
}
