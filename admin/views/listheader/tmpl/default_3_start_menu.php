<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage sportsmanagement
 * @file       default_start_menue.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted Access'); // Protect from unauthorized access
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;


?>
<section class="content-block" role="main">
                <div class="row-fluid">
                    <div class="span12">
                       <!-- <div class="well well-small">       -->
			    <div>
<div class="span1">
                               <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_MENU') ?>"
				   href="index.php?option=com_sportsmanagement">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/transparent_schrift_48.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_MENU') ?>             
					</div>
				</a>
</div>
<div class="span1">				
                   <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS') ?>"
				   href="index.php?option=com_sportsmanagement&view=extensions">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/extensions.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS') ?>             
					</div>
				</a>       
</div>
<div class="span1">				
                   <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS') ?>"
				   href="index.php?option=com_sportsmanagement&view=specialextensions">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/extensions.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS') ?>             
					</div>
				</a>       
</div>
<div class="span1">				
                  <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS') ?>"
				   href="index.php?option=com_sportsmanagement&view=projects">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/projekte.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS') ?>             
					</div>
				</a>      
</div>
<div class="span1">				
                  <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS') ?>"
				   href="index.php?option=com_sportsmanagement&view=predictions">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/tippspiele.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS') ?>             
					</div>
				</a>      
</div>
<div class="span1">				
                       <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS') ?>"
				   href="index.php?option=com_sportsmanagement&view=currentseasons">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/aktuellesaison.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS') ?>             
					</div>
				</a>   
</div>
<div class="span1">				
                      <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_GOOGLE_CALENDAR') ?>"
				   href="index.php?option=com_sportsmanagement&view=currentseasons">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/google-calendar-48-icon.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_GOOGLE_CALENDAR') ?>             
					</div>
				</a>    
</div>

  
 </div>
                    </div>
                </div>
            </section>
  
  

	<?PHP

                          
                          
          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
                          
