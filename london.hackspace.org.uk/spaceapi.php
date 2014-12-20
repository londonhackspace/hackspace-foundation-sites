<?php
$data = array(
    'api' => '0.13',
    'space' => 'London Hackspace',
    'logo' => 'https://london.hackspace.org.uk/images/london.png',
    'url' => 'https://london.hackspace.org.uk',
    'location' => array(
        'address' => '447 Hackney Road, London E2 9DY',
        'lat' => '51.5321',
        'long' => '-0.0607'
    ),
    'spacefed' => array(
        'spacenet' => true,
        'spacesaml' => false,
        'spacephone' => false
    ),
    'state' => array(
        'open' => null
    ),
    'contact' => array(
        'irc' => 'ircs://chat.freenode.net/london-hack-space',
        'twitter' => 'londonhackspace',
        'email' => 'contact@london.hackspace.org.uk',
        'ml' => 'london-hack-space@googlegroups.com',
    ),
    'issue_report_channels' => array(
        'email'
    ),
    'feeds' => array(
        'wiki' => array(
            'url' => 'https://wiki.london.hackspace.org.uk/w/api.php?hidebots=1&amp;days=7&amp;limit=50&amp;action=feedrecentchanges&amp;feedformat=atom',
            'type' => 'atom'
        ),
        'calendar' => array(
            'url' => 'https://www.google.com/calendar/ical/gc1bopmh3c5n0ogvlo6ceujlkc%40group.calendar.google.com/public/basic.ics',
            'type' => 'ical'
        )
    )
);

echo json_encode($data);
?>
