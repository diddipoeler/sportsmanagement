<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage roster
 * @file       default_player_tabletennis.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

if ( Factory::getConfig()->get('debug') )
{
        echo __METHOD__ . ' ' . ' ' . __LINE__ . ' ' . ' eventlist <pre>'.print_r($this->rows,true).'</pre>';
}

?>
<div class="container" id="roster_tabletennis">
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="defaultplayers_tabletennis" itemscope itemtype="http://schema.org/SportsTeam">

 <?php
        foreach ( $this->projectpositions as $positions => $position ) if( $position->persontype == 1 )
{
        ?>
        <table class="<?php echo $this->config['table_class']; ?> table-sm nowrap " id="tableplayer_tabletennis<?php echo $position->id;?>" width="100%">
			<?php
			/**
			 *
			 * jetzt kommt die schleife über die positionen
			 */
			foreach ($this->rows as $position_id => $players) if ( $position_id == $position->id )
			{
			$row      = current($players); 
foreach ($players as $row)
				{
					?>
                    <tr class="" width="" onMouseOver="this.bgColor='#CCCCFF'" onMouseOut="this.bgColor='#ffffff'" itemprop="member" itemscope="" itemtype="http://schema.org/Person">
						<?php
                 $playerName = sportsmanagementHelper::formatName(
							null, $row->firstname,
							$row->nickname,
							$row->lastname,
							$this->config["name_format"]
						); 
                        $picture = $row->ppic;      
?>
                            <td class="" width="" nowrap="nowrap">
                              <span itemprop="name" content="<?php echo $playerName;?>"></span> 
                              <span itemprop="birthDate" content="<?php echo $row->birthday;?>"></span>
				   <span itemprop="deathDate" content="<?php echo $row->deathday;?>"></span>
				    <span itemprop="nationality" content="<?php echo JSMCountries::getCountryName($row->country);?>"></span>
                              
								<?PHP
								echo sportsmanagementHelperHtml::getBootstrapModalImage(
									'player' . $row->playerid,
									$picture,
									$playerName,
									$this->config['player_picture_height'],
									'',
									$this->modalwidth,
									$this->modalheight,
									$this->overallconfig['use_jquery_modal'],
                                  'itemprop',
                                  'image'
								);
								?>

                            </td>
							<?php
                                                    
                        
      }             
             
            }
            ?> 
</table>
<?php
}
?>
</div>
</div>