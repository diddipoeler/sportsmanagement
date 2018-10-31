<?php
// Set flag that this is a parent file

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$_REQUEST['tmpl'] = '';
include('../../../../../../../../index.php');

// query_user.php
 
$search = $_POST['search'];
$result = array();

// Datenbankobjekt laden
$db		=& sportsmanagementHelper::getDBConnection();

$document	=& Factory::getDocument();
$app	=& Factory::getApplication();
 
// Some simple validation
if (is_string($search) && strlen($search) > 2 && strlen($search) < 64)
{
    //$dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass);
 
    // Building the query
    $stmt = $db->prepare("SELECT name FROM users WHERE name LIKE ?");
 
    // The % as wildcard
    if ($stmt->execute(array($search . '%') ) )
    {
        // Filling the results with usernames
        while (($row = $stmt->fetch() ) )
        {
            $result[] = $row['name'];
        }
    }
}
 
// Finally the JSON, including the correct content-type
header('Content-type: application/json');
 
echo json_encode($result); // see NOTE!
 
?>