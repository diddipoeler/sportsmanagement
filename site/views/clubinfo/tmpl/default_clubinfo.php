<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage clubinfo
 * @file       default_clubinfo.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

//$this->columns = 2;
$this->divclass = '';

$paramscomponent = ComponentHelper::getParams('com_sportsmanagement');

if ( version_compare(JVERSION, '4.0.0', 'ge') || $paramscomponent->get('use_jsmgrid'))
{
	$this->divclass = 'col-lg-';
}
elseif ($this->overallconfig['use_bootstrap_version'] && !$paramscomponent->get('use_jsmgrid'))
{
	//$this->divclass = 'col p-2';
	$this->divclass .= "col-xs-";
	$this->divclass .= " col-sm-";
	$this->divclass .= " col-md-";
	$this->divclass .= " col-lg-";
}
else
{
	$this->divclass = "span";
}

if (!isset($this->club))
{
	Log::add(Text::_('Error: ClubID was not submitted in URL or Club was not found in database'));
}
else
{
	?>


<div class="row">
    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2" style="">
   <!-- SHOW LOGO - START -->
			<?php
			if ($this->config['show_club_logo'] && $this->club->logo_big != '')
			{
				$club_emblem_title = str_replace("%CLUBNAME%", $this->club->name, Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_EMBLEM_TITLE'));
				$picture           = $this->club->logo_big;
			}

			$picture = empty($picture) ? sportsmanagementHelper::getDefaultPlaceholder('logo_big') : $picture;

			echo sportsmanagementHelperHtml::getBootstrapModalImage(
				'clubinfo' . $this->club->id,
				$picture,
				$club_emblem_title,
				$this->config['club_logo_width'],
				'',
				$this->modalwidth,
				$this->modalheight,
				$this->overallconfig['use_jquery_modal'],
				'itemprop',
				'image'
			);

			if ($this->config['show_club_logo_copyright'])
			{
				if ($this->club->cr_logo_big)
				{
					echo Text::sprintf('COM_SPORTSMANAGEMENT_PAINTER_INFO', '<i>' . $this->club->cr_logo_big . '</i>');
				}
				?>
                <!--        : &copy; -->
				<?PHP

			}
			?>
            <br/>    
  
  </div>
  
  
    <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3" style="">
      
     <?php
		if ($this->config['show_club_info'])
		{
			?>
            
				<?php
				if ($this->club->address || $this->club->zipcode || $this->club->location)
				{
					$addressString = JSMCountries::convertAddressString($this->club->name, $this->club->address, $this->club->state, $this->club->zipcode, $this->club->location, $this->club->country, 'COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS_FORM');
					?>
                    <address>
                        <strong>
				<?php
							echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_ADDRESS');
							?>
			    </strong>
			    
						<?php echo $addressString; ?>
				

                        <span class="clubinfo_listing_value">
                            <?php
                            if (isset($this->clubassoc->name))
                            {
	                            echo HTMLHelper::image($this->clubassoc->assocflag, $this->clubassoc->name, array('title' => $this->clubassoc->name, 'width' => $this->config['club_assoc_flag_width']));
	                            //echo HTMLHelper::image($this->clubassoc->picture, $this->clubassoc->name, array('title' => $this->clubassoc->name, 'width' => $this->config['club_assoc_logo_width'])) . substr($this->clubassoc->name, 0, 30);
                                echo HTMLHelper::image($this->clubassoc->picture, $this->clubassoc->name, array('title' => $this->clubassoc->name, 'width' => $this->config['club_assoc_logo_width']));
                            }
                            ?>
                            <br/>
                        </span>
                    </address>

					<?php
				}

				if ($this->club->phone)
				{
					?>
                    <address >
			    
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PHONE'); ?></strong>
			    <span itemprop="telephone">
						<?php echo $this->club->phone; ?>
				    </span>
                    </address>
					<?php
				}
			else
			{
			?>
		    <span itemprop="telephone"></span>
		    <?php
			}

				if ($this->club->fax)
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_FAX'); ?></strong>
			    <span itemprop="faxNumber">
						<?php echo $this->club->fax; ?>
				    </span>
                    </address>
					<?php
				}
			else
			{
			?>
		    <span itemprop="faxNumber"></span>
		    <?php
			}

				if ($this->club->email)
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_EMAIL'); ?></strong>

						<?php
						// to prevent spam, crypt email display if nospam_email is selected
						//or user is a guest
						$user = Factory::getUser();
						if (($user->id) or (!$this->overallconfig['nospam_email']))
						{
							?><a
                            href="mailto: <?php echo $this->club->email; ?>"><?php echo $this->club->email; ?></a><?php
						}
						else
						{
							?>
			    <span itemprop="email">
			    <?php
							echo $this->club->email;
							?>
			    </span>
			    <?php
						}
						?>
                    </address>
					<?php
				}
			else
			{
			?>
		    <span itemprop="email"></span>
		    <?php
			}

				if ($this->club->website)
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_WWW'); ?></strong>
<span itemprop="url">
						<?php echo HTMLHelper::_('link', $this->club->website, $this->club->website, array("target" => "_blank")); ?>
</span>
                    </address>
					<?php
				}

				if ($this->club->twitter)
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PERSON_TWITTER'); ?></strong>

						<?php echo HTMLHelper::_('link', $this->club->twitter, $this->club->twitter, array("target" => "_blank")); ?>

                    </address>
					<?php
				}
				if ($this->club->facebook)
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_EXT_PERSON_FACEBOOK'); ?></strong>

						<?php echo HTMLHelper::_('link', $this->club->facebook, $this->club->facebook, array("target" => "_blank")); ?>

                    </address>
					<?php
				}

				if ($this->club->president)
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PRESIDENT'); ?></strong>
						<?php echo $this->club->president; ?>
                    </address>
					<?php
				}

				if ($this->club->manager)
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_MANAGER'); ?></strong>
						<?php echo $this->club->manager; ?>
                    </address>
					<?php
				}

				if ($this->club->founded && $this->club->founded != '0000-00-00' && $this->config['show_founded'])
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_FOUNDED'); ?></strong>
						<?php //echo sportsmanagementHelper::convertDate($this->club->founded, 1); ?>
                        <?php echo HTMLHelper::date($this->club->founded, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE')); ?>
                    </address>
					<?php
				}
				if ($this->club->founded_year && $this->config['show_founded_year'])
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_FOUNDED_YEAR'); ?></strong>
						<?php echo $this->club->founded_year; ?>
                    </address>
					<?php
				}
				if ($this->club->dissolved && $this->club->dissolved != '0000-00-00' && $this->config['show_dissolved'])
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_DISSOLVED'); ?></strong>
						<?php //echo sportsmanagementHelper::convertDate($this->club->dissolved, 1) ?>
			    <?php echo HTMLHelper::date($this->club->dissolved, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE')); ?>
                    </address>
					<?php
				}
				if ($this->club->dissolved_year && $this->config['show_dissolved_year'])
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_DISSOLVED_YEAR'); ?></strong>
						<?php echo $this->club->dissolved_year; ?>
                    </address>
					<?php
				}
				if ($this->club->unique_id)
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_UNIQUE_ID'); ?></strong>
						<?php echo $this->club->unique_id; ?>
                    </address>
					<?php
				}

				if ($this->config['show_playgrounds_of_club'] && (isset($this->stadiums)) && (count($this->stadiums) > 0))
				{
					?>
                    <!-- SHOW PLAYGROUNDS - START -->
					<?php
					$playground_number = 1;
					foreach ($this->playgrounds AS $playground)
					{
						$routeparameter                       = array();
						$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
						$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
						$routeparameter['p']                  = $this->project->slug;
						$routeparameter['pgid']               = $playground->slug;
						$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('playground', $routeparameter);
						$pl_dummy                             = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PLAYGROUND');
						?>
                        <address>
                            <strong>
								<?php
								echo str_replace("%NUMBER%", $playground_number, $pl_dummy);
								?>
                            </strong>
							<?php
							echo HTMLHelper::link($link, $playground->name);
							if (!sportsmanagementHelper::existPicture($playground->picture))
							{
								$playground->picture = sportsmanagementHelper::getDefaultPlaceholder('stadium');
							}
							echo sportsmanagementHelperHtml::getBootstrapModalImage(
								'playground' . $playground->id,
								$playground->picture,
								$playground->name,
								$this->config['playground_picture_width'],
								'',
								$this->modalwidth,
								$this->modalheight,
								$this->overallconfig['use_jquery_modal']
							);

							?>
                        </address>
						<?php
						$playground_number++;
					}
					?>
                    <!-- SHOW PLAYGROUNDS - END -->
					<?php
				}

				if ($this->config['show_club_kunena_link'] && $this->club->sb_catid)
				{
					?>
                    <span class="clubinfo_listing_item">
                    </span>
					<?PHP
					$link     = sportsmanagementHelperRoute::getKunenaRoute($this->club->sb_catid);
					$imgTitle = Text::_($this->club->name . ' Forum');
					$desc     = HTMLHelper::image('media/COM_SPORTSMANAGEMENT/jl_images/kunena.logo.png', $imgTitle, array('title' => $imgTitle, 'width' => '100'));
					?>
                    <span class="clubinfo_listing_value">
                        <?PHP
                        echo HTMLHelper::link($link, $desc);
                        ?>
                    </span>
					<?PHP
				}


			
			
			

				?>
				
        
		<?php 
		}
		?> 
  
  
  
  
  
  </div>
   
    <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7" style="">
<?PHP
if ($this->config['show_notes_club'])
		{
?>

<?php  
$this->notes = array();
$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_DESCRIPTION');
echo $this->loadTemplate('jsm_notes');
?>       
   
		<?php
		$description = $this->club->notes;
		$description = HTMLHelper::_('content.prepare', $description);
		echo $description;
		?>
   
	<?php			
		}			
			
		?>      
  
  
  
  
  </div>
  </div>

















   <?php } ?>
