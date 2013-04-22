<?

$badge_url = 'http://www.flickr.com/badge_code_v2.gne?count=9&display=random' .
             '&size=s&layout=h&context=in%2Fpool-1183951%40N23%2F' .
             '&source=group&group=1183951%40N23';


$passed_headers = array(
  'Accept', 'Accept-Charset', 'Accept-Encoding', 'Accept-Language',
  'Cache-Control'
);

// Don't stop caching or diagnostic headers
$returned_headers = array(
  'Date', 'Expires', 'Last-Modified', 'Cache-Control', 'Pragma',
  'X-Served-By', 'Vary', 'Content-Type', 'Server', 'Age', 'Via'
);


$ctx = stream_context_create(array(
  'http' => array(
    'method' => 'GET',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'], // Yahoo always use it
// maybe 'header' => 'Connection: close'
    'timeout' => 5
  )
));


$body = @file_get_contents($badge_url, false, $ctx);

if ($body === false) {
  header("Location: $badge_url"); // Fall back to insecure

} else {
  foreach ($http_response_header as $header) {
    $headername = reset(explode(':', $header, 2));
    if (in_array($headername, $passed_headers))
      header($header);
  }

  $body = preg_replace('/(?<=src=")http/', 'https', $body);
  header('X-SSL-Proxy: badge.php');
  header('Content-Length: ' . strlen($body));
  echo $body;

}
