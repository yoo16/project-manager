<?php header("Content-Type: text/html; charset=iso-8859-1")?>
<?php header("HTTP/1.0 500 Internal Server Error")?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>500 Internal Server Error</title>
</head><body>
<h1>Internal Server Error</h1>
<p>The server encountered an internal error or
misconfiguration and was unable to complete
your request.</p>
<p>Please contact the server administrator,
 <?php echo $_SERVER['SERVER_ADMIN']?> and inform them of the time the error occurred,
and anything you might have done that may have
caused the error.</p>
<p>More information about this error may be available
in the server error log.</p>
<hr>
<?php echo $_SERVER['SERVER_SIGNATURE']?>
</body></html>
<?php exit?>
