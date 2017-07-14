<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <script type="text/javascript">
        
        function checkForm(form){
            var blank = "";
            var db = ch = kw = true;
            if(form.database.value == blank){db = false; }
            if(form.chamber.value == blank){ch = false;}
		var val = form.keyword.value;
            if(form.keyword.value == blank || !(val.replace(/\s/g,"").length)){kw = false;}
            
            var alertString = "Please enter the following missing information: ";
            
            if(!db || !ch || !kw){
            if(!db){alertString += "Congress Database, ";}
            if(!ch){alertString += "Chamber, ";}
            if(!kw){alertString += "Keyword";}
            
            alert(alertString);
                return false;
            }
        }
        
        function clearForm(form){
            document.getElementById("db").value = "";
            document.getElementById("radio1").checked = true;
            document.getElementById("key").value = "";
            document.getElementById("keyLabel").innerHTML = "Keyword*";
            document.getElementById("output").innerHTML = "";
        }
        function setLabel(form){
            var selectDb = form.database.value;
            if(selectDb == "Legislators"){
                document.getElementById("keyLabel").innerHTML = "State/Representative*";
            document.getElementById("key").value = "";
		}
            else if(selectDb == "Committees"){
                document.getElementById("keyLabel").innerHTML = "Committee ID*";
            document.getElementById("key").value = "";
		}
            else if(selectDb == "Bills"){
                document.getElementById("keyLabel").innerHTML = "Bill ID*";
            document.getElementById("key").value = "";
		}
            else if(selectDb == "Amendments"){
                document.getElementById("keyLabel").innerHTML = "Amendment ID*";
        document.getElementById("key").value = "";    
	}
        }
        
    </script>
    
    <style type="text/css">
        #myForm {text-align: center;}
        #output {margin-top: 40px;}
        table,th,td {text-align: center; border: 1px solid black; border-collapse: collapse; margin-left: auto; margin-right: auto;}
        th,td{text-align: center;padding-left: 50px; padding-right: 50px; }
        img {display: block; margin: 0 auto;padding-top: 10px}
        .wrapper{display:table; margin-left: auto; margin-right: auto; width: 800px; border:1px solid black; margin-top: 10px;}
        .left {float: left;padding-left: 150px}
        .right {float: right;padding-right: 100px}
        .zero {text-align: center}
        #spl {text-align: left}
    </style>
    
</head>
<body>
    <?php
    
        $msg = "";
        $key = "Keyword*";
    
        
    if (isset($_GET['run'])){
        dispDetails($_GET['id'],$_GET['cham'],$_GET['st']);
    } 
    
    if (isset($_GET['run2'])){
        dispBillDetails($_GET['id'],$_GET['title'],$_GET['sponsor'],$_GET['intro'],$_GET['action'],$_GET['burl']);
    } 

    $pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
            
            if($pageWasRefreshed){
                unset($_SESSION['database']);
                unset($_SESSION['chamber']);
                unset($_SESSION['keyword']);
            }
    
    if($_SERVER["REQUEST_METHOD"] == "POST"){
            
        $_SESSION['database'] = $_POST['database'];
        $_SESSION['chamber'] = $_POST['chamber'];
        $_SESSION['keyword'] = $_POST['keyword'];
            
        displayInfo();
     }
    

    
    function displayInfo(){
        $database = $_POST["database"];
        $chamber = $_POST["chamber"];
        $keyword = $_POST["keyword"];
        GLOBAL $msg;
        if ($database == "Legislators"){
            displayLegislator($database, $chamber, $keyword);
        }
        else if ($database == "Committees"){
            displayCommittees($database, $chamber, $keyword);
        }
        else if ($database == "Bills"){
            displayBills($database, $chamber, $keyword);
        }
        else if ($database == "Amendments"){
            displayAmendments($database, $chamber, $keyword);
        }
    }
        
    function displayLegislator($database, $chamber, $keyword){
        GLOBAL $msg;
        $msg = "";
        $states = array(
'alabama'=>'AL',
'alaska'=>'AK',
'arizona'=>'AZ',
'arkansas'=>'AR',
'california'=>'CA',
'colorado'=>'CO',
'connecticut'=>'CT',
'delaware'=>'DE',
'florida'=>'FL',
'georgia'=>'GA',
'hawaii'=>'HI',
'idaho'=>'ID',
'illinois'=>'IL',
'indiana'=>'IN',
'iowa'=>'IA',
'kansas'=>'KS',
'kentucky'=>'KY',
'louisiana'=>'LA',
'maine'=>'ME',
'maryland'=>'MD',
'massachusetts'=>'MA',
'michigan'=>'MI',
'minnesota'=>'MN',
'mississippi'=>'MS',
'missouri'=>'MO',
'montana'=>'MT',
'nebraska'=>'NE',
'nevada'=>'NV',
'new hampshire'=>'NH',
'new jersey'=>'NJ',
'new mexico'=>'NM',
'new york'=>'NY',
'north carolina'=>'NC',
'north dakota'=>'ND',
'ohio'=>'OH',
'oklahoma'=>'OK',
'oregon'=>'OR',
'pennsylvania'=>'PA',
'rhode island'=>'RI',
'south carolina'=>'SC',
'south dakota'=>'SD',
'tennessee'=>'TN',
'texas'=>'TX',
'utah'=>'UT',
'vermont'=>'VT',
'virginia'=>'VA',
'washington'=>'WA',
'west virginia'=>'WV',
'wisconsin'=>'WI',
'wyoming'=>'WY'
);
        
        $keywd = strtolower($keyword);
        $keywd = trim($keywd);
	if (array_key_exists("$keywd",$states)){
           $state = $states["$keywd"]; 
	   $url = "http://congress.api.sunlightfoundation.com/legislators?chamber=$chamber&state=$state&apikey=901c5478ca1f46d8a4dd40c9423f1e78";
        }
        else{
            $rep = trim($keyword);
            $test = explode(" ",$rep);
            if (count($test)>1){                    
                $rep = rawurlencode($rep);
                $url = "http://congress.api.sunlightfoundation.com/legislators?chamber=$chamber&aliases=$rep&apikey=901c5478ca1f46d8a4dd40c9423f1e78";                
            }
            else{
                $url = "http://congress.api.sunlightfoundation.com/legislators?chamber=$chamber&query=$rep&apikey=901c5478ca1f46d8a4dd40c9423f1e78"; 
            }
        }
        $json = file_get_contents($url) or die("Failed");
        $result = json_decode($json, true);    
        $count = $result["count"];
        
        
            
        if($count == 0){
            $msg .= "<p class = 'zero'>The API returned zero results for the request.</p>";
        }
        else{
            if($count > 20){$count = 20;}
            $msg .= "<p><table><tr><th>Name</th><th>State</th><th>Chamber</th><th>Details</th></tr>";
                
            for ($i=0 ; $i < $count; $i++){
                $fname = $result["results"][$i]["first_name"];
                $lname = $result["results"][$i]["last_name"];
                $st = $result["results"][$i]["state_name"];
		$st2 = strtolower($st);
		$stat = $states["$st2"];
                $cham = $result["results"][$i]["chamber"];
                $id = $result["results"][$i]["bioguide_id"];
                $msg .= "<tr><td id='spl'>".$fname." ".$lname."</td><td>".$st."</td><td>".$cham."</td><td><a href='?run&id=$id&cham=$cham&st=$stat' name='$id'>View Details</a></td></tr>";    
            }
        $msg .= "</table></p>";
        }
    }
    
    
    
    function dispDetails($id,$cham,$st){
        
        $url = "http://congress.api.sunlightfoundation.com/legislators?chamber=$cham&state=$st&bioguide_id=$id&apikey=901c5478ca1f46d8a4dd40c9423f1e78";
        $json = file_get_contents($url) or die("Failed");
        $result = json_decode($json, true);    
        
        $pic = "http://theunitedstates.io/images/congress/225x275/$id.jpg";
        $fullName = $result['results'][0]['first_name']." ".$result['results'][0]['last_name'];
        $dispfullName = $result['results'][0]['title']." ".$result['results'][0]['first_name']." ".$result['results'][0]['last_name'];
	$term = $result["results"][0]["term_end"];
         
        if(!array_key_exists('website',$result["results"][0]) || is_null($result["results"][0]["website"])){
            $website = "NA";
        }
        else{
            $website = $result["results"][0]["website"];
        }
	    if(!array_key_exists("office",$result["results"][0]) || is_null($result["results"][0]["office"])){
	       $office = "NA";
        }
	    else{
            $office = $result["results"][0]["office"];
        }
	    if(!array_key_exists("facebook_id",$result["results"][0]) || is_null($result["results"][0]["facebook_id"])){
	       $fb = "NA";
        }
	   else{
           $fb = "https://www.facebook.com/".$result["results"][0]["facebook_id"];
       }
        
	if(!array_key_exists("twitter_id",$result["results"][0]) || is_null($result["results"][0]["twitter_id"])){
	   $twitter = "NA";
        }
	else{
	$twitter = "https://twitter.com/".$result["results"][0]["twitter_id"];
        }
        GLOBAL $msg;
        $msg =" ";
        $msg .= "<div class='wrapper'>";
        $msg .= "<img src=$pic />";
        $msg .= "<div class='left'><p>Full Name<br>Term Ends On<br>Website<br>Office<br>Facebook<br>Twitter</p></div>";
        $msg .= "<div class='right'><p>$dispfullName<br>$term<br>";
	if($website == "NA")
        { 
	  $msg .= "NA<br>";
	}
        else{
        
	$msg .= "<a href='$website' target='_blank'>$website</a><br>";
	}
	$msg .= "$office<br>";
	if($fb == "NA"){
	$msg .= "NA<br>";
 	}
        else{ 
	$msg .= "<a href='$fb' target='_blank'>$fullName</a><br>";
	}
	if($twitter == "NA"){
	$msg .= "NA<br>";
	}
        else{
	$msg .= "<a href='$twitter' target='_blank'>$fullName</a></p></div>";
        }
	$msg .= "</div>";       
    }
                 
   function displayCommittees($database, $chamber, $keyword){
       GLOBAL $msg;
       $msg = "";
       $keyword = trim($keyword);
       $commId = strtoupper($keyword);
      $commId = urlencode($commId);
	$url = "http://congress.api.sunlightfoundation.com/committees?committee_id=$commId&chamber=$chamber&apikey=901c5478ca1f46d8a4dd40c9423f1e78";
       $json = file_get_contents($url) or die("Failed");
       $result = json_decode($json, true); 
       $count = $result["count"];
       if($count == 0){
           $msg .= "<p class = 'zero'>The API returned zero results for the request.</p>";
       }
       else{
          $msg .= "<table><tr><th>Committee ID</th><th>Committee Name</th><th>Chamber</th></tr>";
          for ($i=0; $i < $count ; $i++){
              $id = $result["results"][$i]["committee_id"];
              $name = $result["results"][$i]["name"];
              $cham = $result["results"][$i]["chamber"];
              $msg .= "<tr><td>$id</td><td>$name</td><td>$cham</td></tr>";
          }
           $msg .= "</table>";
       }
   }
    
    function displayBills($database, $chamber, $keyword){
        GLOBAL $msg;
        $msg = "";
        $keyword = trim($keyword);
        $billId = strtolower($keyword);
	$billId = urlencode($billId);
        $url = "http://congress.api.sunlightfoundation.com/bills?bill_id=$billId&chamber=$chamber&apikey=901c5478ca1f46d8a4dd40c9423f1e78";
        $json = file_get_contents($url) or die("Failed");
        $result = json_decode($json, true); 
        $count = $result["count"];
        if($count == 0){
           $msg .= "<p class = 'zero'>The API returned zero results for the request.</p>";
       }
        else{
            $msg .= "<table><tr><th>Bill ID</th><th>Short Title</th><th>Chamber</th><th>Details</th></tr>";
            for($i=0; $i < $count; $i++){
                $id = $result["results"][$i]["bill_id"];
                
   		if (!array_key_exists("short_title", $result["results"][$i]) || $result["results"][$i]["short_title"] == null)
                {$title="NA";}
                else {
                $title = $result["results"][$i]["short_title"];
                }
                
               /* $title = $result["results"][$i]["short_title"];
                if($title == null){
                    $title="NA";
                } */
			
                $cham = $result["results"][$i]["chamber"]; 
                $sponsor = $result["results"][$i]["sponsor"]["title"]." ".$result["results"][$i]["sponsor"]["first_name"]." ".$result["results"][$i]["sponsor"]["last_name"];
                $intro = $result["results"][$i]["introduced_on"];
                $action = $result["results"][$i]["last_version"]["version_name"].", ".$result["results"][$i]["last_action_at"];
                $burl = $result["results"][$i]["last_version"]["urls"]["pdf"];
                $msg .= "<tr><td>$id</td><td>$title</td><td>$cham</td><td><a href='?run2&id=$id&title=$title&sponsor=$sponsor&intro=$intro&action=$action&burl=$burl'>View Details</a></td></tr>";
            }
        $msg .= "</table>";
        }
    }
    
    function dispBillDetails($id,$title,$sponsor,$intro,$action,$burl){
        
        GLOBAL $msg;
        $msg = "";
        $msg .= "<div class='wrapper'>";
        $msg .= "<div class='left'><p>Bill ID<br>Bill Title<br>Sponsor<br>Introduced On<br>Last action with date<br>Bill URL</p></div>";
        
        if($title == "NA"){
            $msg .= "<div class='right'><p>$id<br>$title<br>$sponsor<br>$intro<br>$action<br><a href='$burl' target='_blank'>$id</a></p></div>"   ;
        }
        else{
            $msg .= "<div class='right'><p>$id<br>$title<br>$sponsor<br>$intro<br>$action<br><a href='$burl' target='_blank'>$title</a></p></div>"   ;
        }
        $msg .= "</div>";        
    }
    
    function displayAmendments($database, $chamber, $keyword){
        GLOBAL $msg;
        $msg = "";
        $keyword = trim($keyword);
        $amId = strtolower($keyword);
	$amId = urlencode($amId);
        $url = "http://congress.api.sunlightfoundation.com/amendments?amendment_id=$amId&chamber=$chamber&apikey=901c5478ca1f46d8a4dd40c9423f1e78";
        $json = file_get_contents($url) or die("Failed");
        $result = json_decode($json, true); 
        $count = $result["count"];
        if($count == 0){
           $msg .= "<p class = 'zero'>The API returned zero results for the request.</p>";
       }
        else {
            $msg .= "<p><table><tr><th>Amendment ID</th><th>Amendment Type</th><th>Chamber</th><th>Introduced On</th></tr>";
            for($i=0; $i < $count; $i++){
                $id = $result["results"][$i]["amendment_id"];
                $type = $result["results"][$i]["amendment_type"];
                $cham = $result["results"][$i]["chamber"]; 
                $date = $result["results"][$i]["introduced_on"];
                $msg .= "<tr><td>$id</td><td>$type</td><td>$cham</td><td>$date</td></tr>";
            }
            $msg .= "</table></p>";
        }        
    }

?>
    
    <h1 style="text-align:center">Congress Information Search</h1>
    
    <div id="myForm" style="border: 1px black solid; margin-left:auto; margin-right:auto; width:300px; padding:5px">
    <form id="myForm" style="display:inline-block" action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>' method="POST">
        
    Congress Database <select id="db" name="database" onchange="setLabel(this.form)">
        <option value="">Select your option</option>
        <option value="Legislators" <?php if(isset($_POST["search"]) || isset($_GET['run']) || isset($_GET['run2'])) { if(isset($_SESSION['database']) && $_SESSION['database'] == 'Legislators') 
{echo 'selected="selected"'; $key = "State/Representative*";}}?>>Legislators</option>
        <option value="Committees" <?php if(isset($_POST["search"]) || isset($_GET['run']) || isset($_GET['run2'])) { if(isset($_SESSION['database']) && $_SESSION['database'] == 'Committees') 
{echo 'selected="selected"'; $key = "Committee ID*";}}?> >Committees</option>
        <option value="Bills" <?php if(isset($_POST["search"]) || isset($_GET['run']) || isset($_GET['run2'])) { if(isset($_SESSION['database']) && $_SESSION['database'] == 'Bills') 
{echo 'selected="selected"'; $key = "Bill ID*";}}?> >Bills</option>
        <option value="Amendments" <?php if(isset($_POST["search"]) || isset($_GET['run']) || isset($_GET['run2'])) { if(isset($_SESSION['database']) && $_SESSION['database'] == 'Amendments') 
{echo 'selected="selected"'; $key = "Amendment ID*";}}?> >Amendments</option>
        </select><br>
        
        <span style="margin-left:28px">Chamber</span><input type="radio" id="radio1" name="chamber" value="senate" style="margin-top: 10px; margin-left: 30px" checked>Senate
        <input type="radio" id="radio2" name="chamber" value="house" <?php if(isset($_POST["search"]) || isset($_GET['run']) || isset($_GET['run2'])) {if(isset($_SESSION['chamber']) && $_SESSION['chamber'] == 'house') {echo 'checked="checked"';}}?> >House <br>
        
        <label id="keyLabel" style="width: 140px; float:left;margin-top:10px"><?php if(isset($_POST["search"]) || isset($_GET['run']) || isset($_GET['run2'])){echo $key;} else {echo "Keyword*";} ?></label>
        <input type="text" id="key" name="keyword" style="margin-top: 8px;" value="<?php if(isset($_POST["search"]) || isset($_GET['run']) || isset($_GET['run2'])){echo $_SESSION['keyword'];} ?>" ><br>
        
        <input type="submit" name="search" value="Search" onclick="return checkForm(this.form)" style="margin-left: 140px; margin-top:8px; margin-bottom:8px">
        <input type="button" name="clear" value="Clear" onclick="clearForm(this.form)"><br>
        
        <a href="http://sunlightfoundation.com/" target="_blank">Powered by Sunlight Foundation</a>
    </form>
    </div>
    <div id="output">
     <?php     
        echo $GLOBALS['msg'];
        ?>
    </div>
    
</body>
</html>
