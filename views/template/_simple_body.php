<?php
#css
ob_start();
?>
.brand_desc{
color:#606060;
font-family: Arial;
font-size:.9em;
line-height:1.5em;
}

h1{
font-weight:bold;
color:#343760;
font-family: Arial;
font-size:1.2em;
text-transform:uppercase;
}

h4{
color:#333e6b;
font-weight:bold;
font-family: Arial;
font-size:1.2em;
text-transform:uppercase;
}

.button_container tr td{
	background-color:#eeeeee;
}

.button_container tr td a{
	color:#fff;
	text-transform:uppercase;
	text-decoration:none;
	font-size:0.85em;
	font-weight:bolder;
}

<?php
$css = ob_get_contents();
ob_end_clean();


#markup
ob_start();
?>
<h1>Lorem ipsum dolor sit amet.</h1>
<table width="100%">
	<tr>
		<td class="mid_left" align="center">
			<?php
			/*we need to be able to change this image dynamically*/
			?>
			<img src="http://placehold.it/360x268" alt="" border="0"/>
		</td>
		<td class="mid_right">
			<?php echo $content;?>
			<br />
			<table class="button_container" cellpadding="10">
				<tr>
					<td>
						Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
					</td>
				</tr>
			</table>
			<br />
		</td>
	</tr>
</table>
<?php
$markup = ob_get_contents();
ob_end_clean();

$email = new CSSToInlineStylesHelper($markup, $css);
echo $email->convert();