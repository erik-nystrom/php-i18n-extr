<?php 

/**
 * Sample file for extraction tests, anything in _() will be caught
 */

$text = _('this is some sample text');
$text2 = _('this is some more sample text');
$text2 = _('this is yet some more sample text');
$text3 = _('this is some sample text');

/**
 *  Anything else will *not* be caught
 */ 
$text4 = __('Sample text that should be ignored, unless checking for __');

?>