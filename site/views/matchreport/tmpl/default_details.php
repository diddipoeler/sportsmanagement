<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_details.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<!-- Details-->
<h2>
<?php 
echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_DETAILS'); 
?>
</h2>
<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
<div class="col-md-12">

	<!-- Prev Match-->
	<?php
	if ($this->match->old_match_id > 0)
	{
		?>
		
        
        <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_OLD_MATCH' ); ?></strong>
			<?php echo JHTML :: link(sportsmanagementHelperRoute::getNextMatchRoute( $this->project->slug,$this->match->old_match_id ),$this->oldmatchtext); ?>
            </address>
            
		<?php
	}
	?>
	<!-- Next Match-->
	<?php
	if ($this->match->new_match_id > 0)
	{
		?>
		
        
        <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_NEW_MATCH' ); ?></strong>
			<?php echo JHTML :: link(sportsmanagementHelperRoute::getNextMatchRoute( $this->project->slug,$this->match->new_match_id ),$this->newmatchtext); ?>
            </address>
            
		<?php
	}
	?>	
	<!-- Date -->
	<?php
    $timestamp = strtotime($this->match->match_date);
    if ( $this->config['show_match_date'] )
    {
        if ( $this->match->match_date != '0000-00-00 00:00:00' )
        {
            ?>
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_DATE' ); ?></strong>
			<?php echo JHtml::date($this->match->match_date, JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_GAMES_DATE')); ?>
            </address>
            <?php
        }
        else
        {
            ?>
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_DATE' ); ?></strong>
			<?php echo ''; ?>
            </address>
            <?php
        }
    }
	?>

	<!-- Time -->
	<?php
    if ( $this->config['show_match_time'] )
    {
        if ( $this->match->match_date != '0000-00-00 00:00:00' )
        {
            ?>
             <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_TIME' ); ?></strong>
			<?php echo sportsmanagementHelperHtml::showMatchTime($this->match, $this->config, $this->overallconfig, $this->project); ?>
            </address>
            <?php
        }
        else
        {
            ?>
             <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_TIME' ); ?></strong>
			<?php echo ''; ?>
            </address>
            <?php
        }
	?>

        <!-- present -->
        <?php if ($this->match->time_present > 0): ?>
        
        
         <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_PRESENT' ); ?></strong>
			<?php echo $this->match->time_present; ?>
            </address>
        <?php endif;
    
    }
    ?>

	<!-- match number -->
	<?php
    if ( $this->config['show_match_number'] )
    {
        if ($this->match->match_number > 0): ?>
       
        
        <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_NUMBER' ); ?></strong>
			<?php echo $this->match->match_number; ?>
            </address>
        
        <?php endif;
    }
    ?>
	<!-- playground -->
	<?php
    if ( $this->config['show_match_playground'] )
    {
        if ($this->match->playground_id > 0): ?>
        <?php 
        $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['pgid'] = $this->match->playground_slug;
$playground_link = sportsmanagementHelperRoute::getSportsmanagementRoute('playground',$routeparameter);

        ?>
        
        
        <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_PLAYGROUND' ); ?></strong>
			<?php 
                if ( isset($this->playground->name) )
                    { 
                    echo JHtml::link ($playground_link, $this->playground->name);
                    }
                    else
                    {
                    echo JText::_( 'COM_SPORTSMANAGEMENT_NEXTMATCH_PLAYGROUND_NO_ASSIGN' );    
                    } 
if ( $this->config["show_playground_picture"] )
{
echo sportsmanagementHelperHtml::getBootstrapModalImage('matchpg'.$this->match->playground_id,
$this->match->playground_picture,
$this->match->playground_name,
$this->config['playground_picture_width'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);
}                     
                    ?>
            </address>
        
        <?php endif;
    }
    ?>
	<!-- referees -->
	<?php
    if ( $this->config['show_match_referees'] )
    {    
        if ( $this->matchreferees )
        {
            ?>
                        
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_REFEREE' ); ?></strong>
			<?php 
            
            $first = true;
                    foreach ( $this->matchreferees as $referee ) : 
                    $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['pid'] = $referee->person_slug;
$referee_link = sportsmanagementHelperRoute::getSportsmanagementRoute('referee',$routeparameter);

                        if (!$first) {
                            echo ', ';
                        }
                        $referee_name = sportsmanagementHelper::formatName(null,$referee->firstname,$referee->nickname,$referee->lastname, $this->config["name_format"]);
                        $link = JHtml::link( $referee_link, $referee_name);
                        if ($this->config["show_referee_position"] == 1) $link .= ' ('.$referee->position_name.')';
                        echo $link; 
                        $first = false;
if ( $this->config["show_referee_picture"] )
{
echo sportsmanagementHelperHtml::getBootstrapModalImage('matchreferee'.$referee->id,
$referee->picture,
$referee_name,
$this->config['referee_picture_width'],
'',
$this->modalwidth,
$this->modalheight,
$this->overallconfig['use_jquery_modal']);                        
}                        
                    endforeach;	
            
            ?>
            </address>
                       
            <?php
        }
    }
    ?>
	<!-- crowd -->
	<?php
    if ( $this->config['show_match_crowd'] )
    {
        if ( $this->match->crowd > 0 ): ?>
            
            
            <address>
			<strong><?php echo JText::_( 'COM_SPORTSMANAGEMENT_MATCHREPORT_ATTENDANCES' ); ?></strong>
			<?php echo ': ' . number_format( $this->match->crowd, 0, ',' , '.' ); ?>
            </address>
            
        <?php endif;
    }
    ?>

</div>
</div>
<br/>
