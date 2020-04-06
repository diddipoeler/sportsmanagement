<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_irems.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage allpersons
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="allpersons">       
<table class="<?php echo $this->tableclass;?>">

<thead>
<tr>

<?PHP
if ($this->columns ) {
    foreach( $this->columns as $key => $value )
    {
        ?>
        <th class="" id="">
        <?php  echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_FES_ALLPERSONS_'.strtoupper($value), 'v.'.$value, $this->sortDirection, $this->sortColumn); ?>
    </th>
    <?PHP  
    }
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
    if ($item->projectslug ) {
        $routeparameter = array();
        $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
        $routeparameter['p'] = $item->projectslug;
        $routeparameter['tid'] = $item->teamslug;
        $routeparameter['pid'] = $item->slug;
        $link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);  
        echo HTMLHelper::link($link, $item->$value);
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
    echo sportsmanagementHelperHtml::getBootstrapModalImage(
        'allperson'.$item->id, $item->$value, $item->lastname, '20', '', $this->modalwidth,
        $this->modalheight,
        $this->use_jquery_modal
    );
    break;
case 'website':
    echo HTMLHelper::link($item->$value, $item->$value, array( 'target' => '_blank' ));
    break;
case 'birthday':
case 'deathday':
    echo sportsmanagementHelper::convertDate($item->$value, 1);
    break;
case 'position_id':
    echo Text::_($item->position_name);
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
