<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button type="button" onclick="searchProducts();" data-toggle="tooltip" title="<?php echo $button_parse; ?>" class="btn btn-success" id="button-parse"><i class="fa fa-play"></i></button>
				<button type="submit" form="form" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
				<a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
			<h1><?php echo $heading_title; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb) { ?>
				<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
		<?php if ($error_warning) { ?>
		<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<?php if ($success) { ?>
		<div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
			<button type="button" class="close" data-dismiss="alert">&times;</button>
		</div>
		<?php } ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><i class="fa fa-cogs"></i> <?php echo $text_edit; ?></h3>
			</div>
			<div class="panel-body">
				<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form" class="form-horizontal">
					<ul class="nav nav-tabs">
						<li class="active"><a href="#tab-settings" data-toggle="tab"><i class="fa fa-cog"></i> <?php echo $tab_settings; ?></a></li>
						<li><a href="#tab-help" data-toggle="tab"><i class="fa fa-comment"></i> <?php echo $tab_help; ?></a></li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="tab-settings">
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-pvnm-parser-status"><?php echo $entry_status; ?></label>
								<div class="col-sm-10">
									<div class="btn-group" data-toggle="buttons">
										<?php if ($pvnm_parser_status) { ?>
										<label class="btn btn-info active"><input type="radio" name="pvnm_parser_status" value="1" autocomplete="off" checked="checked"><?php echo $text_enabled; ?></label>
										<label class="btn btn-info"><input type="radio" name="pvnm_parser_status" value="0" autocomplete="off"><?php echo $text_disabled; ?></label>
										<?php } else { ?>
										<label class="btn btn-info"><input type="radio" name="pvnm_parser_status" value="1" autocomplete="off"><?php echo $text_enabled; ?></label>
										<label class="btn btn-info active"><input type="radio" name="pvnm_parser_status" value="0" autocomplete="off" checked="checked"><?php echo $text_disabled; ?></label>
										<?php } ?>
									</div>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label" for="input-pvnm-parser-product-limit"><?php echo $entry_product_limit; ?></label>
								<div class="col-sm-10">
									<input type="text" name="pvnm_parser_product_limit" value="<?php echo $pvnm_parser_product_limit; ?>" placeholder="<?php echo $entry_product_limit; ?>" id="input-pvnm-parser-product-limit" class="form-control" />
								</div>
							</div>
							<table id="pvnm-parser-category" class="table table-striped table-bordered table-hover">
								<thead>
									<tr>
										<td class="text-left"><?php echo $entry_category; ?></td>
										<td class="text-left"><?php echo $entry_attribute_group; ?></td>
										<td class="text-left"><?php echo $entry_donor; ?></td>
										<td class="text-left"><?php echo $entry_limit; ?></td>
										<td></td>
									</tr>
								</thead>
								<tbody>
									<?php $category_row = 0; ?>
									<?php if (!empty($pvnm_parser_category)) { ?>
									<?php foreach ($pvnm_parser_category as $parser_category) { ?>
									<tr id="pvnm-parser-category-row<?php echo $category_row; ?>">
										<td class="text-left">
											<select name="pvnm_parser_category[<?php echo $category_row; ?>][category_id]" class="form-control">
												<option value="0" selected="selected"><?php echo $text_none; ?></option>
												<?php foreach ($categories as $category) { ?>
												<?php if ($category['category_id'] == $parser_category['category_id']) { ?>
												<option value="<?php echo $category['category_id']; ?>" selected="selected"><?php echo $category['name']; ?></option>
												<?php } else { ?>
												<option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
												<?php } ?>
												<?php } ?>
											</select>
										</td>
										<td class="text-left">
											<select name="pvnm_parser_category[<?php echo $category_row; ?>][attribute_group_id]" class="form-control">
												<?php foreach ($attribute_groups as $attribute_group) { ?>
												<?php if ($attribute_group['attribute_group_id'] == $parser_category['attribute_group_id']) { ?>
												<option value="<?php echo $attribute_group['attribute_group_id']; ?>" selected="selected"><?php echo $attribute_group['name']; ?></option>
												<?php } else { ?>
												<option value="<?php echo $attribute_group['attribute_group_id']; ?>"><?php echo $attribute_group['name']; ?></option>
												<?php } ?>
												<?php } ?>
											</select>
										</td>
										<td class="text-left">
											<input type="text" name="pvnm_parser_category[<?php echo $category_row; ?>][url]" value="<?php echo $parser_category['url']; ?>" class="form-control" />
										</td>
										<td class="text-right">
											<input type="text" name="pvnm_parser_category[<?php echo $category_row; ?>][limit]" value="<?php echo $parser_category['limit']; ?>" class="form-control" />
										</td>
										<td class="text-right">
											<button type="button" onclick="$('#pvnm-parser-category-row<?php echo $category_row; ?>').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
										</td>
									</tr>
									<?php $category_row++; ?>
									<?php } ?>
									<?php } ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="4"></td>
										<td class="text-right" style="width: 1px;"><button type="button" onclick="addCategory();" data-toggle="tooltip" title="<?php echo $button_add; ?>" class="btn btn-primary"><i class="fa fa-plus-circle"></i></button></td>
									</tr>
								</tfoot>
							</table>
						</div>
						<div class="tab-pane" id="tab-help">
							<div class="form-group">
								<label class="col-sm-2 control-label"><?php echo $text_documentation; ?></label>
								<div class="col-sm-10"><a href="https://github.com/p0v1n0m/owp" target="_blank" class="btn">https://github.com/p0v1n0m/owp</a></div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label"><?php echo $text_developer; ?></label>
								<div class="col-sm-10"><a href="mailto:p0v1n0m@gmail.com" class="btn btn-link">p0v1n0m@gmail.com</a></div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript"><!--
var category_row = <?php echo $category_row; ?>;

function addCategory() {
	html  = '<tr id="pvnm-parser-category-row' + category_row + '">';	
	html += '  <td class="text-left">';
	html += '     <select name="pvnm_parser_category[' + category_row + '][category_id]" class="form-control">';
	html += '     	<option value="0" selected="selected"><?php echo $text_none; ?></option>';
	<?php foreach ($categories as $category) { ?>
	html += '     	<option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>';
	<?php } ?>
	html += '     </select>';
	html += '  </td>';
	html += '  <td class="text-left">';
	html += '     <select name="pvnm_parser_category[' + category_row + '][attribute_group_id]" class="form-control">';
	<?php foreach ($attribute_groups as $attribute_group) { ?>
	html += '     	<option value="<?php echo $attribute_group['attribute_group_id']; ?>"><?php echo $attribute_group['name']; ?></option>';
	<?php } ?>
	html += '     </select>';
	html += '  </td>';
	html += '  <td class="text-left">';
	html += '     <input type="text" name="pvnm_parser_category[' + category_row + '][url]" value="" placeholder="<?php echo $entry_donor; ?>" class="form-control" />';
	html += '  </td>';
	html += '  <td class="text-right">';
	html += '     <input type="text" name="pvnm_parser_category[' + category_row + '][limit]" value="1" placeholder="<?php echo $entry_limit; ?>" class="form-control" />';
	html += '  </td>';
	html += '  <td class="text-right"><button type="button" onclick="$(\'#pvnm-parser-category-row' + category_row + '\').remove();" data-toggle="tooltip" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
	html += '</tr>';	
	
	$('#pvnm-parser-category tbody').append(html);
	
	category_row++;
}

function searchProducts(next = 0, page = 1) {
	$.ajax({
		url: 'index.php?route=module/pvnm_parser/searchProducts&token=' + getURLVar('token'),
		type: 'post',
		data: 'next=' + next + '&page=' + page,
		dataType: 'json',
		beforeSend: function() {
			$('#button-parse').button('loading');
		},
		success: function(json) {
			$('.alert').remove();

			if (json['success']) {
				$('#content > .container-fluid').prepend('<div class="alert alert-info"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}

			if (json['next'] && json['page']) {
				searchProducts(json['next'], json['page']);
			} else {
				parseProducts();
			}

			if (json['error']) {
				$('#content > .container-fluid').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function parseProducts(next = 0) {
	$.ajax({
		url: 'index.php?route=module/pvnm_parser/parseProducts&token=' + getURLVar('token'),
		type: 'post',
		data: 'next=' + next,
		dataType: 'json',
		success: function(json) {
			$('.alert').remove();

			if (json['success']) {
				$('#content > .container-fluid').prepend('<div class="alert alert-info"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}

			if (json['next']) {
				parseProducts(json['next']);
			} else {
				loadProducts();
			}

			if (json['error']) {
				$('#content > .container-fluid').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function loadProducts(next = 0) {
	$.ajax({
		url: 'index.php?route=module/pvnm_parser/loadProducts&token=' + getURLVar('token'),
		type: 'post',
		data: 'next=' + next,
		dataType: 'json',
		complete: function() {
			$('#button-parse').button('reset');
		},
		success: function(json) {
			$('.alert').remove();

			if (json['success']) {
				$('#content > .container-fluid').prepend('<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}

			if (json['next']) {
				loadProducts(json['next']);
			}

			if (json['error']) {
				$('#content > .container-fluid').prepend('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}
//--></script>
<?php echo $footer; ?>