<?php
$string="John Doe <jdoe@domain.org>";
$s=explode("<",$string);
print trim($s[0]);
?>