<?
$page = 'storagedetails_edit';
$title = "Storage request";
require( '../header.php' );

if(isset($_GET['id'])) {
    $project = new Project(filter_var($_GET['id'], FILTER_SANITIZE_STRING));
    if($user->getId() != $project->getUserId() 
       || ($project->getState() != 'Unapproved' && $project->getState() != 'Pending Approval'))
        fURL::redirect("/storage/{$project->getId()}");
} else {
    $project = new Project();
}

if (!isset($user))
    fURL::redirect("/login.php?forward=/storage/edit/{$project->getId()}");
?>

<h2>Request for storage</h2>
<p>Your request will be sent to the <a target="_blank" href="https://groups.google.com/forum/#!forum/london-hack-space">London Hackspace mailing list</a> for approval where it can be read by the general public. If your project is sensitive in nature choose your words approperiately, but be aware that members may not approve your request if it's too obscure.
<br/><br/>

<?
$locations = fRecordSet::build('Location');
$maxStorageMonths = 6;
if (isset($_POST['token'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

        if(!isset($_POST['name']) || $_POST['name'] == '')
            throw new fValidationException('Name field is required.');
        if(!isset($_POST['description']) || $_POST['description'] == '')
            throw new fValidationException('Description field is required.');
        if(!isset($_POST['location_id']) || $_POST['location_id'] == '')
            throw new fValidationException('Location select is required.');
        if(!isset($_POST['location']) || $_POST['location'] == '')
            throw new fValidationException('Location field is required.');
        if(!isset($_POST['from_date']) || $_POST['from_date'] == '')
            throw new fValidationException('Arrival field is required.');
        if(!isset($_POST['to_date']) || $_POST['to_date'] == '')
            throw new fValidationException('Removal field is required.');

        // from > today
        if(strtotime($_POST['from_date']) < strtotime(date('Y-m-d')))
            throw new fValidationException('Arrival date must be no earlier than today.');
        // from < max
        if(strtotime($_POST['from_date']) > strtotime("+$maxStorageMonths months"))
            throw new fValidationException('Arrival date must be in the next 6 months.');
        // to > from
        if(strtotime($_POST['to_date']) < strtotime($_POST['from_date']))
            throw new fValidationException('Removal date must come after arrival date.');
        // to < max
        if(strtotime($_POST['to_date']) > strtotime("+$maxStorageMonths months", strtotime($_POST['to_date'])))
            throw new fValidationException('Removal date must be 6 months after arrival.');

        $project->setName(filter_var($_POST['name'], FILTER_SANITIZE_STRING));
        $project->setDescription(filter_var($_POST['description'], FILTER_SANITIZE_STRING));
        $project->setLocationId(filter_var($_POST['location_id'], FILTER_SANITIZE_STRING));
        $project->setLocation(filter_var($_POST['location'], FILTER_SANITIZE_STRING));
        $project->setFromDate(filter_var($_POST['from_date'], FILTER_SANITIZE_STRING));
        $project->setToDate(filter_var($_POST['to_date'], FILTER_SANITIZE_STRING));
        if(!$project->getId()) {
            $auto = true;
            $logDetails = "Request created";
            $project->setState('Pending Approval');
        } else {
            $auto = false;
            $logDetails = "Request updated";
        }

        $project->setUpdatedDate(date('Y-m-d'));
        $project->setUserId($user->getId());
        $project->store();
        $project->submitLog($logDetails, $user->getId());

        // post to Google Groups
        $projectUser = new User($project->getUserId());
        $message = 
            '<strong>' . $project->getName() . "</strong><br/>" .
            "by " . htmlspecialchars($projectUser->getFullName()) . " https://london.hackspace.org.uk/storage/" . $project->getId() . "<br/><br/>" .
            $project->outputDates() . "<br/>" .
            $project->outputDuration() .
            $project->outputLocation() . "<br/><br/>" .
            nl2br(stripslashes($project->getDescription())) . "<br/><br/>";

        if($auto)
            $message .= "<strong>***If no one replies to this topic the request will be automatically approved within ".$project->automaticApprovalDuration()." days.***</strong>";

        $project->submitMailingList($message);

        // is this a short term request? If so automatically approve it
        if($project->isShortTerm()) {
            $project->setState('Approved');
            $project->store();

            // log the update
            $logmsg = 'Short term storage detected, status automatically changed to '.$project->getState();
            $project->submitLog($logmsg);
            $project->submitMailingList($logmsg);
        }

        fURL::redirect("/storage/{$project->getId()}");
    } catch (fValidationException $e) {
        echo $e->printMessage();
    } catch (fSQLException $e) {
        echo '<div class="alert alert-danger">An unexpected error occurred, please try again later</div>';
    }
}
?>
<div class="row">
    <div class="col-sm-offset-1 col-sm-9">
        <form id="formStorageRequest" class="form-horizontal storage-form" role="form" method="post">
            <input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />
            <div class="form-group">
                <label for="name" class="col-sm-3 control-label">Name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="name" name="name" placeholder="How would you describe it?" value="<? if($_POST && $_POST['name']) { echo $_POST['name']; } else if($project->getName()) { echo $project->getName(); } ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="description" class="col-sm-3 control-label">Description</label>
                <div class="col-sm-9">
                    <textarea id="description" name="description" class="form-control" placeholder="What will you be doing? What tools do you need and how often do you plan to work on it?" rows="3"><? if($_POST && $_POST['description']) { echo $_POST['description']; } else if($project->getDescription()) { echo $project->getDescription(); } ?></textarea>
                </div>
            </div>
            <div class="form-group location-fields">
                <label for="location_id" class="col-sm-3 control-label">Location</label>
                <div class="col-sm-9">
                    <select class="form-control" id="location_id" name="location_id">
                        <option value="" disabled <? if((!$_POST || !$_POST['location_id']) && !$project->getLocationId()) { echo 'selected'; } ?>></option>
                        <? foreach($locations as $location) {
                                echo '<option value="'.$location->getName().'" ';
                                if($_POST && $_POST['location_id'] == $location->getName()) { 
                                    echo 'selected'; 
                                } else if($project->getLocationId() == $location->getName()) { 
                                    echo 'selected'; 
                                }
                                echo '>'.$location->getName().'</option>';
                            } ?>
                    </select>
                    <input type="text" id="location" name="location" class="form-control" placeholder="Where abouts exactly?" value="<? if($_POST && $_POST['location']) { echo $_POST['location']; } else if($project->getLocation()) { echo $project->getLocation(); }?>"/>
                    <p class="alert alert-warning tip-loading-bay hide" role="alert"><span class="glyphicon glyphicon-star"></span> The loading bay must be kept clear at all times.</p>
                </div>
            </div>
            <div class="form-group">
                <label for="from_date" class="col-sm-3 control-label">Storage Dates</label>
                <div class="col-sm-9">
                    arrival <input type="date" value="<? if($_POST && $_POST['from_date']) { echo $_POST['from_date']; } else if($project->getFromDate()) { echo $project->getFromDate(); } ?>" min="<?=date('Y-m-d') ?>" max="<?=date('Y-m-d', strtotime("+$maxStorageMonths months"))?>" id="from_date" name="from_date" class="form-control" />
                    &nbsp;&nbsp;&nbsp;removal <input type="date" value="<? if($_POST && $_POST['to_date']) { echo $_POST['to_date']; } else if($project->getToDate()) { echo $project->getToDate(); } ?>" min="<?=date('Y-m-d') ?>" max="<?=date('Y-m-d', strtotime("+$maxStorageMonths months"))?>" id="to_date" name="to_date" class="form-control" />
                    <p class="alert alert-info tip-short-term-storage hide" role="alert"><span class="glyphicon glyphicon-star"></span> It's okay to store your project short term to let paint dry, give yourself a break, etc. But short term storage requests can <strong> only be extended 2 days</strong> at most. If it takes longer you'll need to submit a new storage request and leave enough time for other members to review.</p>
                    <p class="alert alert-warning tip-indoor-review hide" role="alert"><span class="glyphicon glyphicon-star"></span> We need <strong>2 days to review</strong> indoor storage requests unless it's a matter of urgency.</p>
                    <p class="alert alert-warning tip-yard-review hide" role="alert"><span class="glyphicon glyphicon-star"></span> We need <strong>7 days to review</strong> yard storage requests unless it's a matter of urgency.</p>
                    <p class="help-block">Think carefully about the date of removal as we take your commitment seriously.</p>
                    <p class="help-block">It helps to estimate how long your project will take then double it. If you're still waiting on parts to be delivered, then double it again.</p>
                </div>
            </div>
            <div class="form-group location-fields">
                <label class="col-sm-3 control-label">Status</label>
                <div class="col-sm-9">
                    <div class="status <? if($project->getState()) { echo strtolower($project->getState()); } else { echo 'pending'; } ?>"><? if($project->getState()) { echo $project->getState(); } else { echo 'Pending Approval'; } ?></div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <input type="submit" id="formSubmit" name="submitForm" value="Submit request" class="btn btn-primary"/>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="yardStorageModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">Note about yard storage</h4>
            </div>
            <div class="modal-body">
                <ul>
                    <li>We may ask for a <strong>£250 deposit</strong> depending on how large your project is how long you intend to store it.</li>
                    <li>No power tools or loud noises are permitted in the yard past <strong>8pm on weekdays</strong> and <strong>6pm on weekends</strong>.</li>
                    <li>The <strong>loading bay</strong> and <strong>three parking spaces</strong> must be kept clear at all times.</li>
                </ul>
                <p>Find out more about our <a target="_blank" href="https://wiki.london.hackspace.org.uk/view/Yard">yard facilities here</a>.</p>
            </div>
            <div class="modal-footer">
                <button id="continueSubmit" type="button" class="btn btn-primary">Continue</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript" src="/javascript/moment.min.js"></script>
<script type="text/javascript">
/*
 * Fix dates so they fall in the required time period
 */
function validateDates(from,to,maxMonths) {

    // reset the min/max values based on today
    from.attr('min',moment().format('YYYY-MM-DD'));
    from.attr('max',moment().add(maxMonths, 'months').format('YYYY-MM-DD'));
    to.attr('min',moment().format('YYYY-MM-DD'));
    to.attr('max',moment().add(maxMonths, 'months').format('YYYY-MM-DD'));

    if(from.val() == '') return;

    // from < now
    if(moment(from.val(), 'YYYY-MM-DD').format('X') < moment(from.attr('min'), 'YYYY-MM-DD').format('X'))
        from.val(moment().format('YYYY-MM-DD'));

    // from > max
    if(moment(from.val(), 'YYYY-MM-DD').format('X') > moment().add(maxMonths, 'months').format('X'))
        from.val(moment().add(maxMonths, 'months').format('YYYY-MM-DD'));

    // reset the values based on from
    to.attr('min',from.val());
    to.attr('max',moment(from.val(), 'YYYY-MM-DD').add(maxMonths, 'months').format('YYYY-MM-DD'));

    if(to.val() == '') return;

    // reset the values based on to
    from.attr('max',to.val());

    // to < from
    if(moment(to.val(), 'YYYY-MM-DD').format('X') < moment(from.val(), 'YYYY-MM-DD').format('X'))
        to.val(from.val());

    // to > max
    if(moment(to.val(), 'YYYY-MM-DD').format('X') > moment(from.val(), 'YYYY-MM-DD').add(maxMonths, 'months').format('X'))
        to.val(moment(from.val(), 'YYYY-MM-DD').add(maxMonths, 'months').format('YYYY-MM-DD'));
}

/*
 * Display a message warning them that a short term request can't be extended long
 */
function tipShortTermStorage(from,to) {
    $('.tip-short-term-storage').addClass('hide');

    // project arrives in the next day and is removed in 3 days
    if(moment(from.val(), 'YYYY-MM-DD').format('X') <= moment(from.attr('min'), 'YYYY-MM-DD').add(1, 'day').format('X') 
       && moment(to.val(), 'YYYY-MM-DD').format('X') <= moment(from.val(), 'YYYY-MM-DD').add(3, 'days').format('X'))
        $('.tip-short-term-storage').removeClass('hide');
}

/*
 * Display a message complaining about insufficient lead time to review the storage request
 */
function tipReviewPeriod(from,location) {
    $('.tip-indoor-review, .tip-yard-review').addClass('hide');

    // only complain about lead time if it's not a short term request
    if($('.tip-short-term-storage').hasClass('hide') && location.val() != '') {
        // if its a yard project with less than 7 days lead time, warn them
        if(location.text() == 'Yard' && moment(from.val(), 'YYYY-MM-DD').format('X') < moment().add(7, 'days').format('X')) {
            $('.tip-yard-review').removeClass('hide');
        }
        // otherwise if there's less than 2 days lead time, warn them
        else if(moment(from.val(), 'YYYY-MM-DD').format('X') < moment().add(2, 'days').format('X')) {
            $('.tip-indoor-review').removeClass('hide');
        }
    }
}

/*
 * Display a message about keeping the loading bay clear
 */
function tipLoadingBay(text) {
    $('.tip-loading-bay').addClass('hide');
    if(text.toLowerCase().indexOf('loading bay') > -1) {
        $('.tip-loading-bay').removeClass('hide');
    }
}

window.onload = function() {
    tipShortTermStorage($('#from_date'),$('#to_date'));
    tipReviewPeriod($('#from_date'),$('#location_id option:selected'));
    tipLoadingBay($('#location').val());

    $('#from_date,#to_date').bind("change", function() {
        validateDates($('#from_date'),$('#to_date'),<?=$maxStorageMonths?>);
        tipShortTermStorage($('#from_date'),$('#to_date'));
        tipReviewPeriod($('#from_date'),$('#location_id option:selected'));
    });

    $('#location_id').bind("change", function() {
        tipReviewPeriod($('#from_date'),$('#location_id option:selected'));
    });

    $('#location').bind("keyup", function() {
        tipLoadingBay($('#location').val());
    });

    $("#formStorageRequest").bind('submit',function(e) {
        if($('#location_id option:selected').text() == 'Yard' && !$('#yardStorageModal').hasClass('in')) {
             $('#yardStorageModal').modal()
             return false;
        }
        return true;
    });

    $('#continueSubmit').bind('click',function(e) {
        $("#formStorageRequest").trigger('submit');
    });
};
</script>
<? require('../footer.php'); ?>
