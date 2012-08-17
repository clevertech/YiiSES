<?php
#css
ob_start();
?>
#contents p{
color:#606060;
font-family: Arial;
font-size:.9em;
line-height:1.5em;
}

h1{
font-weight:bold;
color:#606060;
font-family: Arial;
font-size:1.5em;
}

h4{
color:#333e6b;
font-weight:bold;
font-family: Arial;
font-size:1.2em;
text-transform:uppercase;
}

#footer_container{
background-color:#f0f0ee;
}

#footer tr td{
color:#606060;
font-family: Arial;
text-transform:uppercase;
font-size:0.85em;
}

#tips tr td{
color:#606060;
font-family: Arial;
font-size:0.9em;
}

.small_text{
	color:#606060;
	font-size:0.85em;
}

.unsubscribe_link{
	color:#146089;
	text-decoration:none;
}
<?php
$css = ob_get_contents();
ob_end_clean();


#markup
ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Acme Corporation</title>
</head>
<body style="background-color: #fafafa;" marginheight="0" topmargin="0" marginwidth="0" bgcolor="#fafafa" leftmargin="0">
<table width="100%" border="0">
	<tr>
		<td width="42%" align="left"><img src="http://placehold.it/200x200" alt="" border="0"/></td>
		<td width="58%" align="right">
			<table border="0" cellpadding="5">
				<tr>
					<td align="center">
						<a href="https://twitter.com/" title="Twitter"><img src="http://placehold.it/21x21" alt="" width="21" border="0"/></a>
					</td>
					<td align="center">
						<a href="http://www.facebook.com" title="Facebook"><img src="http://placehold.it/21x21" alt="" width="21" border="0"/></a>
					</td>
					<td align="center">
						<a href="http://pinterest.com/" title=Pinterest"><img src="http://placehold.it/21x21" alt="" width="21" border="0" /></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php echo $content;?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<p class="small_text">
				You are receiving this email because you signed up for {{COMPANY NAME}}. If you believe this has been sent to you in error or don't want to continue receiving emails from us, please safely <a href="<?php echo $unsubscribeUrl?>" title="" class="unsubscribe_link">unsubscribe</a>.
			</p>
		</td>
	</tr>
	<tr>		
		<td colspan="2" id="footer_container">
			<table id="footer" border="0" cellspacing="5">
				<tr>
					<td>{{COMPANY NAME}}</td>
					<td align="center" valign="middle"> | </td>
					<td>{{COMPANY ADDRES}}</td>
					<td align="center" valign="middle"> | </td>
					<td>{{COMPANY CITY}}</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<img src="--IMGSTATS--" width="1" height="1"/>
<?php
$markup = ob_get_contents();
ob_end_clean();

$email = new CSSToInlineStylesHelper($markup, $css);
echo $email->convert();