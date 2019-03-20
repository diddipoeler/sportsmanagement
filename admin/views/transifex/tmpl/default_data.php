<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_data.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage transifex
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.modal');
?>
<div id="j-main-container">
<table class="table table-striped" id="contentList">
				<thead>
					<tr>
						
						<th class="title nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th class="title nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_TITLE_NATIVE', 'a.title_native', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_TAG', 'a.lang_code', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_CODE', 'a.sef', $listDirn, $listOrder); ?>
						</th>
						<th width="8%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_LANG_IMAGE', 'a.image', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'a.access', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_LANGUAGES_HEADING_HOMEPAGE', 'l.home', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone hidden-tablet">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.lang_id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
	
	
	
	
	
	
	
	
	
	
	
	
	
</table>	
</div>
	
