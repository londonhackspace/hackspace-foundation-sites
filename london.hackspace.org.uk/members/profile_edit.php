<? 
$page = 'profile_edit';
$title = "Edit your profile";
$desc = '';
require('../header.php');

if (!isset($user)) {
    fURL::redirect('/login.php?forward=/members/profile_edit.php');
}
?>
<h2>Edit Your Member Profile</h2>
<?php
$user_profile = $user->createUsersProfile();
$my_learning = $user->buildLearnings();
$my_aliases = $user->buildUsersAliases();
$my_interests = $user->buildInterests();

if (isset($_POST['disable'])) {
	$user->setDisabledProfile(1);
	$user->store();
  fURL::redirect("/members/profile/{$user->getId()}");
}

if (isset($_POST['enable'])) {
	$user->setDisabledProfile(0);
	$user->store();
  fURL::redirect("/members/profile/{$user->getId()}");
}

if (isset($_POST['submit'])) {
    try {
        fRequest::validateCSRFToken($_POST['token']);

		// user profile
		(isset($_POST['allow_email']) && filter_var($_POST['allow_email'], FILTER_SANITIZE_STRING) == 'on') ? 
			$user_profile->setAllowEmail(1):
			$user_profile->setAllowEmail(0);
			
		(isset($_POST['allow_doorbot']) && filter_var($_POST['allow_doorbot'], FILTER_SANITIZE_STRING) == 'on') ? 
			$user_profile->setAllowDoorbot(1):
			$user_profile->setAllowDoorbot(0);

		if(isset($_POST['website'])) {
			if($_POST['website'] == 'http://')
				$_POST['website'] = '';
			$user_profile->setWebsite(filter_var($_POST['website'], FILTER_SANITIZE_STRING));
		}

		if(isset($_POST['description']))
			$user_profile->setDescription(filter_var($_POST['description'], FILTER_SANITIZE_STRING));

		if(isset($_POST['photo-upload']) && $_POST['photo-upload'] != '' && $_POST['photo-upload'] != null) {
			$filename = $user->getId() . '_' . str_replace(' ', '_', $user->getFullName());
			$filename = preg_replace("/[^0-9a-zA-Z_]/", "" , $filename);
			$path = $_SERVER['DOCUMENT_ROOT'] . '/../var/photos/';
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
			file_put_contents($path . $filename . '.png', base64_decode(substr($_POST['photo-upload'], strpos($_POST['photo-upload'],",")+1)));
			file_put_contents($path . $filename . '_sml.png', base64_decode(substr($_POST['photo-upload-sml'], strpos($_POST['photo-upload-sml'],",")+1)));
			file_put_contents($path . $filename . '_med.png', base64_decode(substr($_POST['photo-upload-med'], strpos($_POST['photo-upload-med'],",")+1)));
			$user_profile->setPhoto($filename);
		}
		$user_profile->setUserId($user->getId());
		$user_profile->store();
		
		// user learnings
		$list = array();
		if(isset($_POST['learnings'])) {
			foreach($_POST['learnings'] as $key=>$val) {
				array_push($list, filter_var($key, FILTER_SANITIZE_NUMBER_INT));
			}
		}
		$user->setLearnings($list);
		
		// user aliases
		$list = array();
		if(isset($_POST['aliases'])) {
			foreach($_POST['aliases'] as $key=>$val) {
				if($val && $val != null && $val != '')
					$list[filter_var($key, FILTER_SANITIZE_STRING)] = filter_var($val, FILTER_SANITIZE_STRING);
			}
		}
		$user->setAliases($list);
				
		// user interests
		$list = array();
		if(isset($_POST['interests'])) {
			foreach($_POST['interests'] as $key=>$val) {
				array_push($list, filter_var($key, FILTER_SANITIZE_NUMBER_INT));
			}
		}
		$all_interests = fRecordSet::build(
            'Interest',
            array(),
            array('category' => 'asc', 'name' => 'asc')
        );
		if(isset($_POST['other_interests'])) {
			foreach(explode(',',$_POST['other_interests']) as $val) {
				$search = filter_var(trim($val), FILTER_SANITIZE_STRING);
				if($search != '') {
					$key = null;
					foreach($all_interests as $check) {
						if(strtolower($check->getName()) == strtolower($search)) {
							$key = $check->getInterestId();
							break;
						}
					}
					if($key == null)
						$key = $user->addInterest($search,'Other');
					
					array_push($list, $key);
				}
			}
		}
		$user->setInterests($list);

		$user->setHasProfile(1);
		$user->store();
    fURL::redirect("/members/profile/{$user->getId()}");
    } catch (fValidationException $e) {
        echo '<div class="alert alert-danger">' . $e->printMessage() . '</div>';
    } catch (fSQLException $e) {
        echo '<div class="alert alert-danger">An unexpected error occurred, please try again later</div>';
        trigger_error($e);
    }
}

if (isset($_GET['saved'])) {
  echo "<div class=\"alert alert-success\"><p>Your profile was updated successfully.</p></div>";
}
?>
<p>We'd love to get to know you better. The information you share here will be available to all paid up members but not the general public.</p>
<form method="post" role="form" class="profile">
<input type="hidden" name="token" value="<?=fRequest::generateCSRFToken()?>" />

<div class="row">
	<div class="col-md-3">
	    <div class="form-group invisible" style="height: 0; margin: 0">
	        <input type="submit" name="submit" value="Update profile" class="btn btn-primary update-profile"/>
	    </div>
		<div class="member-avatar">
            <img src="photo.php?name=<?=$user_profile->getPhoto() ?>"/>
            <input type="hidden" name="photo-upload" id="photo-upload" />
            <input type="hidden" name="photo-upload-sml" id="photo-upload-sml" />
            <input type="hidden" name="photo-upload-med" id="photo-upload-med" />
        	<button class="btn btn-primary" id="photo-select">Upload new photo</button>
        	<small class="hidden">'Update profile' to save your photo.</small>
	        <input type="file" name="photo-filesystem" id="photo-filesystem" accept="image/*">
        </div>
        <div class="member-training">
	    	<div class="form-group">
				<div class="btn-group">
				  <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
				    Trained in the art of... <span class="caret"></span>
				  </button>
				  <ul class="dropdown-menu">
		            <? foreach(fRecordSet::build('Learning') as $training) {?>
					<li><img data-lid="<?=$training->getLearningId()?>" data-name="<?=$training->getName()?>" src="/images/trained-<?=strtolower(str_replace(' ','',$training->getName()));?>.png" class="icon" title="<?=$training->getDescription()?>" /> <?=$training->getName()?></li>
		            <? } ?>
				  </ul>
				</div>
			</div>
	    	<div class="form-group training-badges container">
            <? foreach($my_learning as $training) {?>
				<div class="remove-img"><img src="/images/trained-<?=strtolower(str_replace(' ','',$training->getName()));?>.png"/><input type="hidden" name="learnings[<?=$training->getLearningId()?>]" value="<?=$training->getName()?>" /></div>
            <? } ?>
			</div>
        </div>
	</div>
	<div class="col-md-9">
		<? if($user->getDisabledProfile() == 0) { ?>
		<small class="profile_edit"><input type="submit" name="disable" value="Disable my profile" class="btn btn-default btn-sm"/></small>
		<? } else { ?>
		<small class="profile_edit"><input type="submit" name="enable" value="Enable my profile" class="btn btn-primary btn-sm"/></small>
		<? } ?>

		<h3>
			<?= htmlspecialchars($user->getFullName()) ?>
			<p><small><?=$user->getMemberNumber()?><br/><? if($user->firstTransaction() != null) {
				echo ' Joined '.$user->firstTransaction(); 
			} if($user_profile->getAllowDoorbot() && $user->getDoorbotTimestamp() != '') {
				echo ', last seen '.date('dS M Y', strtotime($user->getDoorbotTimestamp()));
			} ?>
			</small></p>
		</h3>
		<div class="checkbox">
			<label>
				<input type="checkbox" <? if($user_profile->getAllowEmail()) { echo 'checked'; } ?> name="allow_email" id="allow_email"> allow members to see my email address (<a href="mailto:<?=$user->getEmail()?>"><?=$user->getEmail()?></a>)
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" <? if($user_profile->getAllowDoorbot()) { echo 'checked'; } ?> name="allow_doorbot" id="allow_doorbot"> allow members to see the date I last visited the space (this helps people find each other)
			</label>
		</div>
	    <div class="form-group personal-site">
	        <label for="website">Website</label>
	        <input type="text" id="website" name="website" class="form-control" value="<? if($user_profile->getWebsite()) { echo $user_profile->getWebsite(); } else { echo 'http://'; } ?>" />
	    </div>    


	    <div class="form-group aliases">
	        <label for="aliases">Aliases</label>
			<div class="alias-fields">
            <? 
            $all_aliases = fRecordSet::build('Aliase',array(),array('aliases.type' => 'asc', 'aliases.id' => 'asc'));
			foreach($my_aliases as $my_alias) { 
			?>
				<div class="input-group alias-field">
					<input type="text" class="form-control" name="aliases[<?=$my_alias->getAliasId();?>]" value="<?=$my_alias->getUsername();?>">
					<div class="input-group-btn">
				        <? if (ctype_digit($my_alias->getAliasId())) { ?>
				        <button type="button" class="btn btn-default dropdown-toggle no-icon" data-toggle="dropdown">Other <span class="caret"></span></button>
				        <? } else { ?>
				        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="member-social-icon iconlhs-<?=substr(strtolower(preg_replace("/[^0-9a-zA-Z]/","",$my_alias->getAliasId())),0,14);?>" title="<?=$my_alias->getAliasId()?>"></span><?=$my_alias->getAliasId()?> <span class="caret"></span></button>
				        <? } ?>
				        <ul class="dropdown-menu pull-right">
				            <? $lastType = null; foreach($all_aliases as $alias) {?>
			            	<?if($lastType != null && ($lastType != $alias->getType())) { ?>
			            	<li class="divider"></li>
			            	<? } ?>
							<li><span class="member-social-icon iconlhs-<?=substr(strtolower(preg_replace("/[^0-9a-zA-Z]/","",$alias->getId())),0,14);?>" title="<?=$alias->getId()?>"></span><?=$alias->getId()?></li>
				            <? $lastType = $alias->getType();
							} ?>
				        </ul>
				        <button title="remove" type="button" class="btn btn-default alias-remove">x</button>
				    </div><!-- /btn-group -->
				</div><!-- /input-group -->
            <? } ?>
				<div class="input-group alias-field">
					<input type="text" class="form-control" name="aliases[]" value="">
					<div class="input-group-btn">
				        <button type="button" class="btn btn-default dropdown-toggle no-icon" data-toggle="dropdown">Find me on <span class="caret"></span></button>
				        <ul class="dropdown-menu pull-right">
				            <? $lastType = null; foreach($all_aliases as $alias) {?>
			            	<?if($lastType != null && ($lastType != $alias->getType())) { ?>
			            	<li class="divider"></li>
			            	<? } ?>
							<li><span class="member-social-icon iconlhs-<?=substr(strtolower(preg_replace("/[^0-9a-zA-Z]/","",$alias->getId())),0,14);?>" title="<?=$alias->getId()?>"></span><?=$alias->getId()?></li>
				            <? $lastType = $alias->getType();
							} ?>
				        </ul>
				        <button title="remove" type="button" class="btn btn-default alias-remove">x</button>
				    </div><!-- /btn-group -->
				</div><!-- /input-group -->
			</div>
			<button class="btn btn-default add-alias">+ Add another alias</button>			
	    </div>
	
	    <div class="form-group">
	        <strong>Projects I'm working on</strong><br/>
	        <small>The most commonly asked question in the hackspace. What are you doing? Keep it short and sweet.</small>
	        <textarea id="description" name="description" class="form-control" rows="3"><?=stripslashes($user_profile->getDescription())?></textarea>
	    </div>

	    <div class="form-group interests">
	        <strong>Interests</strong><br/>
        	<small>What else brings you here? Select those which are relevant.</small>
	        <div class="row">
	            <? 
	            $interest_category = ''; 
	            $interest_count = 0;
	            foreach($user->getInterests() as $interest) {
	            	$selected = $my_interests->filter(array('getInterestId=' => $interest->getInterestId()))->count();
	            ?>
					<? if($interest_category != $interest->getCategory()) { 
						$interest_category = $interest->getCategory();
					?>
						<? if($interest_count > 0) { ?>
					</div>
				    <? } ?>
		        	<div class="col-md-3">
		        		<h5><?=$interest->getCategory() ?></h5>
				    <? } ?>
						<div class="checkbox restyle">
							<label <? if($selected) { echo 'class="selected"'; } ?>><input type="checkbox" <? if($selected) { echo 'checked="checked"'; } ?> name="interests[<?=$interest->getInterestId() ?>]" id="trained[<?=$interest->getInterestId() ?>]"> <?=$interest->getName() ?></label>
						</div>
					<? $interest_count++; ?>
		        <? } ?>
				</div>
	        </div>
      		<strong>Other interests</strong><br/>
      		<small>Comma separated list</small><br/>
      		<div class="other-interests-container">
	        <input type="text" id="other_interests" name="other_interests" class="form-control" value="<? foreach($my_interests as $interest) { if($interest->getCategory() == 'Other') { echo $interest->getName().','; } } ?>" />
			</div>
	    </div>	
	    <div class="form-group">
	        <input type="submit" name="submit" value="Update profile" class="btn btn-primary update-profile"/>
	    </div>
	</div>
</div>

</form>
<? require('../footer.php'); ?>
<script type="text/javascript" src="/javascript/bootstrap-tagsinput.min.js"></script>
<script type="text/javascript" src="/javascript/typeahead.min.js"></script>
<script>
window.onload = function() {

// add training features
$(".member-training .dropdown-menu li").bind('click touchend', function(e){
    e.stopPropagation();
    e.preventDefault();

	$(this).parents('.btn-group').removeClass('open');
	$('.member-training .training-badges').append('<div class="remove-img"><img src="'+$(this).find('img').attr('src')+'"/><input type="hidden" name="learnings['+$(this).find('img').data('lid')+']" value="'+$(this).find('img').data('name')+'" /></div>');
	addTrainingRemoveEvent($('.member-training .remove-img:last-child'));
    return false;
});
function addTrainingRemoveEvent(obj) {
	obj.bind('click touchend', function(e) {
		e.preventDefault();
		$(this).unbind().remove();
	});
}
addTrainingRemoveEvent($('.member-training .remove-img'));

// add aliases features
$(".aliases .dropdown-menu li").not('.divider').bind('click touchend', function(e){
    e.stopPropagation();
    e.preventDefault();

	$(this).parents('.input-group-btn').removeClass('open');
	$(this).parents('.input-group-btn').find('.dropdown-toggle').removeClass('no-icon').html($(this).text()+' <span class="caret"></span>');
	$(this).find('span').clone().appendTo($(this).parents('.input-group-btn').find('.dropdown-toggle'));
	$(this).parents('.alias-field').find('input').attr('name','aliases['+$(this).text()+']');
    return false;
});
$('.add-alias').bind('click touchend', function(e) {
	e.preventDefault();
	$('.alias-field:first-child').clone(true, true).appendTo( ".alias-fields" );
	$('.alias-field:last-child').find('.dropdown-toggle').addClass('no-icon').html('Find me on <span class="caret"></span>');
	$('.alias-field:last-child').find('input').attr('name','aliases[]').val('');
});
$('.alias-remove').bind('click touchend', function(e) {
	e.preventDefault();
	$(this).parents('.alias-field').remove();
});

// add intersts features
$('#other_interests').tagsinput({confirmKeys: [188, 13]});
var other_interests = [<? 
	$all_other = fRecordSet::build('Interest',array('category='=>'Other'),array('name'=>'asc')); 
	$count = 0;
	foreach($all_other as $other) {
		if($count != 0)
			echo ',';

		echo "\n'".$other->getName()."'";
		$count++;
	}
?>];
$('#other_interests').tagsinput('input').typeahead({
  local: other_interests
}).bind('typeahead:selected', $.proxy(function (obj, datum) {  
	this.tagsinput('add', datum.value);
	this.tagsinput('input').typeahead('setQuery', '');
}, $('#other_interests')));

$('.update-profile').bind('click touchend', function(e){
	$('#other_interests').tagsinput('add', $('#other_interests').tagsinput('input').val());
	$('#other_interests').tagsinput('input').val('');
});

// all checkboxes add a class to parent
$('input[type="checkbox"]').bind('change',function() {
	$(this).parent().toggleClass('selected');
});

// photo upload feature
$('.member-avatar #photo-filesystem').bind("change", handleFiles);
$('.member-avatar #photo-select').bind("click touchend", function (e) {
    e.stopPropagation();
    e.preventDefault();

    $('.member-avatar #photo-filesystem').click();
    return false;
});

var canvas = document.createElement('canvas'), ctx = canvas.getContext("2d");
var canvas_sml = document.createElement('canvas'), ctx_sml = canvas_sml.getContext("2d");
var canvas_med = document.createElement('canvas'), ctx_med = canvas_med.getContext("2d");
function handleFiles(e) {
    var reader = new FileReader;
    reader.onload = function (event) {
        var img = new Image();
        img.src = reader.result;
        img.onload = function () {
            canvas.width = 256;
            canvas.height = 256;
            var dimensions = getImageDimensionsSquare(256,256,img.width,img.height);
            ctx.drawImage(this, dimensions.sourceX, dimensions.sourceY, dimensions.sourceWidth, dimensions.sourceHeight, dimensions.destX, dimensions.destY, dimensions.destWidth, dimensions.destHeight);

            canvas_sml.width = 48;
            canvas_sml.height = 48;
            var dimensions = getImageDimensionsSquare(48,48,img.width,img.height);
            ctx_sml.drawImage(this, dimensions.sourceX, dimensions.sourceY, dimensions.sourceWidth, dimensions.sourceHeight, dimensions.destX, dimensions.destY, dimensions.destWidth, dimensions.destHeight);

            canvas_med.width = 80;
            canvas_med.height = 80;
            var dimensions = getImageDimensionsSquare(80,80,img.width,img.height);
            ctx_med.drawImage(this, dimensions.sourceX, dimensions.sourceY, dimensions.sourceWidth, dimensions.sourceHeight, dimensions.destX, dimensions.destY, dimensions.destWidth, dimensions.destHeight);

            // The resized file ready for upload
            var finalFile = canvas.toDataURL("image/png");
            var sml = canvas_sml.toDataURL("image/png");
            var med = canvas_med.toDataURL("image/png");
			$('.member-avatar img').attr('src',finalFile);
			$('.member-avatar #photo-upload').val(finalFile);
			$('.member-avatar #photo-upload-sml').val(canvas_sml.toDataURL("image/png"));
			$('.member-avatar #photo-upload-med').val(canvas_med.toDataURL("image/png"));
			$('.member-avatar #photo-select').blur();
			$('.member-avatar small').removeClass('hidden');
        }
    }
    reader.readAsDataURL(e.target.files[0]);
}

function getImageDimensionsSquare(maxWidth, maxHeight, imageWidth, imageHeight) {
    if(imageHeight >= imageWidth) {
        return {sourceX : 0,
        	sourceY : (imageHeight-imageWidth)/2,
        	sourceWidth : imageWidth,
        	sourceHeight : imageWidth,
        	destX : 0,
        	destY : 0,
        	destWidth : maxWidth,
        	destHeight : maxHeight};
    }
    if(imageWidth > imageHeight) {
        return {sourceX : (imageWidth-imageHeight)/2,
        	sourceY : 0,
        	sourceWidth : imageHeight,
        	sourceHeight : imageHeight,
        	destX : 0,
        	destY : 0,
        	destWidth : maxWidth,
        	destHeight : maxHeight};
    }	
}
}
</script>
</body>
</html>
