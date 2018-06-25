<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_3.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage extensions
*/


 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<div id="jsm" class="admin override">

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

<section class="content-block" role="main">

<div class="row-fluid">
<div class="span7">
<div class="well well-small">        
<div id="dashboard-icons" class="btn-group">

<?php 
                
foreach ( $this->sporttypes as $key => $value )
{
switch ($value)
{
case 'soccer':
?>
<a class="btn" href="index.php?option=com_sportsmanagement&view=jlextdfbnetplayerimport">
<img src="components/com_sportsmanagement/assets/icons/dfbnetimport.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT') ?>" /><br />
<span><?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT') ?></span>
</a>
<a class="btn" href="index.php?option=com_sportsmanagement&view=jlextdfbkeyimport">
<img src="components/com_sportsmanagement/assets/icons/dfbschluessel.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY') ?>" /><br />
<span><?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY') ?></span>
</a>
<a class="btn" href="index.php?option=com_sportsmanagement&view=jlextlmoimports">
<img src="components/com_sportsmanagement/assets/icons/lmoimport.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT') ?>" /><br />
<span><?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT') ?></span>
</a>
<a class="btn" href="index.php?option=com_sportsmanagement&view=jlextprofleagimport">
<img src="components/com_sportsmanagement/assets/icons/profleagueimport.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_PROF_LEAGUE_IMPORT') ?>" /><br />
<span><?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_PROF_LEAGUE_IMPORT') ?></span>
</a>                        
<?PHP
break;
case 'basketball':
?>
<a class="btn" href="index.php?option=com_sportsmanagement&view=jlextdbbimport">
<img src="components/com_sportsmanagement/assets/icons/dbbimport.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT') ?>" /><br />
<span><?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT') ?></span>
</a>                        
<?PHP
break;
case 'handball':
?>
<a class="btn" href="index.php?option=com_sportsmanagement&view=jlextsisimport">
<img src="components/com_sportsmanagement/assets/icons/sisimport.png" alt="<?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT') ?>" /><br />
<span><?php echo JText::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT') ?></span>
</a>                        
<?PHP
break;
default:
break;
}
}
?>
     
        
</div>        
</div>
</div>

<div class="span5">
					<div class="well well-small">
						<div class="center">
							<img src="components/com_sportsmanagement/assets/icons/boxklein.png" />
						</div>
						<hr class="hr-condensed">
						<dl class="dl-horizontal">
							<dt><?php echo JText::_('COM_SPORTSMANAGEMENT_VERSION') ?>:</dt>
							<dd><?php echo JText::sprintf( '%1$s', sportsmanagementHelper::getVersion() ); ?></dd>
                            
							<dt><?php echo JText::_('COM_SPORTSMANAGEMENT_DEVELOPERS') ?>:</dt>
							<dd><?php echo JText::_('COM_SPORTSMANAGEMENT_DEVELOPER_TEAM'); ?></dd>

							
                            <dt><?php echo JText::_('COM_SPORTSMANAGEMENT_SITE_LINK') ?>:</dt>
							<dd><a href="http://www.fussballineuropa.de" target="_blank">fussballineuropa</a></dd>
							
                            <dt><?php echo JText::_('COM_SPORTSMANAGEMENT_COPYRIGHT') ?>:</dt>
							<dd>&copy; 2014 fussballineuropa, All rights reserved.</dd>
							
                            <dt><?php echo JText::_('COM_SPORTSMANAGEMENT_LICENSE') ?>:</dt>
							<dd>GNU General Public License</dd>
						</dl>
					</div>

					

				</div>


</div>
</section>

</div>
</div>

<?PHP
//echo "<div>";
//echo $this->loadTemplate('footer');
//echo "</div>";
?>   