<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default_treetonode.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage treetonode
 */

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
//echo '<pre>'.print_r($this->node,true).'</pre>';
$style  = 'style="background-color: '.$this->config['tree_bg_colour'].';';
$style .= 'border: 1px solid  '.$this->config['tree_border_colour'].';';
$style .= 'font-weight: bold; white-space:nowrap;';
$style .= 'font-size: '.$this->config['tree_field_fontsize'].'pt;';
$style .= 'width: '.$this->config['tree_field_width'].'px;';
$style .= 'font-family: verdana; ';
$style .= 'text-align: left; padding-left: 10px;"';

	if($this->config['tree_bracket_type']==0){
		$path = 'media/com_sportsmanagement/treebracket/onwhite/';
	}
	elseif($this->config['tree_bracket_type']==1){
		$path = 'media/com_sportsmanagement/treebracket/onblack/';
	}

$treedl = 'treedl.gif';
$treeul = 'treeul.gif';
$treecl = 'treecl.gif';
$treedr = 'treedr.gif';
$treeur = 'treeur.gif';
$treecr = 'treecr.gif';
$treep = 'treep.gif';
$treeh = 'treeh.gif';

$dl = '<img src="'.$path.$treedl.'" alt="" width="16" height="30">';
$ul = '<img src="'.$path.$treeul.'" alt="" width="16" height="30">';
$cl = '<img src="'.$path.$treecl.'" alt="" width="16" height="30">';
$dr = '<img src="'.$path.$treedr.'" alt="" width="16" height="30">';
$ur = '<img src="'.$path.$treeur.'" alt="" width="16" height="30">';
$cr = '<img src="'.$path.$treecr.'" alt="" width="16" height="30">';
$p = '<img src="'.$path.$treep.'" alt="" width="16" height="30">';
$h = '<img src="'.$path.$treeh.'" alt="" width="16" height="30">';


if(!$this->node)
{
    echo Text::_( 'COM_SPORTSMANAGEMENT_TREETONODE_GENERATE_THE_TREE' );
}
else
{
	$i = $this->node[0]->tree_i;		//depth
	$hide = $this->node[0]->hide;		//hide	
	
$r = 2*(pow(2,$i)); 			//rows
$c = 2*$i+1;        			//columns
$col_hide = $c-2*$hide;			//hiden col
?>

<table class="table table-responsive">
<?php
//headerline
if( $this->config['show_treeheader'] == 1 )
{
?>    
<tr style="text-align: center;">
<td align="middle">
</td>
<?PHP    
		for($h=0;$h<=$c;$h++)
        {
 
				if( ( $h%2 != 0 ) && ( $h > 2 ) )
				{
				 ?>            
<td align="middle" colspan="2">
<?PHP    
					$hed = (($h-1)/2)-1;
					//roundname
					if( $this->config['show_name_from'] == 0 )
                    {
						echo $this->roundname[$hed]->name;
					}
					else{
						echo $this->config['tree_name_'.$hed];
					}
					//roundname end
					//date
					if( $this->config['show_round_date'] )
                    {
						echo '<br/>';
						$date1 = Factory::getDate($this->roundname[$hed]->round_date_first)->format('d-m-Y');
						$date2 = Factory::getDate($this->roundname[$hed]->round_date_last )->format('d-m-Y');
						if( $date1 == $date2 )
                        {
							echo $date1;
						}
						else
                        {
							echo Factory::getDate($this->roundname[$hed]->round_date_first)->format('d');
							echo '&divide;';
							echo Factory::getDate($this->roundname[$hed]->round_date_last )->format('d-m-Y');
						}
					}
					//date end
?>	
</td>
<?PHP                     
				}
 
		}
?>
</tr>
<?PHP  
}
//headerline end

	for($j=1;$j<$r;$j++)
	{
	if($this->node[$j-1]->published ==0)
	{
		;
	}
	else
	{
	?>   
		<tr>
		<td height=30></td>
	<?PHP
    	for($k=1;$k<=$c;$k++)
		{
			if($k > $col_hide)
			{
				;
			}
			else
			{
			echo '<td ';
			for($w=0;$w<=$i;$w++)
			{
				if(( $k == (1+($w*2)) ) && ( $j % (2*(pow(2,$w))) == (pow(2,$w)) ))
				{
						echo "$style";
				}
			}
			echo ' >';
			
			for($w=0;$w<=$i;$w++)
			{
				if(( $k == (1+($w*2)) ) && ( $j % (2*(pow(2,$w))) == (pow(2,$w))))
				{
// node __________________________________________________________________________________________________
	if( $this->node[$j-1]->is_leaf )
    {
		if( $this->config['show_overlib_seed'] == 0 )
        {
			;
		}
		sportsmanagementHelper::showClubIcon($this->node[$j-1],$this->config['show_logo_small_flag_leaf']);
		echo ' ';
	}
	else{
		sportsmanagementHelper::showClubIcon($this->node[$j-1],$this->config['show_logo_small_flag']);
		echo ' ';
	}
	if($this->node[$j-1]->is_leaf)
    {
		if ( ($this->config['name_team_type_leaf']==0) && $this->node[$j-1]->short_name )
        {
			echo $this->node[$j-1]->short_name;
		}
		elseif(($this->config['name_team_type_leaf']==1) && $this->node[$j-1]->middle_name )
        {
			echo $this->node[$j-1]->middle_name;
		}
		elseif(($this->config['name_team_type_leaf']==2)&& $this->node[$j-1]->team_name )
        {
			echo $this->node[$j-1]->team_name;
		}
	}
	else{
		if (($this->config['name_team_type']==0)&& $this->node[$j-1]->short_name )
        {
			echo $this->node[$j-1]->short_name;
		}
		elseif(($this->config['name_team_type']==1)&& $this->node[$j-1]->middle_name )
        {
			echo $this->node[$j-1]->middle_name;
		}
		elseif(($this->config['name_team_type']==2)&& $this->node[$j-1]->team_name )
        {
			echo $this->node[$j-1]->team_name;
		}
	}
	
if ( $this->node[$j-1]->match_id )
{
$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = Factory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = Factory::getApplication()->input->getInt('p',0);
$routeparameter['mid'] = $this->node[$j-1]->match_id;
$report_link = sportsmanagementHelperRoute::getSportsmanagementRoute('matchreport',$routeparameter); 
?>    
<a href='<?php echo $report_link ; ?>'>
<img src='<?php echo Uri::root(); ?>components/com_sportsmanagement/assets/images/history-icon-png--21.png'
width='20'
alt='<?php echo Text::_( 'COM_SPORTSMANAGEMENT_RESULTS_SHOW_MATCHREPORT' ); ?>'
title='<?php echo Text::_( 'COM_SPORTSMANAGEMENT_RESULTS_SHOW_MATCHREPORT' ); ?>'>
</a>
<?php					
}					
					
					
					
	if(($this->config['show_overlib_seed']==0)&& $this->node[$j-1]->is_leaf )
    {
		;
	}
	else{
	;
	}
// node end_________________________________________________________________________________________________
			}
				elseif(( $k == (2+($w*2)) ) && ( $j % (4*(pow(2,$w))) == (pow(2,$w)) ))
				{
					echo "$dl";
				}
				elseif(( $k == (2+($w*2)) ) && ( $j % (4*(pow(2,$w))) == (2*(pow(2,$w))) ))
				{
					if($this->node[$j-1]->is_leaf == 1)
					{
						;
					}
					else
					{
						echo "$cl";
					}
				}
				elseif(( $k == (2+($w*2)) ) && ( $j % (4*(pow(2,$w))) == (3*(pow(2,$w))) ))
				{
					echo "$ul";
				}
				elseif(( $k == (2+($w*2)) ) && ( ( $j % (4*(pow(2,$w))) > (pow(2,$w)) ) && ( $j % (4*(pow(2,$w))) < (3*(pow(2,$w))) ) ))
				{
					echo "$p";
				}
				else
				{
					;
				}
			}
            ?>
			</td>
            <?PHP
		}
		}
        ?>
		</tr>
        <?PHP
	}
	}
?>

</table>

<?php
}
?>
