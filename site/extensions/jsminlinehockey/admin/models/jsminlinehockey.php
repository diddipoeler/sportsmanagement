<?php



// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
jimport( 'joomla.application.component.model' );


$maxImportTime=480;

if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}


$maxImportMemory='350M';
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){@ini_set('memory_limit',$maxImportMemory);}

class sportsmanagementModeljsminlinehockey extends JModelLegacy
{

var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';
     static public $success_text = '';
    var $success_text_teams = '';
    var $success_text_results = '';
    
    var $teamart = '';
    var $country = '';
    var $project_type = '';
    var $season_id = 0;
    var $teams = array();
    var $rounds = array();
    var $divisions = array();
    var $matches = array();
    var $projectteams = array();




function __construct()
	{
		$mainframe = JFactory::getApplication();
        
        if($mainframe->isAdmin()) 
{

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'libraries'.DS.'PHPExcel'.DS.'PHPExcel.php');
}
        parent::__construct();
        }
        
function getClubs()
{
$app = JFactory::getApplication ();
$jinput = $app->input;
$option = $jinput->getCmd('option');
$db = JFactory::getDbo();
$query = $db->getQuery(true);

$username = JComponentHelper::getParams($option)->get('ishd_benutzername');;
$password = JComponentHelper::getParams($option)->get('ishd_kennwort');;
$url_clubs = 'https://www.ishd.de/licenses/clubs.xml';

/*
$context = stream_context_create(array(
    'http' => array(
        'header' => "Authorization: Basic " . base64_encode("$username:$password")
    )
));
$data = file_get_contents($url, false, $context);
$xml = simplexml_load_string($data);
*/


$curl = curl_init($url_clubs);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($curl, CURLOPT_HEADER, 1);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_USERPWD, $username.':'.$password );
$result = curl_exec($curl);
$code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);

//$xml = JFactory::getXML($result,true); 
$xml = simplexml_load_string($result );

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' url      <br><pre>'.print_r($url,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' username <br><pre>'.print_r($username ,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' password <br><pre>'.print_r($password ,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data <br><pre>'.print_r($data ,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xml <br><pre>'.print_r($xml ,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' code <br><pre>'.print_r($code ,true).'</pre>'),'');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result <br><pre>'.print_r($result ,true).'</pre>'),'');

foreach( $xml->children() as $quote )  
{ 
$club_id = (string)$quote->club_id;
$club_name = (string)$quote->club_name;
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name<br><pre>'.print_r($club_name,true).'</pre>'),'');

// Select some fields 
$query->clear(); 
$query->select('id'); 
// From the table 
$query->from('#__sportsmanagement_club'); 
$query->where('id = '.$club_id ); 
$db->setQuery($query); 
if ( !$db->loadResult() ) 
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name nicht vorhanden<br><pre>'.print_r($club_name,true).'</pre>'),'');
// Create and populate an object.
$profile = new stdClass();
$profile->id = $club_id;
$profile->name = $club_name;
$profile->alias = JFilterOutput::stringURLSafe( $club_name );;
 
// Insert the object into the user profile table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_club', $profile);
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name angelegt<br><pre>'.print_r($club_name,true).'</pre>'),'');
}
else
{
$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name vorhanden<br><pre>'.print_r($club_name,true).'</pre>'),'Notice');
}

}

}


function getdata()
{
    	$app = JFactory::getApplication ();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
		$document = JFactory::getDocument ();
		// Check for request forgeries
		//JRequest::checkToken () or die ( 'COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN' );
		$msg = '';
        $post = $jinput->post->getArray(array());
        
        	if (JFile::exists(JPATH_SITE.DS.'tmp'.DS.'ish_bw_import.xls'))
		{
		  echo date('H:i:s') , " Load workbook from Excel5 file" , EOL;
$callStartTime = microtime(true);
		  $objPHPExcel = PHPExcel_IOFactory::load(JPATH_SITE.DS.'tmp'.DS.'ish_bw_import.xls');
          //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' objPHPExcel<br><pre>'.print_r($objPHPExcel,true).'</pre>'),'');
          
/**
* heimmannschaft auslesen
*/          
          for($a=5;$a <= 22;$a++)
          {
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('H'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue();
		$temp->pass = $objPHPExcel->getActiveSheet()->getCell('N'.$a)->getValue();
		$export[] = $temp;
          //echo 'getCellValue <pre>'.print_r($objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue(),true).'</pre>';
         }
         
         $this->_datas['homeplayer'] = array_merge($export);
          
          unset($export);

/**
* gastmannschaft auslesen
*/
          
          for($a=34;$a <= 51;$a++)
          {
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('H'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue();
		$temp->pass = $objPHPExcel->getActiveSheet()->getCell('N'.$a)->getValue();
		$export[] = $temp;
          //echo 'getCellValue <pre>'.print_r($objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue(),true).'</pre>';
         }
          $this->_datas['awayplayer'] = array_merge($export);
          
/**
* schiedsrichter auslesen
*/          
          
          unset($export);
//          for($a=22;$a <= 31;$a + 3)
//          {
            $a=22;
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('B'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('D'.$a)->getValue();
		$temp->vorname = $objPHPExcel->getActiveSheet()->getCell('E'.$a)->getValue();
		$export[] = $temp;
            $a=25;
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('B'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('D'.$a)->getValue();
		$temp->vorname = $objPHPExcel->getActiveSheet()->getCell('E'.$a)->getValue();
		$export[] = $temp;
        
        $a=28;
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('B'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('D'.$a)->getValue();
		$temp->vorname = $objPHPExcel->getActiveSheet()->getCell('E'.$a)->getValue();
		$export[] = $temp;
        
        $a=31;
            $temp = new stdClass();
            
    $temp->nummer = $objPHPExcel->getActiveSheet()->getCell('B'.$a)->getValue();
		$temp->name = $objPHPExcel->getActiveSheet()->getCell('D'.$a)->getValue();
		$temp->vorname = $objPHPExcel->getActiveSheet()->getCell('E'.$a)->getValue();
		$export[] = $temp;
          //echo 'getCellValue <pre>'.print_r($objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue(),true).'</pre>';
//         }
         
         $this->_datas['referees'] = array_merge($export);
          
         unset($export);
          for($a=5;$a <= 25;$a++)
          {
            $temp = new stdClass();
            
    $temp->time = $objPHPExcel->getActiveSheet()->getCell('O'.$a)->getValue();
		$temp->g_nummer = $objPHPExcel->getActiveSheet()->getCell('P'.$a)->getValue();
		$temp->a_nummer = $objPHPExcel->getActiveSheet()->getCell('Q'.$a)->getValue();
        $temp->t_nummer = $objPHPExcel->getActiveSheet()->getCell('R'.$a)->getValue();
		$export[] = $temp;
          //echo 'getCellValue <pre>'.print_r($objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue(),true).'</pre>';
         }
          $this->_datas['homegoals'] = array_merge($export);
          
           unset($export);
          for($a=34;$a <= 51;$a++)
          {
            $temp = new stdClass();
            
    $temp->time = $objPHPExcel->getActiveSheet()->getCell('O'.$a)->getValue();
		$temp->g_nummer = $objPHPExcel->getActiveSheet()->getCell('P'.$a)->getValue();
		$temp->a_nummer = $objPHPExcel->getActiveSheet()->getCell('Q'.$a)->getValue();
        $temp->t_nummer = $objPHPExcel->getActiveSheet()->getCell('R'.$a)->getValue();
		$export[] = $temp;
          //echo 'getCellValue <pre>'.print_r($objPHPExcel->getActiveSheet()->getCell('J'.$a)->getValue(),true).'</pre>';
         }
          $this->_datas['awaygoals'] = array_merge($export);
          
          $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _datas<br><pre>'.print_r($this->_datas,true).'</pre>'),'');
          
          echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;


// Echo memory peak usage
echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;

// Echo done
echo date('H:i:s') , " Done reading file" , EOL;
          
          }
    
}




}


?>
