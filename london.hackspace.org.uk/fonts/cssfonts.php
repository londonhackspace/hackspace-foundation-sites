<?php

header('Content-type: text/css');

$families = array(
    'Open Sans' => array(
        array('weight' => 400, 'ttf' => 'OpenSans-Regular.ttf'),
        array('weight' => 700, 'ttf' => 'OpenSans-Bold.ttf'),
    )
);

foreach ($families as $name => $fonts) {
    foreach ($fonts as $font) {
        $data = file_get_contents($font['ttf']);
?>
@font-face {
  font-family: '<?=$name?>';
  font-style: normal;
  font-weight: <?=$font['weight'] ?>;
  src: url(data:font/ttf;base64,<?=base64_encode($data) ?>);
}
<?php
    }
}

