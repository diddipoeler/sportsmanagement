<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_( 'behavior.tooltip' );
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">

<?PHP
if(version_compare(JVERSION,'3.0.0','ge')) 
{
echo $this->loadTemplate('joomla3');
}
else
{
echo $this->loadTemplate('joomla2');    
}
?>

<!--	<div id="editcell"> -->
		<table class="table">
			<thead>
				<tr>
					<th class="title" class="nowrap">
						<?php
						echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TOOL' );
						?>
					</th>
					<th class="title" class="nowrap">
						<?php
						echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_DESCR' );
						?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2">
						<?php
						echo "&nbsp;";
						?>
					</td>
				</tr>
			</tfoot>
			<tbody>
            
            <tr>
					<td class="nowrap" valign="top">
						<?php
						$link = JRoute::_( 'index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.truncate' );
						?>
						
                        <a href="<?php echo $link; ?>" title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE2' ); ?>">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE' );
							?>
						</a>
					</td>
					<td>
						<?php
						echo JText::_( "COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE_DESCR" );
						?>
					</td>
				</tr>
                
                <tr>
					<td class="nowrap" valign="top">
						<?php
						$link = JRoute::_( 'index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.truncatejl' );
						?>
						
                        <a href="<?php echo $link; ?>" title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE2JL' ); ?>">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATEJL' );
							?>
						</a>
					</td>
					<td>
						<?php
						echo JText::_( "COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATEJL_DESCR" );
						?>
					</td>
				</tr>
                
				<tr>
					<td class="nowrap" valign="top">
						<?php
						$link = JRoute::_( 'index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.optimize' );
						?>
						<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE2' ); ?>">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE' );
							?>
						</a>
					</td>
					<td>
						<?php
						echo JText::_( "COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE_DESCR" );
						?>
					</td>
				</tr>

				<tr>
					<td class="nowrap" valign="top">
						<?php
                        $link = JRoute::_( 'index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.repair' );
						?>
						
							<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR2' ); ?>">
                            <?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR' );
							?>
						</a>
					</td>
					<td>
						<?php
						echo JText::_( "COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR_DESCR" );
						?>
					</td>
				</tr>
                
                <tr>
					<td class="nowrap" valign="top">
						<?php
						$link = JRoute::_( 'index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.picturepath' );
						?>
						
                        <a  href="<?php echo $link; ?>" title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION2' ); ?>">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION' );
							?>
						</a>
					</td>
					<td>
						<?php
						echo JText::_( "COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION_DESCR" );
						?>
					</td>
				</tr>
                
                <tr>
					<td class="nowrap" valign="top">
						<?php
						$link = JRoute::_( 'index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.updatetemplatemasters' );
						?>
						
                        <a  href="<?php echo $link; ?>" title="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS2' ); ?>">
							<?php
							echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS' );
							?>
						</a>
					</td>
					<td>
						<?php
						echo JText::_( "COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS_DESCR" );
						?>
					</td>
				</tr>

			</tbody>
		</table>
<!--	</div> -->

	<input type="hidden" name="task" value="databasetool.execute" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>  