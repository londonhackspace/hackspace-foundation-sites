<?
$page = 'storagedetails_edit';
$title = "Storage request";
require( '../header.php' );

ensureMember();

if(isset($_GET['id'])) {
    $project = new Project(filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT));

    if($user->getId() != $project->getUserId() || ($project->getState() != 'Unapproved' && $project->getState() != 'Pending Approval'))
        fURL::redirect("/storage/{$project->getId()}");
} else {
    $project = new Project();
}
?>

<h2>Request for storage</h2>
<p>Your request will be sent to the <a target="_blank" href="https://groups.google.com/forum/#!forum/london-hack-space">London Hackspace mailing list</a> for approval where it can be read by the general public. If your project is sensitive in nature choose your words appropriately, but be aware that members may not approve your request if it's too obscure.
<br/><br/>

<?
$locations = fRecordSet::build('Location');
$maxStorageMonths = 6;
if (isset($_POST['token'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);
        $identicalNames = fRecordSet::build('Project',array('user_id='=>$user->getId(),'name='=>array(filter_var($_POST['name'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES))), array('name' => 'asc'));

        if(!isset($_POST['name']) || $_POST['name'] == '')
            throw new fValidationException('Name field is required.');
        if(count($identicalNames) > 0 && !$project->getId())
            throw new fValidationException('You\'ve already made a request with that name. How is this request different to the last time? Our members like to know a project with multiple storage requests is being actively worked on and progress is being made.');
        if(!isset($_POST['description']) || $_POST['description'] == '')
            throw new fValidationException('Description field is required.');

        if($_POST['contact'] && $_POST['contact'] != '') {
            $validator = new fValidation();
            $validator->addEmailFields('contact');
            $validator->validate();
        }

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
        if(strtotime($_POST['to_date']) > strtotime("+$maxStorageMonths months", strtotime($_POST['from_date'])))
            throw new fValidationException('Removal date must be 6 months after arrival.');

        $project->setName(filter_var($_POST['name'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
        $project->setDescription(filter_var($_POST['description'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
        if($_POST['contact'] && $_POST['contact'] != '')
            $project->setContact(filter_var($_POST['contact'], FILTER_SANITIZE_EMAIL));
        else
            $project->setContact(null);

        $project->setLocationId(filter_var($_POST['location_id'], FILTER_SANITIZE_NUMBER_INT));
        $project->setLocation(filter_var($_POST['location'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
        $project->setFromDate(filter_var($_POST['from_date'], FILTER_SANITIZE_STRING));
        $project->setToDate(filter_var($_POST['to_date'], FILTER_SANITIZE_STRING));
        if(!$project->getId()) {
            $auto = true;
            $logDetails = "Request created";
            $project->setState('Pending Approval');
            $initial = true;
        } else {
            $auto = false;
            $logDetails = "Request updated";
            $initial = false;
        }

        $project->setUpdatedDate(date('Y-m-d'));
        $project->setUserId($user->getId());
        $project->store();
        $project->submitLog($logDetails, $user->getId());

        // post to Google Groups
        $projectUser = new User($project->getUserId());
        $message = 
            '<strong>' . htmlspecialchars($project->getName()) . "</strong><br>\n" .
            "https://london.hackspace.org.uk/storage/" . $project->getId() . "<br>\n" .
            "by <a href=\"https://london.hackspace.org.uk/members/member.php?id=".$project->getUserId()."\">" . htmlspecialchars($projectUser->getFullName()) . "</a><br>\n" .
            $project->outputDates() . "<br>\n" .
            $project->outputDuration() . ' ' .
            $project->outputLocation() . "<br>\n<br>\n" .
            nl2br(htmlspecialchars($project->getDescription())) . "<br>\n<br>\n";

        if($auto && !$project->isShortTerm())
            $message .= "<strong>***If no one replies to this topic the request will be automatically approved within ".$project->automaticApprovalDuration()." days.***</strong>";

        $project->submitMailingList($message, $initial);

        // is this a short term request? If so automatically approve it
        if($project->isShortTerm()) {
            $project->setState('Approved');
            $project->store();

            // log the update
            $logmsg = 'Short term storage detected, status automatically changed to '.$project->getState();
            $project->submitLog($logmsg,false);
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
                <label for="name" class="col-sm-3 control-label">Name of Item</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="name" name="name" placeholder="What are you storing?" value="<? if($_POST && $_POST['name']) { echo htmlspecialchars($_POST['name']); } else if($project->getName()) { echo htmlspecialchars($project->getName()); } ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="description" class="col-sm-3 control-label">Description</label>
                <div class="col-sm-9">
                    <textarea id="description" name="description" class="form-control" placeholder="What will you be doing? What tools do you need and how often do you plan to work on it?" rows="3"><? if($_POST && htmlspecialchars($_POST['description'])) { echo htmlspecialchars($_POST['description']); } else if($project->getDescription()) { echo htmlspecialchars($project->getDescription()); } ?></textarea>
                </div>
            </div>
            <div class="form-group location-fields">
                <label class="col-sm-3 control-label">Contact</label>
                <div class="col-sm-9">
                    <input type="email" id="contact" name="contact" placeholder="Email address" class="form-control" value="<? if($_POST && $_POST['contact']) { echo htmlspecialchars($_POST['contact']); } else if($project->getContact()) { echo $project->getContact(); }?>"/> or <?=$user->getEmail();?> (if left blank)
                    <p class="help-block">Your email address (above) will be made available to all members in case there's a problem with your project while it's being stored in the space.</p>
                </div>
            </div>
            <div class="form-group location-fields">
                <label for="location_id" class="col-sm-3 control-label">Location</label>
                <div class="col-sm-9">
                    <select class="form-control" id="location_id" name="location_id">
                        <option value="" disabled <? if((!$_POST || !$_POST['location_id']) && !$project->getLocationId()) { echo 'selected'; } ?>></option>
                        <? foreach($locations as $location) {
                                echo '<option value="'.$location->getId().'" ';
                                if($_POST && $_POST['location_id'] == $location->getId()) { 
                                    echo 'selected'; 
                                } else if($project->getLocationId() == $location->getId()) { 
                                    echo 'selected'; 
                                }
                                echo '>'.$location->getName().'</option>';
                            } ?>
                    </select>
                    <input type="text" id="location" name="location" class="form-control" placeholder="Where abouts exactly?" value="<? if($_POST && $_POST['location']) { echo htmlspecialchars($_POST['location']); } else if($project->getLocation()) { echo htmlspecialchars($project->getLocation()); }?>"/>
                    <p class="alert alert-warning tip-loading-bay hide" role="alert"><span class="glyphicon glyphicon-star"></span> The loading bay must be kept clear at all times.</p>
                </div>
            </div>
            <div class="form-group">
                <label for="from_date" class="col-sm-3 control-label">Storage Dates</label>
                <div class="col-sm-9">
                    arrival <input type="date" placeholder="yyyy-mm-dd" value="<? if($_POST && $_POST['from_date']) { echo htmlspecialchars($_POST['from_date']); } else if($project->getFromDate()) { echo $project->getFromDate(); } ?>" min="<?=date('Y-m-d') ?>" max="<?=date('Y-m-d', strtotime("+$maxStorageMonths months"))?>" id="from_date" name="from_date" class="form-control" />
                    &nbsp;&nbsp; <div style="display:inline;white-space:nowrap;">removal&nbsp;<input type="date" placeholder="yyyy-mm-dd" value="<? if($_POST && $_POST['to_date']) { echo htmlspecialchars($_POST['to_date']); } else if($project->getToDate()) { echo $project->getToDate(); } ?>" min="<?=date('Y-m-d') ?>" max="<?=date('Y-m-d', strtotime("+$maxStorageMonths months"))?>" id="to_date" name="to_date" class="form-control" /></div>
                    <p class="alert alert-info tip-short-term-storage hide" role="alert"><span class="glyphicon glyphicon-star"></span> It's okay to store your project short term to let paint dry, give yourself a break, etc. But short term storage requests can <strong> only be extended 2 days</strong> at most. If it takes longer you'll need to submit a new storage request and leave enough time for other members to review.</p>
                    <p class="alert alert-warning tip-indoor-review hide" role="alert"><span class="glyphicon glyphicon-star"></span> We need <strong>2 days to review</strong> indoor storage requests unless it's a matter of urgency.</p>
                    <p class="alert alert-warning tip-yard-review hide" role="alert"><span class="glyphicon glyphicon-star"></span> We need <strong>7 days to review</strong> yard storage requests unless it's a matter of urgency.</p>
                    <p class="help-block">Think carefully about your date of removal as we take your commitment seriously. It helps to estimate how long your project will take then double it.</p>
                    <p class="help-block">If you're still waiting on parts to be delivered, please reconsider submitting your request until you have everything you need.</p>
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

<? require('../footer.php'); ?>
<script type="text/javascript" src="/javascript/moment.min.js"></script>
<script type="text/javascript" src="/javascript/jquery-ui-1.9.2.custom.min.js"></script>
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
    // date picker polyfill
    if(document.getElementById('from_date').type == 'text') {
        $.datepicker.setDefaults({dateFormat: 'yy-mm-dd'});
        $('#from_date').datepicker(); 
        $('#to_date').datepicker(); 
    }

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
</body>
</html>
