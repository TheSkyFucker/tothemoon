<div class="footer">
   <p>&copy; 2018 Glance Design Dashboard. All Rights Reserved | Design by <a href="https://w3layouts.com/" target="_blank">w3layouts</a></p>
</div>

<?php 
	if (isset($_SESSION['msg'])) 
	{ 
		$msg = $_SESSION['msg'];
		unset($_SESSION['msg']); 
?>
<script>
  sweet_alert("<?=$msg['type']?>", "<?=$msg['title']?>", "<?=$msg['text']?>")
</script>
<?php  
	} 
?>