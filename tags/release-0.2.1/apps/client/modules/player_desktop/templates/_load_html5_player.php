<?php
#
# Loading an appropriate HTML5 player will change as the standard gets better, add cases here
#

//Progressive/Predictive buffering in Chrome is far better with a video tag
if( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Chrome' ))
{
  echo '<video preload="none" controls="" id="musicplayer" class="chrome"></video>' . "\r\n";
}
//Safari codec set on windows isn't as good in the audio tag
else if( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Safari' ) && strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Windows' ))
{
  echo '<video preload="none" controls="" id="musicplayer" class="safari-win"></video>' . "\r\n";
}
//Safari can use audio just fine on the mac..the player looks quite different
else if( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Safari' ) && strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Macintosh' ))
{
  echo '<audio preload="none" controls="" id="musicplayer" class="safari-mac"></audio>' . "\r\n";
}
//Safari ccan use audio just fine on the mac..the player looks quite different
else if( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Safari' ) && strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'iPad; U' ))
{
  echo '<audio preload="none" controls="" id="musicplayer" class="safari-ipad"></audio>' . "\r\n";
}
//firefox has an oddly shaped audio player - add a separate stylesheet
else if( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Firefox' ))
{
  echo '<audio preload="none" controls="" id="musicplayer" class="firefox"></audio>' . "\r\n";
}
//audio for all other HTML5 implementations
else
{
  echo '<audio preload="none" controls="" id="musicplayer"></audio>';
}
?>