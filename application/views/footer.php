		<div class="footer">
		   <p>&copy; 2018 Glance Design Dashboard. All Rights Reserved | Design by <a href="https://w3layouts.com/" target="_blank">w3layouts</a></p>
		</div>
<?php 
	if (isset($GLOBALS['message'])) { 
		$message=$GLOBALS['message'];
		unset($GLOBALS['message']); 
?>
<script>
  sweet_alert("<?=$message['type']?>", "<?=$message['title']?>", "<?=$message['text']?>")
</script>
<?php  
	} 
?>