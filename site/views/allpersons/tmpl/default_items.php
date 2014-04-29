<?php 
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file components/sportsmanagement/views/allpersons/tmpl/default_items.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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
        
<table width="100%">

<thead>
<tr>

<?PHP
foreach( $this->columns as $key => $value )
{
?>
<th class="" id="">
<?php  echo JHtml::_('grid.sort', 'COM_SPORTSMANAGEMENT_FES_ALLPERSONS_'.strtoupper($value), 'v.'.$value, $this->sortDirection, $this->sortColumn) ; ?>
</th>
<?PHP    
}

?>
</tr>
</thead>


<?php foreach ($this->items as $field => $item) : ?>
<tr class="row<?php echo $i % 2; ?>">

<?php 

foreach( $this->columns as $key => $value )
{
?>
<td>
<?PHP 
switch ($value)
{
    case 'lastname':
    if ( $item->projectslug )
    {
    $link = sportsmanagementHelperRoute::getPlayerRoute( $item->projectslug, $item->teamslug, $item->slug );
    echo JHtml::link( $link, $item->$value );
    }
    else
    {
    echo $item->$value;    
    }
    break;
    case 'country':
    echo JSMCountries::getCountryFlag($item->$value);
    break;
    case 'picture':
    ?>
    <a href="<?php echo $item->$value;?>" title="<?php echo $item->lastname;?>" class="modal">
    <img src="<?php echo $item->$value;?>" alt="<?php echo $item->lastname;?>" width="20" />
    </a>  
    <?PHP 
    break;
    case 'website':
    echo JHtml::link( $item->$value, $item->$value, array( 'target' => '_blank' ) );
    break;
    default:
    echo $item->$value;
    break;

    
}
?>
</td>
<?PHP 

}

?>

</tr>
<?php endforeach; ?>
</table>


<div class="pagination">
	<p class="counter">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</p>
    <p class="counter">
    <?php echo $this->pagination->getResultsCounter(); ?>
    </p>
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>