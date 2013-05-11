
<form action="<?php echo $form->action(); ?>" method="<?php echo $form->method();?>" class="form-horizontal" >
  <?php echo ($form->legend() != null ? '<legend>'. $form->legend() .'</legend>' : null); ?>
  
  <!-- form messages -->
  <?php
    if (isset($_SESSION['form_msg'])) {
      echo '<div class="control-group"><div class="controls input-xxlarge">', $_SESSION['form_msg'], '</div></div>';
      unset($_SESSION['form_msg']);
    }
  ?>
  <!-- /form messages -->
  
  <div class="control-group">
    <label class="control-label" for="adherent">Adhérent</label>
    <div class="controls">
      <select class="input-xlarge" name="adherent" id="adherent">
        <?php echo $form->select('adherent', $form->input('adherent')); ?>
      </select>
    </div>
  </div>
  
  <div class="form-actions">
    <input type="submit" class="btn btn-primary" value="<?php echo $form->submit(); ?>" /> 
    <input type="reset" class="btn" value="Annuler" />
  </div>
  
</form>