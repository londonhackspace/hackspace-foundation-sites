<div class="interests">
  <h4>Interests</h4>
  <div class="list">
  <? $interest_category = '';
     $interest_count = 0;
     foreach($this_user->buildInterests() as $interest) {
      if ($interest_category != $interest->getCategory()) {
        $interest_category = $interest->getCategory();
        if ($interest_count > 0) {
          # Finish previous category
          echo '</ul></div>';
        }
      ?>
        <div>
          <h5><?=$interest->getCategory() ?></h5>
          <ul>
      <? } ?>
            <li>
              <? if($interest->getUrl() != null && $interest->getUrl() != '') { ?>
                <a href="<?=htmlentities($interest->getUrl())?>" target="_blank"><?=$interest->getName() ?></a>
              <? } else { ?>
                <?=$interest->getName() ?>
              <? } ?>
            </li>
    <? $interest_count++;
    } ?>
      </ul>
      </div>
  </div>
</div>
<div style="clear:both"></div>
