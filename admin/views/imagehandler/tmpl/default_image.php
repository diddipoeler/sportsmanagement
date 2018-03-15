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

// no direct access

defined('_JEXEC') or die('Restricted access');
?>
		<div class="item">
				<div align="center" class="imgBorder">
					<a onclick="window.parent.selectImage_<?php echo $this->type; ?>('<?php echo $this->_tmp_img->name; ?>', '<?php echo $this->_tmp_img->name; ?>', '<?php echo $this->field; ?>', '<?php echo $this->fieldid; ?>');">
						<div class="image">
							<img src="<?php echo JURI::root(); ?>/images/com_sportsmanagement/database/<?php echo $this->folder; ?>/<?php echo $this->_tmp_img->name; ?>"  width="<?php echo $this->_tmp_img->width_60; ?>" height="<?php echo $this->_tmp_img->height_60; ?>" alt="<?php echo $this->_tmp_img->name; ?> - <?php echo $this->_tmp_img->size; ?>" />
						</div>
					</a>
				</div>
			<div class="controls">
				<?php echo $this->_tmp_img->size; ?> -
				<a class="delete-item" href="index.php?option=com_sportsmanagement&amp;task=imagehandler.delete&amp;&amp;tmpl=component&amp;type=<?php echo $this->type; ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>">
					<img src="<?php echo JURI::root(); ?>/media/com_sportsmanagement/jl_images/publish_x.png" width="16" height="16" border="0" alt="<?php echo JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_DEL_IMG' ); ?>" />
				</a>
			</div>
			<div class="imageinfo">
				<?php 
                //echo $this->escape( substr( $this->_tmp_img->name, 0, 10 ) . ( strlen( $this->_tmp_img->name ) > 10 ? '...' : ''));
                echo $this->_tmp_img->name;  
                
                ?>
			</div>
		</div>