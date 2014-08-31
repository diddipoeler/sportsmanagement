<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
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