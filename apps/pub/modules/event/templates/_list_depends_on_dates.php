<?php if ( $event->Manifestations->count() > 0 ): ?>
<ul class="no-bullet">
<?php foreach ( $event->Manifestations as $manif ): ?>
  <li class="month-<?php echo format_date(strtotime($manif->happens_at), 'yyyyMM') ?>" data-time="<?php echo strtotime($manif->happens_at) ?>">
    <?php echo $manif->DependsOn->getFormattedDate() ?>
  </li>
<?php endforeach ?>
</ul>
<?php endif ?>

