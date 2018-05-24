<div class="footer">
   <p>&copy; 2018 fzuacm. All Rights Reserved | Powered by <a href="http://and-who.cn/" target="_blank">andwho</a></p>
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