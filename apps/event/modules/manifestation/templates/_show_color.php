<div class="sf_admin_form_row sf_admin_form_field_color">
  <label><?php echo __('Color') ?>:</label>
  <?php if ( $v = $manifestation->current_version ): ?>
  <span class="diff">
    <?php if ( $manifestation->current_version->color_id ): ?>
    <?php $color = Doctrine::getTable('Color')->findOneById($v->color_id) ?>
    <span style="background-color: <?php echo $color ?>; padding: 2px 30px;">
      <?php echo $color->name ?>
    </span>
    <?php else: ?>
      <?php echo 'n/a' ?>
    <?php endif ?>
  </span>
  <?php endif ?>
  <span style="background-color: <?php echo $manifestation->Color ?>; padding: 2px 30px;">
    <?php echo $manifestation->Color->name ?>
  </span>
</div>
