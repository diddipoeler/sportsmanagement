<?php

echo 'match <pre>'.print_r($this->match,true).'</pre>';
echo 'singlematch <pre>'.print_r($this->singlematches,true).'</pre>';

$starters_home    = sportsmanagementModelMatch::getMatchPersons($this->match->projectteam1_id, 0, $this->match->id, 'player');
$starters_away    = sportsmanagementModelMatch::getMatchPersons($this->match->projectteam2_id, 0, $this->match->id, 'player');
echo 'starters_home <pre>'.print_r($starters_home,true).'</pre>';
echo 'starters_away <pre>'.print_r($starters_away,true).'</pre>';

/** erst einmal 5 spiele */
for ($a=1; $a < 6;$a++)
{

$checksinglematch = $this->model->getSingleMatchData($this->match->id,$a);
if ( $checksinglematch )
{
    
}
else
{
    echo 'nicht vorhanden<br>';
}

}

?>
