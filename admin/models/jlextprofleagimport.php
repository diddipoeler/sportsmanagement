<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       jlextprofleagimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log;
use Joomla\Utilities\ArrayHelper;

$option = Factory::getApplication()->input->getCmd('option');
$maxImportTime = ComponentHelper::getParams($option)->get('max_import_time', 0);
if (empty($maxImportTime)) {
    $maxImportTime=480;
}
if ((int)ini_get('max_execution_time') < $maxImportTime) {@set_time_limit($maxImportTime);
}

$maxImportMemory = ComponentHelper::getParams($option)->get('max_import_memory', 0);
if (empty($maxImportMemory)) {
    $maxImportMemory = '350M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory) {@ini_set('memory_limit', $maxImportMemory);
}

require_once JPATH_COMPONENT_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'SofeeXmlParser.php';


jimport('joomla.html.pane');
jimport('joomla.utilities.utility');


/**
 * sportsmanagementModeljlextprofleagimport
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModeljlextprofleagimport extends BaseDatabaseModel
{
    var $_datas =array();
    var $_league_id = 0;
    var $_season_id = 0;
    var $_sportstype_id = 0;
    var $import_version = '';
    var $debug_info = false;

    /**
 * sportsmanagementModeljlextprofleagimport::__construct()
 *
 * @return void
 */
    function __construct( )
    {
        parent::__construct($config);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmapp = Factory::getApplication();
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
         $show_debug_info = ComponentHelper::getParams($this->jsmoption)->get('show_debug_info', 0);
        if ($show_debug_info ) {
            $this->debug_info = true;
        }
        else
          {
            $this->debug_info = false;
        }


    }

    /**
  * sportsmanagementModeljlextprofleagimport::_getXml()
  *
  * @return
  */
    function _getXml()
    {
        if (File::exists(JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sportsmanagement_import.xml')) {
            if (function_exists('simplexml_load_file')) {
                return @simplexml_load_file(JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sportsmanagement_import.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
            }
            else
            {
                Log::add(Text::_('<a href="http://php.net/manual/en/book.simplexml.php" target="_blank">SimpleXML</a> does not exist on your system!'));
            }
        }
        else
        {
            Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_LMO_ERROR', 'Missing import file'));
            echo "<script> alert('".Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_LMO_ERROR', 'Missing import file')."'); window.history.go(-1); </script>\n";
        }
    }

    /**
     * Method to get the record form.
     *
     * @param  array   $data     Data for the form.
     * @param  boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return mixed    A JForm object on success, false on failure
     * @since  1.7
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->jsmoption.'.'.$this->name, $this->name,
            array('load_data' => $loadData)
        );
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed    The data for the form.
     * @since  1.7
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState($this->jsmoption.'.edit.'.$this->name.'.data', array());
        if (empty($data)) {
            $data = $this->getData();
        }
        return $data;
    }

    /**
 * sportsmanagementModeljlextprofleagimport::xml2assoc()
 *
 * @param  mixed $xml
 * @param  mixed $target
 * @return
 */
    function xml2assoc($xml, array &$target = array())
    {
        while ($xml->read()) {
            switch ($xml->nodeType) {
            case XMLReader::END_ELEMENT:
                return $target;
            case XMLReader::ELEMENT:
                $name = $xml->name;
                $target[$name] = $xml->hasAttributes ? array() : '';
                if (!$xml->isEmptyElement) {
                    $target[$name] = array();
                    self::xml2assoc($xml, $target[$name]);
                }

                if ($xml->hasAttributes) {
                    while($xml->moveToNextAttribute()) {
                        $target[$name]['@'.$xml->name] = $xml->value;
                    }
                }
                break;
            case XMLReader::TEXT:
            case XMLReader::CDATA:
                $target = $xml->value;
            }
        }
        return $target;
    }


    /**
 * sportsmanagementModeljlextprofleagimport::DumpStructure()
 *
 * @param  mixed $structure
 * @param  mixed $positions
 * @param  mixed $path
 * @return void
 */
    Function DumpStructure(&$structure,&$positions,$path)
    {
        echo "[".$positions[$path]["Line"].",".$positions[$path]["Column"].",".$positions[$path]["Byte"]."]";
        if(GetType($structure[$path])=="array") {
            echo "&lt;".$structure[$path]["Tag"];
            if(IsSet($structure[$path]["Attributes"])) {
                $attributes = $structure[$path]["Attributes"];
                $ta = count($attributes);
                for(Reset($attributes), $a = 0; $a < $ta; Next($attributes), ++$a)
                {
                    $attribute = Key($attributes);
                    echo " ", $attribute, "=\"", HtmlSpecialChars($attributes[$attribute]), "\"";
                }
            }
            echo "&gt;";
            for($element=0;$element<$structure[$path]["Elements"];$element++) {
                self::DumpStructure($structure, $positions, $path.",$element");
            }
            echo "&lt;/".$structure[$path]["Tag"]."&gt;";
        }
        else {
            echo $structure[$path];
        }
    }




          
    /**
 * sportsmanagementModeljlextprofleagimport::getData()
 *
 * @return
 */
    function getData()
    {
        $option = Factory::getApplication()->input->getCmd('option');

          $app = Factory::getApplication();
          $document    = Factory::getDocument();

          $lang = Factory::getLanguage();
          $teile = explode("-", $lang->getTag());

          $post = Factory::getApplication()->input->post->getArray(array());
          $country = $post['country'];
          //$country = JSMCountries::convertIso2to3($teile[1]);

          $app->enqueueMessage(Text::_('land '.$country.''), '');

          $option = Factory::getApplication()->input->getCmd('option');
         $project = $app->getUserState($option . 'project', 0);
  
      
  

          $temp = new stdClass();
          $temp->id = 1;
          $temp->name = 'COM_SPORTSMANAGEMENT_ST_SOCCER';
          $this->_datas['sportstype'] = $temp;

        $file = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sportsmanagement_import.xml';

  



        $xml = new SofeeXmlParser();
        $xml->parseFile($file);
        $tree = $xml->getTree();
        unset($xml);

        $app->setUserState($option.'pltree', $tree);

        $temp = new stdClass();
        $temp->name = $tree[tournament][title][value];
        $this->_datas['exportversion'] = $temp;

        $temp = new stdClass();
        $temp->name = $tree[tournament][season][value];
        $this->_datas['season'] = $temp;

        $temp = new stdClass();
        $temp->name = $tree[tournament][title][value];
        $temp->alias = $tree[tournament][title][value];
        $temp->short_name = $tree[tournament][title][value];
        $temp->middle_name = $tree[tournament][title][value];
        $temp->country = $country;
        $this->_datas['league'] = $temp;

        $temp = new stdClass();
        $temp->name = $tree[tournament][title][value].' '.$tree[tournament][season][value];
        $temp->serveroffset = 0;
        $temp->project_type = 'SIMPLE_LEAGUE';
        $temp->current_round_auto = '2';
        $temp->auto_time = '2880';
        $temp->start_date = '2013-08-08';
        $temp->start_time = '15:30';
        $temp->game_regular_time = '90';
        $temp->game_parts = '2';
        $temp->halftime = '15';
        $temp->points_after_regular_time = '3,1,0';
        $temp->use_legs = '0';
        $temp->allow_add_time = '0';
        $temp->add_time = '30';
        $temp->points_after_add_time = '3,1,0';
        $temp->points_after_penalty = '3,1,0';
        $this->_datas['project'] = $temp;

        // spieler als personen anlegen
        $lfdnummerperson = 1;
        for($a=0; $a < sizeof($tree[tournament][player]); $a++ )
        {
            //echo $tree[tournament][player][$a][id].'<br>';
            $temp = new stdClass();
            $tempexportplayer[$tree[tournament][player][$a][id]] = $lfdnummerperson;

            //$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][player][$a][id] );
            //$tempexportplayer[$tree[tournament][player][$a][id]] = $temp->id;
            $temp->id = $lfdnummerperson;
            $temp->plid = $tree[tournament][player][$a][id];
            $temp->lastname = $tree[tournament][player][$a][lastName];
            $temp->firstname = $tree[tournament][player][$a][firstName];
            $temp->birthday = $tree[tournament][player][$a][dateOfBirth][date][value];
            if (!$temp->birthday ) {
                $temp->birthday = '0000-00-00';  
            }
            $temp->country = $country;
            $temp->show_persdata = 1;
            $temp->show_teamdata = 1;
            $temp->show_on_frontend = 1;
            $exportplayer[] = $temp;
            $this->_checkplayer[] = $temp;
            $lfdnummerperson++;
        }

        // trainer als personen anlegen
        for($a=0; $a < sizeof($tree[tournament][coach]); $a++ )
        {
            //echo $tree[tournament][player][$a][id].'<br>';
            $temp = new stdClass();
            $tempexportplayer[$tree[tournament][coach][$a][id]] = $lfdnummerperson;
            //$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][coach][$a][id] );
            //$tempexportplayer[$tree[tournament][coach][$a][id]] = $temp->id;
            $temp->id = $lfdnummerperson;
            $temp->plid = $tree[tournament][coach][$a][id];
            $temp->lastname = $tree[tournament][coach][$a][lastName];
            $temp->firstname = $tree[tournament][coach][$a][firstName];
            $temp->birthday = $tree[tournament][coach][$a][dateOfBirth][date][value];
            if (!$temp->birthday ) {
                $temp->birthday = '0000-00-00';  
            }
            $temp->country = $country;
            $temp->show_persdata = 1;
            $temp->show_teamdata = 1;
            $temp->show_on_frontend = 1;
            $temp->position_id = 2000;
            $exportplayer[] = $temp;
            $exportpositiontemp[2000] = 2000;
            $lfdnummerperson++;
        }

        // schiedsrichter als personen anlegen
        for($a=0; $a < sizeof($tree[tournament][referee]); $a++ )
        {
            //echo $tree[tournament][player][$a][id].'<br>';
            $temp = new stdClass();
            //$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][player][$a][id] );
            $temp->id = $lfdnummerperson;
            $temp->plid = $tree[tournament][referee][$a][id];
            $temp->lastname = $tree[tournament][referee][$a][lastName];
            $temp->firstname = $tree[tournament][referee][$a][firstName];
            $temp->location = $tree[tournament][referee][$a][location];
            $temp->birthday = $tree[tournament][referee][$a][dateOfBirth][date][value];
            if (!$temp->birthday ) {
                $temp->birthday = '0000-00-00';  
            }
            $temp->country = $country;
            $temp->show_persdata = 1;
            $temp->show_teamdata = 1;
            $temp->show_on_frontend = 1;
            $temp->position_id = 1000;
            $exportplayer[] = $temp;

            $temp = new stdClass();
            //$temp->id = preg_replace ( "![^0-9]+!", "", $tree[tournament][referee][$a][id] );
            $tempexportreferee[$tree[tournament][referee][$a][id]] = $lfdnummerperson;
            //$tempexportreferee[$tree[tournament][referee][$a][id]] = $temp->id;
            $temp->id = $lfdnummerperson;
            $temp->plid = $tree[tournament][referee][$a][id];
            $temp->person_id = $lfdnummerperson;
            $temp->project_position_id = 1000;
            $temp->published = 1;
            $exportreferee[] = $temp;
            $exportpositiontemp[1000] = 1000;
            $lfdnummerperson++;
        }

        /**
 * teams, teamplayer, playgrounds bearbeiten
 */
        $lfdnummerteamperson = 1;
        $lfdnummerteam = 1;
        for($a=0; $a < sizeof($tree[tournament][team]); $a++ )
        {
            $temp = new stdClass();
            $temp->id = preg_replace("![^0-9]+!", "", $tree[tournament][team][$a][id]);
            $temp->plid = $tree[tournament][team][$a][id];
            $temp->name = $tree[tournament][team][$a][name];
            $temp->country = $country;
            $temp->standard_playground = preg_replace("![^0-9]+!", "", $tree[tournament][team][$a][id]);
            $exportclubs[] = $temp;

            $temp = new stdClass();
            $temp->id = preg_replace("![^0-9]+!", "", $tree[tournament][team][$a][id]);
            $temp->plid = $tree[tournament][team][$a][id];
            $temp->club_id = preg_replace("![^0-9]+!", "", $tree[tournament][team][$a][id]);
            $temp->name = $tree[tournament][team][$a][name];
            $temp->middle_name = $tree[tournament][team][$a][name];
            $temp->short_name = $tree[tournament][team][$a][name];
            $exportteams[] = $temp;

            $temp = new stdClass();
            $temp->id = preg_replace("![^0-9]+!", "", $tree[tournament][team][$a][id]);
            $temp->plid = $tree[tournament][team][$a][id];
            $exportprojectteamtemp[$tree[tournament][team][$a][id]] = $temp->id;
            $temp->team_id = preg_replace("![^0-9]+!", "", $tree[tournament][team][$a][id]);
            $temp->project_team_id = preg_replace("![^0-9]+!", "", $tree[tournament][team][$a][id]);
            $temp->is_in_score = 1;
            $temp->standard_playground = preg_replace("![^0-9]+!", "", $tree[tournament][team][$a][id]);
            $exportprojectteam[] = $temp;

            /**
 * teamplayer
 */
            for($b=0; $b < sizeof($tree[tournament][team][$a][player]); $b++ )
            {
                $temp = new stdClass();
                $tempexportteamplayer[$tree[tournament][team][$a][player][$b][id]] = $lfdnummerteamperson;
                $temp->id= $lfdnummerteamperson;
                $temp->plid= $tree[tournament][team][$a][player][$b][id];
                $temp->projectteam_id = preg_replace("![^0-9]+!", "", $tree[tournament][team][$a][id]);
                $temp->active = 1;
                $temp->person_id = $tempexportplayer[$tree[tournament][team][$a][player][$b][playerRef]];
                //$temp->person_id = preg_replace ( "![^0-9]+!", "", $tree[tournament][team][$a][player][$b][playerRef] );
                $temp->jerseynumber = $tree[tournament][team][$a][player][$b][shirtNumber];
                $temp->plposition = $tree[tournament][team][$a][player][$b][posCode];

                if (!empty($tree[tournament][team][$a][player][$b][posCode]) ) {
                    $temp->project_position_id = $this->getProfLeagPosition($tree[tournament][team][$a][player][$b][posCode], $tree[tournament][team][$a][player][$b][playerRef]);
                }
                else
                {
                    $temp->project_position_id = $this->getProfLeagPosition(900, $tree[tournament][team][$a][player][$b][playerRef]);
                }

                $exportteamplayer[] = $temp;

                // temp player position
                // temp positionen
                if (!empty($tree[tournament][team][$a][player][$b][posCode]) ) {
                    $exportplayerpositiontemp[(string) $tree[tournament][team][$a][player][$b][playerRef]] = (string) $tree[tournament][team][$a][player][$b][posCode];
                    $exportpositiontemp[(string) $tree[tournament][team][$a][player][$b][posCode] ] = (string) $tree[tournament][team][$a][player][$b][posCode];
                }
                else
                {
                    $exportplayerpositiontemp[(string) $tree[tournament][team][$a][player][$b][playerRef]] = 900;
                    $exportpositiontemp[900] = 900;
                }
                // ist die position schon in der tabelle
                $profleagpos = $this->getProfLeagPosition((string) $tree[tournament][team][$a][player][$b][posCode], (string) $tree[tournament][team][$a][player][$b][playerRef]);

                $lfdnummerteamperson++;
            }

            // playground
            $temp = new stdClass();
            $temp->id = preg_replace("![^0-9]+!", "", $tree[tournament][team][$a][id]);
            $temp->plid = $tree[tournament][team][$a][id];
            $temp->name = $tree[tournament][team][$a][venue];
            $temp->country = $country;
            $temp->max_visitors = $tree[tournament][team][$a][capacity];

            if ($temp->name ) {
                $exportplayground[] = $temp;
            }

        }


        // jetzt die spiele
        $lfdnumbermatch = 1;
        $lfdnumberlineup = 1;
        $lfdnumberlineupstaff = 1;
        $lfdnumbermatchevent = 1;
        $countgoals = 0;

        for($a=0; $a < sizeof($tree[tournament][tournamentElement][group][groupRound]); $a++ )
        {

            // spieltage
            $temp = new stdClass();
            $temp->id = $tree[tournament][tournamentElement][group][groupRound][$a][header][shortcut];
            $round_id = $tree[tournament][tournamentElement][group][groupRound][$a][header][shortcut];
            $temp->roundcode = $tree[tournament][tournamentElement][group][groupRound][$a][header][shortcut];
            $temp->name = $tree[tournament][tournamentElement][group][groupRound][$a][header][title];
            $exportround[] = $temp;

            // paarung
            for($b=0; $b < sizeof($tree[tournament][tournamentElement][group][groupRound][$a][round][match]); $b++ )
            {
                $temp = new stdClass();
                $temp->id = $lfdnumbermatch;
                $temp->round_id = $round_id;
                $temp->match_number = $lfdnumbermatch;
                $temp->match_date = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][date] . ' ' . $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][kickoff];
                $temp->summary = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][note][value];
                $temp->count_result = 1;
                $temp->published = 1;
                $temp->show_report = 1;
                $temp->projectteam1_id = preg_replace("![^0-9]+!", "", $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref]);


                if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][coachRef] ) {
                    $tempexportteamstaff[$tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref]] = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][coachRef];

                    $temp2 = new stdClass();
                    $temp2->id = $lfdnumberlineupstaff;
                    $temp2->match_id = $lfdnumbermatch;
                    $temp2->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][coachRef];
                    $temp2->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref];
                    $temp2->project_position_id = 2000;
                    $temp2->team_staff_id = $tempexportplayer[(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][coachRef]];
                    $exportmatchstaff[] = $temp2;
                    $lfdnumberlineupstaff++;

                }

                $temp->projectteam2_id = preg_replace("![^0-9]+!", "", $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref]);

                if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][coachRef] ) {
                    $tempexportteamstaff[$tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref]] = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][coachRef];

                    $temp2 = new stdClass();
                    $temp2->id = $lfdnumberlineupstaff;
                    $temp2->match_id = $lfdnumbermatch;
                    $temp2->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][coachRef];
                    $temp2->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref];
                    $temp2->project_position_id = 2000;
                    $temp2->team_staff_id = $tempexportplayer[(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][coachRef]];
                    $exportmatchstaff[] = $temp2;
                    $lfdnumberlineupstaff++;

                }

                $temp->team1_result = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][result][home];
                $temp->team2_result = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][result][away];
                $exportmatch[] = $temp;

                $countgoals = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][result][home] +
                          $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][result][away];


                /**
 * startaufstellung oder auswechselung
 * heimmannschaft
 */
                for($c=0; $c < sizeof($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry]); $c++ )
                {

                    /**
 * startaufstellung heimmannschaft
 */
                    if (empty($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][type]) ) {
                        $temp = new stdClass();
                        $temp->id = $lfdnumberlineup;
                        $temp->match_id = $lfdnumbermatch;
                        $temp->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];
                        $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref];
                        $temp->teamplayer_id = $tempexportteamplayer[(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef]];

                        //$teile = explode("_",(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef]);
                        //$playerposition = $exportplayerpositiontemp[$teile[1]];
                        $playerposition = (string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][posCode];

                        if (empty($playerposition) ) {
                            $playerposition = 900;
                        }

                        $temp->project_position_id = $this->getProfLeagPosition($playerposition, '');

                        $exportmatchplayer[] = $temp;
                        $lfdnumberlineup++;

                        // hat der spieler karten bekommen ?
                        // gelbe karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'Y' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            $temp->event_type_id = 2;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                        // gelb/rote karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'YR' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            $temp->event_time = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][$d][time][min];
                            $temp->event_type_id = 3;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                        // rote karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'R' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            //$temp->event_time = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][$d][time][min];
                            $temp->event_type_id = 4;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                    }
                    else
                    {
                        /**
 * auswechselung heimmannschaft
 */
                        $temp = new stdClass();
                        $temp->id = $lfdnumberlineup;
                        $temp->match_id = $lfdnumbermatch;
                        $temp->teamplayer_id = $tempexportteamplayer[(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef]];
                        $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref];
                        $inplayer = $temp->teamplayer_id;

                        $teile = explode("_", (string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef]);
                        $playerposition = $exportplayerpositiontemp[$teile[1]];
                        //$playerposition = (string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][posCode];

                        if (empty($playerposition) ) {
                            $playerposition = 900;
                        }

                        $temp->project_position_id = $this->getProfLeagPosition($playerposition, '');
                        $temp->came_in = 1;
                        $temp->out = 0;
                        $temp->in_for = $tempexportteamplayer[ (string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][substitution][substRef] ];
                        $outplayer = $temp->in_for;
                        $temp->in_out_time  = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][substitution][time][min];
                        $exportmatchplayer[] = $temp;
                        $lfdnumberlineup++;

                        // hat der spieler karten bekommen ?
                        // gelbe karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'Y' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            $temp->event_type_id = 2;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                        // gelb/rote karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'YR' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            $temp->event_type_id = 3;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                        // rote karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][penalty][type][value] == 'R' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][homeTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            $temp->event_type_id = 4;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                    }

                    $lfdnumberlineup++;



                }

                // gastmannschaft
                for($c=0; $c < sizeof($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry]); $c++ )
                {

                    // startaufstellung gastmannschaft
                    if (empty($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][type]) ) {
                        $temp = new stdClass();
                        $temp->id = $lfdnumberlineup;
                        $temp->match_id = $lfdnumbermatch;
                        $temp->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];
                        $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref];
                        $temp->teamplayer_id = $tempexportteamplayer[(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef]];

                        //$teile = explode("_",(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef]);
                        //$playerposition = $exportplayerpositiontemp[$teile[1]];
                        $playerposition = (string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][posCode];

                        if (empty($playerposition) ) {
                            $playerposition = 900;
                        }
                        $temp->project_position_id = $this->getProfLeagPosition($playerposition, '');

                        $exportmatchplayer[] = $temp;
                        $lfdnumberlineup++;

                        // hat der spieler karten bekommen ?
                        // gelbe karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'Y' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            $temp->event_type_id = 2;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                        // gelb/rote karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'YR' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }


                            $temp->event_type_id = 3;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                        // rote karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'R' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            $temp->event_type_id = 4;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                    }
                    else
                    {
                        // auswechselung gastmannschaft
                        $temp = new stdClass();
                        $temp->id = $lfdnumberlineup;
                        $temp->match_id = $lfdnumbermatch;
                        $temp->plid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];
                        $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref];
                        $temp->teamplayer_id = $tempexportteamplayer[(string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef]];
                        $inplayer = $temp->teamplayer_id;

                        $teile = explode("_", (string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef]);
                        $playerposition = $exportplayerpositiontemp[$teile[1]];
                        //$playerposition = (string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][posCode];

                        if (empty($playerposition) ) {
                            $playerposition = 900;
                        }

                        $temp->project_position_id = $this->getProfLeagPosition($playerposition, '');
                        $temp->came_in = 1;
                        $temp->out = 0;
                        $temp->in_for = $tempexportteamplayer[ (string) $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][substitution][substRef] ];
                        $outplayer = $temp->in_for;
                        $temp->in_out_time  = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][substitution][time][min];
                        $exportmatchplayer[] = $temp;
                        $lfdnumberlineup++;

                        // hat der spieler karten bekommen ?
                        // gelbe karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'Y' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            $temp->event_type_id = 2;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                        // gelb/rote karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'YR' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            $temp->event_type_id = 3;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                        // rote karte ?
                        if ($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][penalty][type][value] == 'R' ) {
                            $temp = new stdClass();
                            $temp->id = $lfdnumbermatchevent;
                            $temp->match_id = $lfdnumbermatch;
                            $temp->proteamid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][teamRef][ref];
                            $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][awayTeam][teamData][lineup][entry][$c][playerRef];

                            foreach ( $exportteamplayer as $teamplayer)
                            {
                                if ($teamplayer->plid == $tempplid ) {
                                    $temp->projectteam_id = $teamplayer->projectteam_id;
                                    $temp->teamplayer_id = $teamplayer->id;
                                }
                            }

                            $temp->event_type_id = 4;
                            $temp->event_sum = 1;
                            $exportmatchevent[] = $temp;

                            $lfdnumbermatchevent++;

                        }

                    }

                    $lfdnumberlineup++;

                }

                // torschuetzen ?
                if ($countgoals == 1 ) {
                    $temp = new stdClass();
                    $temp->id = $lfdnumbermatchevent;
                    $temp->match_id = $lfdnumbermatch;
                    $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][scorerRef];

                    foreach ( $exportteamplayer as $teamplayer)
                    {
                        if ($teamplayer->plid == $tempplid ) {
                            $temp->projectteam_id = $teamplayer->projectteam_id;
                            $temp->teamplayer_id = $teamplayer->id;
                        }
                    }

                    $temp->event_time = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][time][min];
                    $temp->event_type_id = 1;
                    $temp->event_sum = 1;
                    $exportmatchevent[] = $temp;

                    $lfdnumbermatchevent++;
                }

                if ($countgoals > 1 ) {

                    for($d=0; $d < sizeof($tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score]); $d++ )
                    {
                        $temp = new stdClass();
                        $temp->id = $lfdnumbermatchevent;
                        $temp->match_id = $lfdnumbermatch;
                        $tempplid = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][$d][scorerRef];

                        foreach ( $exportteamplayer as $teamplayer)
                        {
                            if ($teamplayer->plid == $tempplid ) {
                                $temp->projectteam_id = $teamplayer->projectteam_id;
                                $temp->teamplayer_id = $teamplayer->id;
                            }
                        }

                        $temp->event_time = $tree[tournament][tournamentElement][group][groupRound][$a][round][match][$b][score][$d][time][min];
                        $temp->event_type_id = 1;
                        $temp->event_sum = 1;
                        $exportmatchevent[] = $temp;

                        $lfdnumbermatchevent++;
                    }

                }

                $countgoals = 0;
                $lfdnumbermatch++;

            }


        }

        foreach ( $tempexportteamstaff as $key => $value )
        {
            $temp = new stdClass();
            $temp->id = $tempexportplayer[$value];
            $temp->projectteam_id = $exportprojectteamtemp[$key];
            $temp->person_id = $tempexportplayer[$value];
            $temp->project_position_id = 2000;
            $temp->pro_team_id = $key;
            $temp->pro_person_id = $value;
            $exportteamstaff[] = $temp;
        }

        $app->setUserState($option.'tempexportteamplayer', $tempexportteamplayer);
        $app->setUserState($option.'exportplayer', $exportplayer);
        $app->setUserState($option.'exportteamplayer', $exportteamplayer);
        $app->setUserState($option.'exportteamstaff', $exportteamstaff);
        $app->setUserState($option.'exportreferee', $exportreferee);
        $app->setUserState($option.'exportmatchplayer', $exportmatchplayer);
        $app->setUserState($option.'exportmatchstaff', $exportmatchstaff);
        $app->setUserState($option.'exportmatchevent', $exportmatchevent);

        // parentposition
        $temp = new stdClass();
        $temp->id = 1;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_F_PLAYERS';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_F_PLAYERS';
        $temp->published = 1;
        $exportparentposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 2;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_F_COACHES';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_F_COACHES';
        $temp->published = 1;
        $exportparentposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 3;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_F_REFEREES';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_F_REFEREES';
        $temp->published = 1;
        $exportparentposition[] = $temp;

        // ereignisse
        $temp = new stdClass();
        $temp->id = 1;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_E_GOAL';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_E_GOAL';
        $temp->published = 1;
        $exportevent[] = $temp;
        $temp = new stdClass();
        $temp->id = 2;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_E_YELLOW_CARD';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_E_YELLOW_CARD';
        $temp->published = 1;
        $exportevent[] = $temp;
        $temp = new stdClass();
        $temp->id = 3;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_E_YELLOW-RED_CARD';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_E_YELLOW-RED_CARD';
        $temp->published = 1;
        $exportevent[] = $temp;
        $temp = new stdClass();
        $temp->id = 4;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_E_RED_CARD';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_E_RED_CARD';
        $temp->published = 1;
        $exportevent[] = $temp;

        $app->setUserState($option.'exportevent', $exportevent);




        /**
 * PositionEventType generieren
 */
        $lfd_nummer = 1;
        foreach ( $exportevent as $row )
        {
            for($a=1; $a < 5;$a++  )
            {
                $temp = new stdClass();
                $temp->id = $lfd_nummer;
                $temp->position_id = $a;
                $temp->eventtype_id = $row->id;
                $temp->published = 1;
                $exportpositioneventtype[] = $temp;  
            }
        }
        //$selectposition = implode(",",$exportpositiontemp);
        //$exportposition = $this->getPlPosition($selectposition);

        // spieler
        $temp = new stdClass();
        $temp->id = 1;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_P_GOALKEEPER';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_P_GOALKEEPER';
        $temp->parent_id = 1;
        $temp->published = 1;
        $temp->persontype = 1;
        $exportposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 2;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_P_DEFENDER';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_P_DEFENDER';
        $temp->parent_id = 1;
        $temp->published = 1;
        $temp->persontype = 1;
        $exportposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 3;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_P_MIDFIELDER';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_P_MIDFIELDER';
        $temp->parent_id = 1;
        $temp->published = 1;
        $temp->persontype = 1;
        $exportposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 4;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_P_FORWARD';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_P_FORWARD';
        $temp->parent_id = 1;
        $temp->published = 1;
        $temp->persontype = 1;
        $exportposition[] = $temp;

        // trainer
        $temp = new stdClass();
        $temp->id = 2000;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_F_COACH';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_F_COACH';
        $temp->parent_id = 2;
        $temp->published = 1;
        $temp->persontype = 2;
        $exportposition[] = $temp;

        // schiedsrichter
        $temp = new stdClass();
        $temp->id = 1000;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_F_MAIN_REFEREE';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_F_MAIN_REFEREE';
        $temp->parent_id = 3;
        $temp->published = 1;
        $temp->persontype = 3;
        $exportposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 1001;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_F_LINESMAN';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_F_LINESMAN';
        $temp->parent_id = 3;
        $temp->published = 1;
        $temp->persontype = 3;
        $exportposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 1002;
        $temp->name = 'COM_SPORTSMANAGEMENT_SOCCER_F_LINESMAN';
        $temp->alias = 'COM_SPORTSMANAGEMENT_SOCCER_F_LINESMAN';
        $temp->parent_id = 3;
        $temp->published = 1;
        $temp->persontype = 3;
        $exportposition[] = $temp;

        // spieler
        $temp = new stdClass();
        $temp->id = 1;
        $temp->position_id = 1;
        $exportprojectposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 2;
        $temp->position_id = 2;
        $exportprojectposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 3;
        $temp->position_id = 3;
        $exportprojectposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 4;
        $temp->position_id = 4;
        $exportprojectposition[] = $temp;
        // trainer
        $temp = new stdClass();
        $temp->id = 2000;
        $temp->position_id = 2000;
        $exportprojectposition[] = $temp;
        // schiedsrichter
        $temp = new stdClass();
        $temp->id = 1000;
        $temp->position_id = 1000;
        $exportprojectposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 1001;
        $temp->position_id = 1001;
        $exportprojectposition[] = $temp;
        $temp = new stdClass();
        $temp->id = 1002;
        $temp->position_id = 1002;
        $exportprojectposition[] = $temp;


        //$exportprojectposition = $this->getPlProjectPosition($selectposition);

        foreach ($exportplayer as $row)
        {
            $playerposition = $exportplayerpositiontemp[ (string) $row->plid];
            $row->position_id = $this->getProfLeagPosition($playerposition, '');
            $row->picture = 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';
        }

        //$exportpositioneventtype = $this->getPlProjectEventPosition($selectposition);

        $this->_datas['person'] = array_merge($exportplayer);
        $this->_datas['club'] = array_merge($exportclubs);
        $this->_datas['team'] = array_merge($exportteams);
        $this->_datas['teamplayer'] = array_merge($exportteamplayer);
        $this->_datas['teamstaff'] = array_merge($exportteamstaff);
        $this->_datas['projectteam'] = array_merge($exportprojectteam);
        $this->_datas['projectreferee'] = array_merge($exportreferee);

        $this->_datas['projectposition'] = array_merge($exportprojectposition);

        $this->_datas['position'] = array_merge($exportposition);
        $this->_datas['parentposition'] = array_merge($exportparentposition);

        $this->_datas['playground'] = array_merge($exportplayground);
        $this->_datas['round'] = array_merge($exportround);
        $this->_datas['match'] = array_merge($exportmatch);
        $this->_datas['matchplayer'] = array_merge($exportmatchplayer);
        $this->_datas['matchstaff'] = array_merge($exportmatchstaff);
        $this->_datas['matchevent'] = array_merge($exportmatchevent);
        $this->_datas['event'] = array_merge($exportevent);

        $this->_datas['positioneventtype'] = array_merge($exportpositioneventtype);



        /**
 * das ganze für den standardimport aufbereiten
 */
        $output = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        // open the project
        $output .= "<project>\n";
        // set the version of SportsManagement
        $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsManagementVersion());
        // set the project datas
        if (isset($this->_datas['project']) ) {
            $app->enqueueMessage(Text::_('project daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setProjectData($this->_datas['project']));
        }
        // set league data of project
        if (isset($this->_datas['league']) ) {
            $app->enqueueMessage(Text::_('league daten '.'generiert'), '');    
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setLeagueData($this->_datas['league']));
        }
        // set season data of project
        if (isset($this->_datas['season']) ) {
            $app->enqueueMessage(Text::_('season daten '.'generiert'), '');    
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSeasonData($this->_datas['season']));
        }

        // set the rounds sportstype
        if (isset($this->_datas['sportstype']) ) {
            $app->enqueueMessage(Text::_('sportstype daten '.'generiert'), '');    
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setSportsType($this->_datas['sportstype']));  
        }

        // set the rounds data
        if (isset($this->_datas['round']) ) {
            $app->enqueueMessage(Text::_('round daten '.'generiert'), '');    
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['round'], 'Round'));
        }
        // set the teams data
        if (isset($this->_datas['team']) ) {
            $app->enqueueMessage(Text::_('team daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['team'], 'JL_Team'));
        }

        // set the teamplayer data
        if (isset($this->_datas['teamplayer']) ) {
            $app->enqueueMessage(Text::_('teamplayer daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['teamplayer'], 'TeamPlayer'));
        }
        // set the teamstaff data
        if (isset($this->_datas['teamstaff']) ) {
            $app->enqueueMessage(Text::_('teamstaff daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['teamstaff'], 'TeamStaff'));
        }


        // set the clubs data
        if (isset($this->_datas['club']) ) {
            $app->enqueueMessage(Text::_('club daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['club'], 'Club'));
        }
        // set the matches data
        if (isset($this->_datas['match']) ) {
            $app->enqueueMessage(Text::_('match daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['match'], 'Match'));
        }
        // set the positions data
        if (isset($this->_datas['position']) ) {
            $app->enqueueMessage(Text::_('position daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['position'], 'Position'));
        }
        // set the positions parent data
        if (isset($this->_datas['parentposition']) ) {
            $app->enqueueMessage(Text::_('parentposition daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['parentposition'], 'ParentPosition'));
        }
        // set position data of project
        if (isset($this->_datas['projectposition']) ) {
            $app->enqueueMessage(Text::_('projectposition daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectposition'], 'ProjectPosition'));
        }

        // set event data of project
        if (isset($this->_datas['event']) ) {
            $app->enqueueMessage(Text::_('event daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['event'], 'EventType'));
        }

        // set positioneventtype data of project
        if (isset($this->_datas['positioneventtype']) ) {
            $app->enqueueMessage(Text::_('positioneventtype daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['positioneventtype'], 'PositionEventType'));
        }

        // set the matchreferee data
        if (isset($this->_datas['matchreferee']) ) {
            $app->enqueueMessage(Text::_('matchreferee daten '.'generiert'), ''); 
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchreferee'], 'MatchReferee'));
        }


        // set the matchplayer data
        if (isset($this->_datas['matchplayer']) ) {
            $app->enqueueMessage(Text::_('matchplayer daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchplayer'], 'MatchPlayer'));
        }

        // set the matchstaff data
        if (isset($this->_datas['matchstaff']) ) {
            $app->enqueueMessage(Text::_('matchstaff daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchstaff'], 'MatchStaff'));
        }

        // set the matchevent data
        if (isset($this->_datas['matchevent']) ) {
            $app->enqueueMessage(Text::_('matchevent daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['matchevent'], 'MatchEvent'));
        }


        // set the person data
        if (isset($this->_datas['person']) ) {
            $app->enqueueMessage(Text::_('person daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['person'], 'Person'));
        }
        // set the projectreferee data
        if (isset($this->_datas['projectreferee']) ) {
            $app->enqueueMessage(Text::_('projectreferee daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectreferee'], 'ProjectReferee'));
        }
        // set the projectteam data
        if (isset($this->_datas['projectteam']) ) {
            $app->enqueueMessage(Text::_('projectteam daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['projectteam'], 'ProjectTeam'));
        }
        // set playground data of project
        if (isset($this->_datas['playground']) ) {
            $app->enqueueMessage(Text::_('playground daten '.'generiert'), '');  
            $output .= sportsmanagementHelper::_addToXml(sportsmanagementHelper::_setXMLData($this->_datas['playground'], 'Playground'));
        }          
          
        // close the project
        $output .= '</project>';
        // mal als test
        $xmlfile = $output;
        $file = JPATH_SITE.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sportsmanagement_import.jlg';
        File::write($file, $xmlfile);


        if ($this->debug_info ) {
            echo $this->pane->endPane();  
        }

        $this->import_version='NEW';
        //$this->import_version='';
        return $this->_datas;
  
    }

    /**
 * sportsmanagementModeljlextprofleagimport::getProfLeagPosition()
 *
 * @param  mixed $val
 * @param  mixed $posplayer
 * @return
 */
    function getProfLeagPosition($val,$posplayer)
    {

        /**
 * Torwart - Nr. 1-15
 * Abwehr - Nr. 16-95
 * Mittelfeld - Nr. 96-175
 * Sturm - Nr. 176-255
 */

        // Torwart
        if (($val >= 1 && $val <= 15) ) {
            return 1;
        }
        // Abwehr
        if (($val >= 16 && $val <= 95) ) {
            return 2;
        }
        // Mittelfeld
        if (($val >= 96 && $val <= 175) ) {
            return 3;
        }
        // Sturm
        if (($val >= 176 && $val <= 255) ) {
            return 4;
        }
  
    }  
  

  
            
    /**
 * sportsmanagementModeljlextprofleagimport::_loadData()
 *
 * @return
 */
    function _loadData()
    {
          global $app, $option;
          $this->_data =  $app->getUserState($option . 'project', 0);
 
          return $this->_data;
    }

    /**
 * sportsmanagementModeljlextprofleagimport::_initData()
 *
 * @return
 */
    function _initData()
    {
         global $app, $option;
          $this->_data =  $app->getUserState($option . 'project', 0);
          return $this->_data;
    }




}


?>

