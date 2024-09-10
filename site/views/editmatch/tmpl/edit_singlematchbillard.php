<?php

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;



echo 'match <pre>'.print_r($this->match,true).'</pre>';
echo 'singlematch <pre>'.print_r($this->singlematches,true).'</pre>';

$starters_home    = sportsmanagementModelMatch::getMatchPersons($this->match->projectteam1_id, 0, $this->match->id, 'player');
$starters_away    = sportsmanagementModelMatch::getMatchPersons($this->match->projectteam2_id, 0, $this->match->id, 'player');
echo 'starters_home <pre>'.print_r($starters_home,true).'</pre>';
echo 'starters_away <pre>'.print_r($starters_away,true).'</pre>';

/** erst einmal 5 spiele */
for ($a=1; $a < 6;$a++)
{
?>
<div class="row">
<?php
$checksinglematch = $this->model->getSingleMatchData($this->match->id,$a);
if ( $checksinglematch )
{
?>
<div class="text-bg-primary p-3">Spiel <?php echo $a; ?> vorhanden</div>
<?php    
}
else
{
    ?>
    <div class="text-bg-danger p-3">Spiel <?php echo $a; ?> nicht vorhanden</div>
    <?php
    //echo 'nicht vorhanden<br>';
    foreach ( $starters_home as $keyhome => $valuehome ) if ( $valuehome->trikot_number == $a)
    {
    foreach ( $starters_away as $keyaway => $valueaway ) if ( $valueaway->trikot_number == $a)
    {
    $insertsinglematch = $this->model->insertSingleMatchData($this->match->id,$a,$valuehome->teamplayer_id, $valueaway->teamplayer_id,$valuehome->projectteam_id, $valueaway->projectteam_id);    
        if ( $insertsinglematch )
        {
            ?>
            <div class="text-bg-success p-3">Spiel <?php echo $a; ?> angelegt</div>
            <?php
            //echo 'spiel angelegt <br>';
        }
        else
        {
            ?>
            <div class="text-bg-danger p-3">Fehler beim Anlegen des Spiels <?php echo $a; ?></div>
            <?php
            //echo 'spiel nicht angelegt <br>';
        }
       
        
    }    
        
        
        
    }
    
    
}
?>
</div>
<?php
}















?>
