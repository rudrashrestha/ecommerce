
<script type="text/javascript">
function areyousure()
{
	return confirm('<?php echo lang('confirm_delete_coupon');?>');
}
</script>


<div class="pageheader">
   
	<div class="pageicon"><span class="iconfa-table"></span></div>
	<div class="pagetitle">
		<h5>Catalog</h5>
		<h1>Coupons Management</h1>
	</div>
</div><!--pageheader-->

<div class="maincontent">
<div class="maincontentinner">
    
    
    
    <div style="text-align:right">
     <a class="btn" style="float:right;" href="<?php echo site_url('admin/coupons/form'); ?>"><i class="icon-plus-sign"></i> <?php echo lang('add_new_coupon');?></a>
    </div>
	
	


<table class="table">
	<thead>
		<tr>
		  <th><?php echo lang('code');?></th>
		  <th><?php echo lang('usage');?></th>
		  <th></th>
		</tr>
	</thead>
	<tbody>
	<?php echo (count($coupons) < 1)?'<tr><td style="text-align:center;" colspan="3">'.lang('no_coupons').'</td></tr>':''?>
<?php foreach ($coupons as $coupon):?>
		<tr>
			<td><?php echo  $coupon->code; ?></td>
			<td>
			  <?php echo  $coupon->num_uses ." / ". $coupon->max_uses; ?>
			</td>
			<td>
				<div class="btn-group" style="float:right;">
					<a class="btn" href="<?php echo site_url('admin/coupons/form/'.$coupon->id); ?>"><i class="icon-pencil"></i> <?php echo lang('edit');?></a>
					<a class="btn btn-danger" href="<?php echo site_url('admin/coupons/delete/'.$coupon->id); ?>" onclick="return areyousure();"><i class="icon-trash icon-white"></i> <?php echo lang('delete');?></a>
				</div>
			</td>
	  </tr>
<?php endforeach; ?>
	</tbody>
</table>

<div class="divider15"></div>
</div>
</div>

 


