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

 * https://stackoverflow.com/questions/37958708/dynamic-data-store-in-database-using-jquery-brackets

 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;


$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
echo $this->loadTemplate('projectheading');

//echo 'bracket elfmeter <pre>'.print_r($this->bracket['elfmeter'],true).'</pre>';
//echo 'bracket results <pre>'.print_r($this->bracket['results'],true).'</pre>';
$matchinfo = array();
$temparray = array();
$bracketinfo = array();
$matchinfo = explode("#", $this->bracket['elfmeter'][0]);
//echo 'matchinfo <pre>'.print_r($matchinfo,true).'</pre>';
for($a=0; $a < sizeof($matchinfo); $a++  )
{
$temparray = json_decode($matchinfo[$a]);
//echo 'matchinfo <pre>'.print_r($temparray,true).'</pre>';
if ( $temparray[3] )
{
  $teile = explode("-",$temparray[3]);
$bracketinfo[] = trim($teile[0]);
$bracketinfo[] = trim($teile[1]);
}
  else
  {
$bracketinfo[] = '';
$bracketinfo[] = '';
  }
//$bracketinfo[] = $temparray[3];
}

//krsort($bracketinfo);
$bracketinfo = array_combine(range(1, count($bracketinfo)), array_values($bracketinfo));
$matchinfo = json_encode($bracketinfo);
//echo 'bracketinfo <pre>'.print_r($bracketinfo,true).'</pre>';









?>


<!--
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
-->

<!--
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.10.1/jquery.bracket.min.js" integrity="sha512-f7+ERBLjCiFW5rSaMI0LgiHIh6TO2ca5XPYxKB2e0sNmhGBJoakyD4Cmot8kzuXLDYBpDGp5epKQaUA7sz74pA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.10.1/jquery.bracket.min.css" integrity="sha512-4zVTYhMDL1kdK+bCKSZ5s7GfGDcPKXL1XybpsqhrO8Co+bSevY8iB+YxHn3kfg7Up1xGy8bc2t6/PObGIKkPNQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
-->


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.js" integrity="sha512-BgJKmxJA3rvUEa00GOdL9BJm5+lu6V7Gx2K0qWDitRi0trcA+kS/VYJuzlqlwGJ0eUeIopW4T9faczsg8hzE/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<?php
//$document = Factory::getDocument();
//$document->addScript('/components/com_sportsmanagement/assets/js/jquery_bracket_2018.js');
?>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bracket/0.11.1/jquery.bracket.min.css" integrity="sha512-8QbEO8yS//4kwUDxGu/AS49R2nVILw83kYCtgxBYk+Uw0B9S4R0RgSwvhGLwMaZuYzhhR5ZHR9dA2cDgphRTgg==" crossorigin="anonymous" referrerpolicy="no-referrer" />


<div class="<?php echo $this->divclasscontainer; ?>" id="tournamentbracket">
<div class="row-fluid">

<div style="margin-bottom: 5px; font-size: 16px;"><span id="matchCallback"></span></div>
<script type="text/javascript">
// https://github.com/teijo/jquery-bracket/issues/152#issuecomment-1133752099
  /**
const veldnamen = JSON.stringify(<?php echo $this->bracket['runden']; ?>);
console.log('veldnamen ' + veldnamen );
const roundArray = JSON.parse(veldnamen);
console.log('roundArray ' + roundArray );
var container = $('#minimal .demo');
var str = JSON.stringify(container);
console.log('container ' + str);
  */

console.info(`${ screen.width } × ${ screen.height }`);
console.info(`${ screen.availWidth } × ${ screen.availHeight }`);

  /** das ist richtig*/
console.info(`${ window.innerWidth } × ${ window.innerHeight }`);
console.info(`${ window.outerWidth } × ${ window.outerHeight }`);

var scoreWidth = 90;
var matchMargin = 60;
var roundMargin = 60;
var teamWidth = 200;


var matchinfo = <?php echo $matchinfo; ?>
//var detailmatchinfo = JSON.parse(matchinfo);


// or you can use this instead.
let wholeArray = Object.keys(matchinfo).map(key => matchinfo[key]);
console.log(wholeArray);

var minimalData = {
      teams : <?php echo $this->bracket['teams']; ?>,
      results : <?php echo $this->bracket['results']; ?>

    }

function onclick(data) {
  //$('#matchCallback').text("onclick(data: '" + data + "')")
}

function onhover(data, hover) {
  //$('#matchCallback').text("onhover(data: '" + data + "', hover: " + hover + ")")
}

/* Edit function is called when team label is clicked */
function edit_fn(container, data, doneCb) {
  var input = $('<input type="text">')
  input.val(data.name)
  container.html(input)
  input.focus()
  input.blur(function() { doneCb({flag: data.flag, name: input.val()}) })
}


/* Render function is called for each team label when data is changed, data
 * contains the data object given in init and belonging to this slot. */
function render_fn(container, data, score) {
  if (!data.flag || !data.name)
    return
  container.append('<img src="images/'+data.flag+'.png" /> ').append(data.name)
}

function inforoundname() {

console.log(minimalData.results)
console.log(matchinfo)
//console.log(detailmatchinfo)

const elements = document.querySelectorAll("[data-resultid]");
console.log(elements)
console.log(elements.length)

  /** ergebnisse selektieren */
for (let a = 1; a <= elements.length; a++) {
var str = "result-" + a;
var term = document.querySelectorAll(`[data-resultid=${str}]`);
console.log(term)
console.log('ergebnis ' + term[0].textContent);

console.log('ergebnis info  ' + wholeArray[a - 1]);


const hello = document.querySelector('.score[data-resultid="' + str + '"]');
  /** das klappt */
//hello.innerText = wholeArray[a - 1] + ' ' + term[0].textContent;
if (wholeArray[a - 1]) {
  //  block of code to be executed if the condition is true
  hello.innerText = wholeArray[a - 1];
} else {
  hello.innerText = term[0].textContent;
}
console.log(hello)


const div = document.createElement("div");
        //div.style.position = "relative"
        div.style.width = "0"
        div.style.height = "0"

        const label = document.createElement("label");
        label.style.position = "absolute"
        label.style.width = "250px"
        label.style.fontSize = "100%"
        label.style.fontWeight = "bold";
        label.style.textAlign = "center";
        label.style.left = "10px"
        label.style.top = "-5px"
        label.style.padding = "0"
        label.style.color = 'rgba(0,0,0,0.6)'
        label.innerHTML = 'test';

        div.append(label)
        //hello.append(div)







}

      const matches = document.getElementsByClassName("round");
      //const tijdenknockout = !{JSON.stringify(tijdenknockout)};
      //const veldnamen = !{JSON.stringify(veldnamen)};

      for (let i = 0; i < matches.length; i++) {

        const div = document.createElement("div");
        //div.style.position = "relative"
        div.style.width = "0"
        div.style.height = "0"

        const label = document.createElement("label");
        label.style.position = "absolute"
        label.style.width = "250px"
        label.style.fontSize = "100%"
        label.style.fontWeight = "bold";
        label.style.textAlign = "center";
        label.style.left = "10px"
        label.style.top = "-5px"
        label.style.padding = "0"
        label.style.color = 'rgba(0,0,0,0.6)'
        label.innerHTML = roundArray[i];

        div.append(label)
        matches[i].append(div)
      }

}

 function resize(target, propName) {
    resizeParameters[propName] = parseInt(target.value);
    target.previousElementSibling.textContent = target.value;
    updateResizeDemo();
    inforoundname();
  }











</script>


   <div id="resize" class="col-12">
  <h3>Resizing</h3>
     <!--
  <p>You can adjust the sizes and margins of the bracket elements with
    initialization parameters. Other styles can be overridden by CSS.</p>
     -->
  <label id="teamWidth" class="rangePicker">teamWidth: <span id="teamWidth" ></span>; <input id="teamWidth" oninput="resize(this, 'teamWidth')" type="range" min="30" max="400" step="1" value=""/></label>
  <label id="scoreWidth" class="rangePicker">scoreWidth: <span id="scoreWidth">90</span>; <input id="scoreWidth" oninput="resize(this, 'scoreWidth')" type="range" min="20" max="100" step="1" value="90"/></label>
  <label id="matchMargin" class="rangePicker">matchMargin: <span id="matchMargin">60</span>; <input id="matchMargin" oninput="resize(this, 'matchMargin')" type="range" min="0" max="100" step="1" value="60"/></label>
  <label id="roundMargin" class="rangePicker">roundMargin: <span id="roundMargin">60</span>; <input id="roundMargin" oninput="resize(this, 'roundMargin')" type="range" min="3" max="100" step="1" value="60"/></label>



  </div>
<div id="minimal" class="col-12">
  <script type="text/javascript">


document.querySelectorAll("#teamWidth").forEach(v => {
    v.value = teamWidth;
  })

    document.querySelectorAll("#scoreWidth").forEach(v => {
    v.value = scoreWidth;
  })
    document.querySelectorAll("#matchMargin").forEach(v => {
    v.value = matchMargin;
  })
    document.querySelectorAll("#roundMargin").forEach(v => {
    v.value = roundMargin;
  })

    // These are modified by the sliders
    var resizeParameters = {
     scoreWidth: scoreWidth,
  matchMargin: matchMargin,
  roundMargin: roundMargin,
        teamWidth: teamWidth,
      init: minimalData
    };

    function updateResizeDemo() {
      $('#minimal .demo').bracket(resizeParameters);
    }

    $(updateResizeDemo)
  </script>


<div class="roundnames " style="display:flex">
  </div>


  <!-- <h3>Minimal</h3> teamContainer -->
  <div class="demo col-12">

  </div>
  <script type="text/javascript">



const veldnamen = JSON.stringify(<?php echo $this->bracket['runden']; ?>);
console.log('veldnamen ' + veldnamen );
const roundArray = JSON.parse(veldnamen);
console.log('roundArray ' + roundArray );
var container = $('#minimal .demo');
var str = JSON.stringify(container);
console.log('container ' + str);

// var data = container.bracket('data');
/**
var minimalData = {
      teams : <?php echo $this->bracket['teams']; ?>,
      results : <?php echo $this->bracket['results']; ?>

    }
*/
// rounds : <?php echo $this->bracket['runden']; ?>











    //var container = $('#minimal .demo');
  $(function() {
      container.bracket({
        init: minimalData, /* data to initialize the bracket with */
        skipConsolationRound: true,
        scoreWidth: scoreWidth,
  matchMargin: matchMargin,
  roundMargin: roundMargin,
        teamWidth: teamWidth,
        onMatchClick: onclick,
    onMatchHover: onhover
         })

inforoundname();
    })


  </script>




</div>


</div>

</div>  