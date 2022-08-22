<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version   1.0.05
 * @file      agegroup.php
 * @author    diddipoeler, stony, svdoldie und donclumsy, llambion (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
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
 *
 *
 * Sticker template
 * Version 1.0.0
 * Created by llambion
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

		$css      = Uri::base() . 'modules/' . $module->module . '/assets/rquote.css';
		$document = Factory::getDocument();
		$document->addStyleSheet($css);

		$quotemarks = $params->get('quotemarks');


		$border = $params->get('border');
		$border_color = $params->get('border_color');
		$border_rounded = $params->get('border_rounded');
		$border_shadow = $params->get('border_shadow');
		$background_color = $params->get('background_color');
		$text_color = $params->get('text_color');
		$text_size = $params->get('text_size');
		$text_italic = $params->get('text_italic');
		$author_color = $params->get('author_color');
		$author_size = $params->get('author_size');
		$author_italic = $params->get('author_italic');
		$author_align = $params->get('author_align');
		$show_picture = $params->get('showpicture');
		
		$style = "";
		
		if ($border)
		 {
			$style = "border: 1px solid " . $border_color . "; " ;
			$style =  $style . 'border-radius: 20px ; ';
			if($border_rounded)
			{
				$style =  $style . 'border-radius: 20px ; ';
			}
			if($border_shadow)
			{
				$style =  $style . 'box-shadow: 10px 10px 6px 3px #474747; ';
			}			
			
		 }		
		
		if ($cfg_which_database)
			{
				$paramscomponent = ComponentHelper::getParams('com_sportsmanagement');
				DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $paramscomponent->get('cfg_which_database_server'));
			}
		else
			{
				DEFINE('COM_SPORTSMANAGEMENT_PICTURE_SERVER', Uri::root());
			}

		if (!isset($rquote->person_picture))
			{
				$rquote->person_picture = $rquote->picture;
			}


		if ($list)
			{
				foreach ($list as $rquote)
				{
				
				$rquote->default_picture = sportsmanagementHelper::getDefaultPlaceholder('clublogobig');

				if (curl_init($module->picture_server . DIRECTORY_SEPARATOR . $rquote->person_picture) && $rquote->person_picture != '')
				{
					$picture = $rquote->person_picture;
				}
                elseif (curl_init($module->picture_server . DIRECTORY_SEPARATOR . $rquote->default_picture) && $rquote->default_picture != '')
				{
					$picture = $rquote->default_picture;
				}
			
			
	?>	
					<div class="container-fluid" style="<? echo $style; ?>
											background-color: <? echo $background_color; ?>;
											margin: 0 0 25px;">											
											
				<?php							
					if ($show_picture)
							{
								?>
								 <div class="photo">
                                    <img src="<?php echo $picture; ?>" class="img-responsive" width="<?php echo $params->get('picture_width', 50); ?>"/>
                                </div>
							<?php	
							}											
											
				?>							
						<div class="row"style="text-align:left;
												font-style: <?php echo ($text_italic ? 'italic' : 'normal'); ?> ;
												font-size: <? echo $text_size;?>px;
												color: <? echo $text_color;?> ; 
												display:block;
												margin: 0px 0px 10px 10px;
												clear:both;">											
							<?php echo $rquote->quote; ?>
						</div>
						
						<div class="row"style="text-align:<?php echo ($author_align ? 'right' : 'left'); ?>;
												font-style: <?php echo ($author_italic ? 'italic' : 'normal'); ?> ;
												font-size: <? echo $author_size;?>px;
												color: <? echo $author_color;?> ; 
												display:block;
												margin: 0px 0px 10px 10px;
												clear:both;">											
							<?php echo $rquote->author; ?>
						</div>						
					</div>	
	<?php	
				}
			}

		else
			{
				echo Text::_('MOD_SPORTSMANAGEMENT_RQUOTES_NUMBER_RANDOM_QUOTES_ERROR');
			}



?>