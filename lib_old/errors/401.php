<?php header("Content-Type: text/html; charset=iso-8859-1") ?>
<?php header("HTTP/1.0 404 Not Found") ?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>401 Authorization Required</title>
</head><body>
<h1>Authorization Required</h1>
<p>This server could not verify that you
are authorized to access the document
requested.  Either you supplied the wrong
credentials (e.g., bad password), or your
browser doesn't understand how to supply
the credentials required.</p>
<hr>
<?php echo $_SERVER['SERVER_SIGNATURE']?>
</body></html>
<?php exit?>
