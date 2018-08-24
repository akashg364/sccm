<div class="popup-wheat-bg">
<?php 

if(!empty($devices)){
?>
<table class="table table-striped">
	<tr>
		<th>Hostname</th>
		<th>Status</th>
		<th>Errors</th>
	</tr>
	<?php 
	foreach($devices as $device){ 
		$rowClass = $device["status"]=="success"?"success":"danger";
	?>
	<tr class="<?=$rowClass;?>">
		<td><?php echo $device["hostname"];?></td>
		<td><?php echo $device["status"];?></td>
		<td><?php echo $device["errors"];?></td>
	</tr>	
	<?php }
	?>	
</table>
<?php 
}

?>
</div>
