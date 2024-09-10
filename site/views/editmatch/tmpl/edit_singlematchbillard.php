<?php

echo 'match <pre>'.print_r($this->match,true).'</pre>';
echo 'singlematch <pre>'.print_r($this->singlematches,true).'</pre>';

$starters_home    = sportsmanagementModelMatch::getMatchPersons($this->projectteam1_id, 0, $this->match->id, 'player');
$starters_away    = sportsmanagementModelMatch::getMatchPersons($this->projectteam2_id, 0, $this->match->id, 'player');
echo 'starters_home <pre>'.print_r($starters_home,true).'</pre>';
echo 'starters_away <pre>'.print_r($starters_away,true).'</pre>';


?>
