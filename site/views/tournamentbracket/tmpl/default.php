<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tournamentbracket
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>



<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.10.1/jquery.bracket.min.js" integrity="sha512-f7+ERBLjCiFW5rSaMI0LgiHIh6TO2ca5XPYxKB2e0sNmhGBJoakyD4Cmot8kzuXLDYBpDGp5epKQaUA7sz74pA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.10.1/jquery.bracket.min.css" integrity="sha512-4zVTYhMDL1kdK+bCKSZ5s7GfGDcPKXL1XybpsqhrO8Co+bSevY8iB+YxHn3kfg7Up1xGy8bc2t6/PObGIKkPNQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  
  
<div id="minimal">
  <h3>Minimal</h3>
  <script type="text/javascript">
  var minimalData = {
      teams : [
        ["Team 1", "Team 2"], /* first matchup */
        ["Team 3", "Team 4"],  /* second matchup */
        ["Team 5", "Team 6"],  /* second matchup */
        ["Team 7", "Team 8"]  /* second matchup */
      ],
      results : [
        [[1,2], [3,4],[],[7,8]],       /* first round */
        [[4,6]]        /* second round */
      ]
    }

  $(function() {
      $('#minimal .demo').bracket({
        skipConsolationRound: true,
        init: minimalData /* data to initialize the bracket with */ })
    })
  </script>
  
  <div class="demo">
  </div>
</div>  
