<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_nextmatch.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage nextmatch
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 

//echo '<pre>',print_r($this->teams,true),'</pre><br>';

?>

<!-- Main START -->
<table class="table">
	<?php
	if( $this->config['show_logo'] )
	{
		?>
	<tr class="nextmatch">
		<td class="teamlogo"><?php
			$pic = $this->config['show_picture'];
        
        $picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER.$this->teams[0]->$pic;
        
echo sportsmanagementHelperHtml::getBootstrapModalImage('nextmatch'.$this->teams[0]->id,
$picture,
$this->teams[0]->name,
$this->config['club_logo_width'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal'])
                             
		?>

        </td>
		<td class="vs">&nbsp;</td>
		<td class="teamlogo"><?php

        $picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER.$this->teams[1]->$pic;

echo sportsmanagementHelperHtml::getBootstrapModalImage('nextmatch'.$this->teams[1]->id,
$picture,
$this->teams[1]->name,
$this->config['club_logo_width'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal'])
        
		?>
        
        </td>
	</tr>
	<?php
	}
	if( $this->config['show_team_picture'] )
	{
?>
	<tr class="nextmatch">
		<td class="teampicture">
			<?php
        
        $picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER.$this->teams[0]->projectteam_picture;
        
echo sportsmanagementHelperHtml::getBootstrapModalImage('nextmatch_team'.$this->teams[0]->id,
$picture,
$this->teams[0]->name,
$this->config['team_picture_width'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal'])
                             
		?>

        </td>
		<td class="vs">&nbsp;</td>
		<td class="teampicture"><?php

        $picture = COM_SPORTSMANAGEMENT_PICTURE_SERVER.$this->teams[1]->projectteam_picture;

echo sportsmanagementHelperHtml::getBootstrapModalImage('nextmatch_team'.$this->teams[1]->id,
$picture,
$this->teams[1]->name,
$this->config['team_picture_width'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal'])
        
		?>
        
        </td>
	</tr>
	<?php		
	}
	?>
	<tr class="nextmatch">
		<td class="team"><?php
		if ( !is_null ( $this->teams ) )
		{
			echo $this->teams[0]->name;
		}
		else
		{
			echo JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_UNKNOWNTEAM" );
		}
		?></td>
		<td class="vs"><?php
		echo JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_VS" );
		?></td>
		<td class="team"><?php
		if ( !is_null ( $this->teams ) )
		{
			echo $this->teams[1]->name;
		}
		else
		{
			echo JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_UNKNOWNTEAM" );
		}
		?></td>
	</tr>
</table>

	<?php 
	$routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->id;
$routeparameter['mid'] = $this->match->id;
$report_link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter);
        
					
        if(isset($this->match->team1_result) && isset($this->match->team2_result))
            { ?>
			<div class="notice">
			<?php 
                $text = JText::_( "COM_SPORTSMANAGEMENT_NEXTMATCH_ALREADYPLAYED" );
                echo JHtml::link( $report_link, $text );
			?>
			</div>
			<?php 
            } ?>
                
<br />
