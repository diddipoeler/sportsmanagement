<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tournamentbracket
 * @file       tournamentbracket.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Component\ComponentHelper;


class sportsmanagementModeltournamentbracket extends BaseDatabaseModel
{

function gettournamentbracket($project_id = 0)
  {
  $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $result = array();
echo $project_id;

  /**
         *
         * alle runden
         */
        $query->clear();
        $query->select('*');
        $query->from('#__sportsmanagement_round');
        $query->where('project_id = ' . $project_id);
        $query->where('tournement = 1');
  $query->order('roundcode DESC');
        $db->setQuery($query);
        //$roundresult = $db->loadObjectList('id');
  $roundresult = $db->loadObjectList();

  /**
        $roundresult2 = usort(
            $roundresult, function ($a, $b) {
            $c = $a->roundcode - $b->roundcode;

            return $c;
        }
        );
*/
         Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($roundresult,true).'</pre>'  , '');
  //Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . '<pre>'.print_r($roundresult2,true).'</pre>'  , '');

  $start = 0;
  $startselektion = 1;
  $startergebnisse = 0;
  $ergebnisse = array();
  $mannschaften = array();
  foreach ( $roundresult as $key => $value )
  {
    Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. 'round key <pre>'.print_r($key,true).'</pre>'  , '');
    $query->clear();
    $query->select('*');
        $query->from('#__sportsmanagement_match');
        $query->where('round_id = ' . $value->id);
    $db->setQuery($query);
        $matches = $db->loadObjectList();

    if ( $matches && $startselektion )
    {
$start = $key + 1;
      foreach ( $matches as $keymatch => $valuematch )
  {

        $mannschaften[$key][] = $valuematch->projectteam1_id;
        $mannschaften[$key][] = $valuematch->projectteam2_id;

        if ( !$valuematch->team1_result )
        {
     $ergebnisse[$key][] = '[null, null]';
          }
        elseif ( $valuematch->team1_result_so )
        {
        $ergebnisse[$key][] = '['.$valuematch->team1_result_so.','. $valuematch->team2_result_so.']';
        }
        elseif ( $valuematch->team1_result_ot )
        {
        $ergebnisse[$key][] = '['.$valuematch->team1_result_ot.','. $valuematch->team2_result_ot.']';
        }
         elseif ( $valuematch->team1_result )
        {
        $ergebnisse[$key][] = '['.$valuematch->team1_result.','. $valuematch->team2_result.']';
        }

      }

    Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '.$value->name. '<pre>'.print_r($matches,true).'</pre>'  , '');
      $startselektion = 0;
    }


  }

//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' ergebnisse'. '<pre>'.print_r($ergebnisse,true).'</pre>'  , '');
 // Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' mannschaften'. '<pre>'.print_r($mannschaften,true).'</pre>'  , '');

for($a = $start; $a < sizeof($roundresult); $a++ )
{
Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' variable a'. '<pre>'.print_r($a,true).'</pre>'  , '');

$startroundteams = $a - 1;

foreach($mannschaften[$startroundteams] as $keystartteams => $valuestarteams )
{
//Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' valuestarteams'. '<pre>'.print_r($valuestarteams,true).'</pre>'  , '');

$query->clear();
    $query->select('*');
        $query->from('#__sportsmanagement_match');
        $query->where('round_id = ' . $roundresult[$a]->id );
        $query->where('( projectteam1_id = ' . $valuestarteams .' OR projectteam2_id = '.$valuestarteams .' )' );
    $db->setQuery($query);
          try {
        $singlematch = $db->loadObjectList();
          } catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    Factory::getApplication()->enqueueMessage($msg, 'error'); // commonly to still display that error
Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' '. '<pre>'.print_r($query->dump(),true).'</pre>'  , 'error');
}

foreach ( $singlematch as $keymatch => $valuematch )
  {

        $mannschaften[$a][] = $valuematch->projectteam1_id;
        $mannschaften[$a][] = $valuematch->projectteam2_id;

        if ( !$valuematch->team1_result )
        {
     $ergebnisse[$a][] = '[null, null]';
          }
        elseif ( $valuematch->team1_result_so )
        {
        $ergebnisse[$a][] = '['.$valuematch->team1_result_so.','. $valuematch->team2_result_so.']';
        }
        elseif ( $valuematch->team1_result_ot )
        {
        $ergebnisse[$a][] = '['.$valuematch->team1_result_ot.','. $valuematch->team2_result_ot.']';
        }
         elseif ( $valuematch->team1_result )
        {
        $ergebnisse[$a][] = '['.$valuematch->team1_result.','. $valuematch->team2_result.']';
        }

  if ( $a == sizeof($roundresult) - 1 )
 {
 Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' mannschaften'   , '');
    $tempmannschaften[] = '['.$valuematch->projectteam1_id.','. $valuematch->projectteam2_id.']';
  }
      }






}


}

//if ( $a == sizeof($roundresult) - 1 )
//  {
  Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' mannschaften'. '<pre>'.print_r($tempmannschaften,true).'</pre>'  , '');
  $teamsreturn = '['.implode(",",$tempmannschaften).']';
 // }

krsort($ergebnisse);
Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' ergebnisse'. '<pre>'.print_r($ergebnisse,true).'</pre>'  , '');
$ergebnisreturn = array();
foreach ( $ergebnisse as $key => $value )
  {
  $ergebnisreturn[] = '['.implode(",",$value).']';

}
  Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' ergebnisreturn'. '<pre>'.print_r($ergebnisreturn,true).'</pre>'  , '');
  Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ .' teamsreturn'. '<pre>'.print_r($teamsreturn,true).'</pre>'  , '');

    $result['teams'] = $teamsreturn;
        $result['results'] = '['.implode(",",$ergebnisreturn).']';
  return $result;
  }


}

?>
