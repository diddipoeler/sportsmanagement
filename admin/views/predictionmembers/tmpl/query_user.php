<?php
// Set flag that this is a parent file
define( '_JEXEC', 1 );

$_REQUEST['tmpl'] = '';
include('../../../../../../../../index.php');

// query_user.php
 
$search = $_POST['search'];
$result = array();

// Datenbankobjekt laden
$db		=& JFactory::getDBO();

$document	=& JFactory::getDocument();
$mainframe	=& JFactory::getApplication();
 
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