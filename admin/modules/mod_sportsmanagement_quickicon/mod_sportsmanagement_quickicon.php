<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_quickicon
 * @file       mod_sportsmanagement_quickicon.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted Access'); // Protect from unauthorized access
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

// Make sure Sportsmanagement is enabled
if (!ComponentHelper::isEnabled('com_sportsmanagement', true))
{
	Log::add(Text::_('SM_NOT_ENABLED'), Log::ERROR, 'jsmerror');

	return;
}

// Require_once __DIR__ . '/helper.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php';
$position = ModSportsmanagementQuickIconHelper::getModPosition();


$document = Factory::getDocument();
$document->addStyleSheet(JURI::base(true) . '/modules/mod_sportsmanagement_quickicon/tmpl/css/style.css');

if (version_compare(JVERSION, '1.6.0', 'ge'))
{
	$jsm_version = '';
}
else
{
	$jsm_version = '15';
}

// Joomla versionen
if (version_compare(JVERSION, '4.0.0', 'ge'))
{
	?>
    <nav class="quick-icons px-3 pb-3" aria-label="Schnellstartlinks Sportspamangement">
		<ul class="nav flex-wrap">
			<li class="quickicon quickicon-single">
				<a title="<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LINK') ?>"
				   href="index.php?option=com_sportsmanagement">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/transparent_schrift_48.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LABEL') ?>             
					</div>
				</a>
			</li>
			<li class="quickicon quickicon-single">
				<a title="<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LINK') ?>"
				   href="index.php?option=com_sportsmanagement&view=extensions">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/extensions.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LABEL') ?>             
					</div>
				</a>
			</li>
			<li class="quickicon quickicon-single">
				<a title="<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LINK') ?>"
				   href="index.php?option=com_sportsmanagement&view=projects">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/projekte.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LABEL') ?>             
					</div>
				</a>
			</li>
			<li class="quickicon quickicon-single">
				<a title="<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LINK') ?>"
				   href="index.php?option=com_sportsmanagement&view=predictions">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/tippspiele.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LABEL') ?>             
					</div>
				</a>
			</li>
			<li class="quickicon quickicon-single">
				<a title="<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LINK') ?>"
				   href="index.php?option=com_sportsmanagement&view=currentseasons">
					<div class="quickicon-icon">
						<img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/aktuellesaison.png">
					</div>
					<div class="quickicon-name d-flex align-items-end">
						<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LABEL') ?>             
					</div>
				</a>
			</li>
		</ul>
    </nav>
	<?PHP
}
else if (version_compare(JVERSION, '3.0.0', 'ge') && $position == 'icon')
{
	// Require_once __DIR__ . '/helper.php';
	$buttons = ModSportsmanagementQuickIconHelper::getButtons($params);
	$html    = HTMLHelper::_('links.linksgroups', ModSportsmanagementQuickIconHelper::groupButtons($buttons));

	if (!empty($html))
		:
		?>
        <div class="sidebar-nav quick-icons">
			<?php echo $html; ?>
        </div>
	<?php endif;
}
else
{
	?>
    <div id="jsmQuickIcons<?php echo $jsm_version; ?>" class="jsmNoLogo">
        <div class="icon-wrapper">
            <div class="icon">
                <a title="<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LINK') ?>"
                   href="index.php?option=com_sportsmanagement">
                    <img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/transparent_schrift_48.png">
                    <span>          
			<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PANEL_LABEL') ?>             
		</span></a>
            </div>
        </div>
        <div class="icon-wrapper">
            <div class="icon">
                <a title="<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LINK') ?>"
                   href="index.php?option=com_sportsmanagement&view=extensions">
                    <img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/extensions.png">
                    <span>          
			<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_EXTENSIONS_LABEL') ?>             
		</span></a>
            </div>
        </div>
        <div class="icon-wrapper">
            <div class="icon">
                <a title="<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LINK') ?>"
                   href="index.php?option=com_sportsmanagement&view=projects">
                    <img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/projekte.png">
                    <span>          
			<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PROJECTS_LABEL') ?>             
		</span></a>
            </div>
        </div>
        <div class="icon-wrapper">
            <div class="icon">
                <a title="<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LINK') ?>"
                   href="index.php?option=com_sportsmanagement&view=predictions">
                    <img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/tippspiele.png">
                    <span>          
			<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_PREDICTIONS_LABEL') ?>             
		</span></a>
            </div>
        </div>
        <div class="icon-wrapper">
            <div class="icon">
                <a title="<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LINK') ?>"
                   href="index.php?option=com_sportsmanagement&view=currentseasons">
                    <img src="<?php echo JURI::base(false) ?>/components/com_sportsmanagement/assets/icons/aktuellesaison.png">
                    <span>          
			<?php echo Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_CURRENT_SAISON_LABEL') ?>             
		</span></a>
            </div>
        </div>
    </div>
	<?PHP
}
