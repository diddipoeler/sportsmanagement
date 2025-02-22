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
  <div class="demo">
  </div>
  <script type="text/javascript">


  var minimalData = {
      teams : <?php echo $this->bracket['teams']; ?>,
      results : <?php echo $this->bracket['results']; ?>
    }

  $(function() {
      $('#minimal .demo').bracket({
        skipConsolationRound: true,
        teamWidth: 300,
        init: minimalData /* data to initialize the bracket with */ })
    })
  </script>




</div>

<script type="text/javascript">
  function resize(target, propName) {
    resizeParameters[propName] = parseInt(target.value);
    target.previousElementSibling.textContent = target.value;
    updateResizeDemo();
  }
</script>
<div id="resize">
  <h3>Resizing</h3>
  <p>You can adjust the sizes and margins of the bracket elements with
    initialization parameters. Other styles can be overridden by CSS.</p>
  <label class="rangePicker">teamWidth: <span>60</span>; <input oninput="resize(this, 'teamWidth')" type="range" min="30" max="100" step="1" value="60"/></label>
  <label class="rangePicker">scoreWidth: <span>40</span>; <input oninput="resize(this, 'scoreWidth')" type="range" min="20" max="100" step="1" value="40"/></label>
  <label class="rangePicker">matchMargin: <span>40</span>; <input oninput="resize(this, 'matchMargin')" type="range" min="0" max="100" step="1" value="40"/></label>
  <label class="rangePicker">roundMargin: <span>20</span>; <input oninput="resize(this, 'roundMargin')" type="range" min="3" max="100" step="1" value="20"/></label>
  <script type="text/javascript">
    // These are modified by the sliders
    var resizeParameters = {
      teamWidth: 60,
      scoreWidth: 20,
      matchMargin: 10,
      roundMargin: 50,
      init: minimalData
    };

    function updateResizeDemo() {
      $('#minimal .demo').bracket(resizeParameters);
    }

    $(updateResizeDemo)
  </script>
</div>


