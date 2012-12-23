<div class="pull-right" style="margin-bottom:10px;">
  <a href="/?page=new-activity" class="btn btn-primary"><i class="icon-plus icon-white"></i> Ajouter</a>
</div>

<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>Activité</th>
      <th>Visible</th>
      <th>Tarif</th>
      <th>Tarif jeune</th>
      <th> </th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($activities as $act): ?>
    <tr <?php echo $act->active() == 0 ? 'class="muted"' : null; ?>>
      <td><?php echo $act->id(); ?></td>
      <td><a href="/?page=activity&id=<?php echo $act->id(); ?>"><?php echo $act->name(); ?></a></td>
      <td><?php echo ($act->active() == 1) ? '<a href="'. _FSC_ .'/?page=activite&id='. $act->id() .'" target="_blank"><i class="icon-ok"></i></a>' : '<i class="icon-ban-circle"></i>' ; ?></td>
      <td><?php echo $act->price(); ?> €</td>
      <td><?php echo ($act->price_young() == -1) ? '&ndash;' : $act->price_young() .' €'; ?></td>
      <td style="width: 80px;">
        <div class="btn-group">
          <a class="btn dropdown-toggle btn-small" data-toggle="dropdown" href="#"><i class="icon-edit"></i> 
          <span class="caret"></span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="/?page=activity&id=<?php echo $act->id(); ?>"><i class="icon-eye-open"></i> Voir</a></li>
            <li><a href="/?page=edit-activity&id=<?php echo $act->id(); ?>"><i class="icon-pencil"></i> Modifier</a></li>
            <li class="divider"></li>
            <li><a href="#confirmBox<?php echo $act->id(); ?>" role="button" data-toggle="modal"><i class="icon-trash"></i> Supprimer</a></li>
          </ul>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php foreach ($activities as $act): ?>
<div id="confirmBox<?php echo $act->id(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="ConfirmDelActivity<?php echo $act->id(); ?>" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
<h3 id="ConfirmDelActivity<?php echo $act->id(); ?>"><?php echo $act->name(); ?></h3>
</div>
<div class="modal-body">
<p class="text-error">Êtes-vous sûr de vouloir supprimer cette activité ?</p>
</div>
<div class="modal-footer">
<a class="btn" data-dismiss="modal" aria-hidden="true"/>Annuler</a>
<a href="/?page=activity&id=<?php echo $act->id(); ?>&action=delete" class="btn btn-danger">Confirmer</a>
</div>
</div>
<?php endforeach; ?>