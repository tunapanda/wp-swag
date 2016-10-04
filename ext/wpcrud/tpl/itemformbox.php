<script>
	EMPTY_IMAGE_URL="<?php echo $emptyImageUrl; ?>";
</script>

<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
	<tbody>
		<?php foreach ($fields as $field) { ?>
			<tr class="form-field">
				<th valign="top" scope="row">
					<label for="<?php echo $field["field"]; ?>"><?php echo $field["label"]; ?></label>
				</th>
				<td>
					<?php if ($field["spec"]->type=="select") { ?>
						<select id="<?php echo $field["field"] ?>"
								name="<?php echo $field["field"] ?>">
							<?php foreach ($field["spec"]->options as $key=>$value) { ?>
								<option value="<?php echo $key; ?>"
									<?php if ($field["value"]==$key) { ?>
										selected="true"
									<?php } ?>
								>
									<?php echo $value; ?>
								</option>
							<?php } ?>
						</select>
					<?php } else if ($field["spec"]->type=="timestamp") { ?>
						<input id="<?php echo $field["field"] ?>" 
								name="<?php echo $field["field"] ?>" 
								type="text" 
								style="width: 95%" 
								value="<?php echo esc_attr($field['value'])?>"
								size="50" placeholder="" 
								class="code wpcrud-timestamp"/>
					<?php } else if ($field["spec"]->type=="textarea") { ?>
						<textarea id="<?php echo $field["field"] ?>"
							name="<?php echo $field["field"] ?>"
							style="width: 95%; height: 100px" class="code"
						><?php echo esc_attr($field['value'])?></textarea>
					<?php } else if ($field["spec"]->type=="media-image") { ?>
						<input type="hidden" 
							name="<?php echo $field["field"] ?>"
							id="<?php echo $field["field"] ?>"
							value="<?php echo esc_attr($field['value'])?>"/>
						<div class="wpcrud-media-image-holder"
							media-image-id="<?php echo $field["field"]; ?>">
							<a class="wpcrud-media-image-link" href="#"
								media-image-id="<?php echo $field["field"]; ?>">
								<img class="wpcrud-media-image"
									src="<?php echo $field["src"]; ?>"
									id="<?php echo $field["field"] ?>-image"
									style="height: 75px"
									media-image-id="<?php echo $field["field"] ?>"/>
							</a>
							<img src="<?php echo $deleteIconUrl; ?>"
								class="wpcrud-media-image-delete-button"
								style="width: 20px; height: 20px; display: none"
								media-image-id="<?php echo $field["field"]; ?>"
								id="<?php echo $field["field"] ?>-delete-button"/>
						</div>
					<?php } else if ($field["spec"]->type=="label") { ?>
						<?php echo $field["value"]; ?>
						<input id="<?php echo $field["field"] ?>"
								name="<?php echo $field["field"] ?>" 
								value="<?php echo esc_attr($field['value'])?>"
								type="hidden"/>
					<?php } else { ?>
						<input id="<?php echo $field["field"] ?>" 
								name="<?php echo $field["field"] ?>" 
								type="text" 
								style="width: 95%" 
								value="<?php echo esc_attr($field['value'])?>"
								size="50" class="code" placeholder=""/>
					<?php } ?>
					<?php if ($field["description"]) { ?>
						<p class="description"><?php echo $field["description"]; ?></p>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>