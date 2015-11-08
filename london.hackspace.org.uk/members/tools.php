<?php
if (isset($_GET['summary'])){
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/../lib/init.php');
    ensureMember();
    header('Content-Type: application/json');

    $opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>"API-KEY: ".$ACSERVER_KEY."\r\n"
      )
    );

    $context = stream_context_create($opts);
    $result = file_get_contents($ACSERVER_ADDRESS . "/api/get_tools_summary_for_user/".$user->getId(),false,$context);

    if($result === FALSE) {
        echo "\nFailed to fetch data:";
    }
    echo $result;
    //print_r();
    //echo "\nJSON status: " . json_last_error();
    die();
}

$page = 'tools';
$title = 'Tool access';
$desc = '';
require('../header.php');

global $ACSERVER_ADDRESS;
global $ACSERVER_KEY;


ensureMember();


?>
<!-- Need Anguluar for tools page -->
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>

<h2>Tool access</h2>

<div ng-app="toolApp" class='ng-cloak'>
 
    <table ng-controller="ToolController" 
           class="table table-bordered table-striped tool-summary">
        <thead>
          <tr>
            <th>Tool</th>
            <th>Status<small>Status and availability</small></th>
            <th>Status message <small>Any extra info</small></th>
            <th>Access? <small>Your access level</small></th>
            
          </tr>
        </thead>
        <tbody>
        
            <tr ng-repeat="tool in contents" class="well well-small">
              
                <th>{{tool.name}}</th>
                <td ng-class="{'is-visible': tool.status=='Operational',
                    'is-bad': tool.status=='Out of service',
                    'is-special': tool.status=='In use'}">{{tool.status}}</td>
                <td ng-class="{'is-hidden': tool.status_message=='OK',
                    'is-bad': tool.status_message!='OK'}">{{tool.status_message}}</td>
                <td ng-class="{'is-visible': tool.permission=='user',
                    'is-bad': tool.permission=='unauthorised',
                    'is-special': tool.permission=='maintainer'}">{{tool.permission}}</td>
            </tr>
        </tbody>
 </table>
</div>

<?php require('../footer.php'); ?>
</body>

<script src="/../javascript/tool_access.js"></script>

</html>
