<?php 
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file components/sportsmanagement/views/allprojects/tmpl/default_items.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
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

//echo '<pre>'.print_r($this->items,true).'</pre>';

?>
<div class="table-responsive">        
<table class="<?php echo $this->tableclass;?>">

<thead>
<tr>
<th class="" id="">
<?php  echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ALL_PROJECTS', 'v.name', $this->sortDirection, $this->sortColumn) ; ?>
</th>
<th class="" id="">
<?php echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_GLOBAL_IMAGE', 'v.picture', $this->sortDirection, $this->sortColumn); ?>
</th>

<th class="" id="">
<?php echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ALL_PROJECTS_LEAGUE_NAME', 'l.name', $this->sortDirection, $this->sortColumn); ?>
</th> 
<th class="" id="">
<?php echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_ALL_PROJECTS_SEASON', 's.name', $this->sortDirection, $this->sortColumn); ?>
</th> 
                 
<th class="" id="">
<?php echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_EDIT_CLUBINFO_COUNTRY', 'v.country', $this->sortDirection, $this->sortColumn); ?>
</th>                                 
                
	</tr>
		</thead>




<?php foreach ($this->items as $i => $item) : ?>
<tr class="row<?php echo $i % 2; ?>">
<td>
<?php 
if ( $item->slug )
{
$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $item->slug;
$routeparameter['type'] = 0;
$routeparameter['r'] = 0;
$routeparameter['from'] = 0;
$routeparameter['to'] = 0;
$routeparameter['division'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking',$routeparameter);    
echo JHtml::link( $link, $item->name );
}
else
{
echo $item->name;    
}

if ( !JFile::exists(JPATH_SITE.DS.$item->picture) )
{
$item->picture = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
}

?>
</td>
<td>
<?PHP 
echo sportsmanagementHelperHtml::getBootstrapModalImage('allproject'.$item->id,$item->picture,$item->name,'20')
?>
<td>
<?php echo $item->leaguename; ?>
</td>
<td>
<?php echo $item->seasonname; ?>
</td>
<td>
<?php echo JSMCountries::getCountryFlag($item->country); ?>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<div class="pagination">
	<p class="counter">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</p>
    <p class="counter">
    <?php echo $this->pagination->getResultsCounter(); ?>
    </p>
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>