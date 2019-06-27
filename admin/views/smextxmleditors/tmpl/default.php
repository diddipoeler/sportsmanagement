<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage smextxmleditors
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);




?>

<?PHP

//if(version_compare(JVERSION,'3.0.0','ge')) 
//{
//echo $this->loadTemplate('joomla3');
//}
//else
//{
//echo $this->loadTemplate('joomla2');    
//}

?>

<!-- <fieldset class="adminform"> -->
			<legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXT_XML'); ?></legend>

<table class="<?php echo $this->table_data_class; ?>">
<?PHP
foreach ( $this->files as $file )
{


			$link = Route::_('index.php?option=com_sportsmanagement&view=smextxmleditor&layout=default&file_name='.$file);
			?>
			<tr class="">
				<td class="center"></td>
				<?php
					
                    ?>
                    <td class="center" nowrap="nowrap">
								<a	href="<?php echo $link; ?>" >
                                    <?php
									$imageTitle=Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXT_XML_EDIT');
									echo HTMLHelper::_(	'image','administrator/components/com_sportsmanagement/assets/images/edit.png',
													$imageTitle,'title= "'.$imageTitle.'"');
									?>
                    </a>                 
					</td>
				<td>
                <?php
					
					echo $file;
					
					?>
     </td>
	
			</tr>
			<?php

    
}    

?>
</table>
<!-- </fieldset> -->
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>  