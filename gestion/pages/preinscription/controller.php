<?php

use lib\content\Page;
use lib\users\UserInscription;
use lib\preinscriptions\Preinscription;
use lib\preinscriptions\FutureParticipant;
use lib\content\Message;
use lib\content\Display;
use lib\activities\Activity;
use lib\activities\Schedule;

function quit() {
  header('Location: '. _GESTION_ .'/?page=preinscriptions');
  exit();
}

if (isset($_GET['id']) && Preinscription::isMember($_GET['id']+0)) {
  
  $pre = new Preinscription($_GET['id']+0);
  $account = new UserInscription($pre->id_user_inscription());
  $account->checkStatus();
  if ($pre->minor())
    $respo = new Preinscription($pre->responsible());
    
  $pageInfos = array(
   'name' => $pre->name(),
   'url' => _GESTION_.'/?page=preinscriptions'
  );
  $page = new Page($pageInfos['name'], $pageInfos['url'], 
    array(
      array('name' => 'Préinscriptions', 'url' => '?page=preinscriptions'),
      array('name' => $account->login(), 'url' => '?page=preinscriptions&amp;account='.$account->id()),
      $pageInfos)
  );

  // generation des buttons du membre en fonction de son status
  function getButtonsMember() {
    global $pre;
    $result = '<div class="btn-group"><a ';
    if ($pre->status() ==  Preinscription::AWAITING)
      $result .= 'href="'. _GESTION_ .'/?page=validate-preinscription&amp;id='. $pre->id() .'" title="Valider la préinscription"';
    $result .= ' class="btn btn-small';
    if ($pre->status() !=  Preinscription::AWAITING)
      $result .= ' disabled';
    $result .= '"><i class="icon-plus"></i></a></div>'. PHP_EOL;
    $result .= '<div class="btn-group">';
    $result .= '<a ';
    if ($pre->status() ==  Preinscription::AWAITING)
      $result .= 'href="'. _GESTION_ .'/?page=edit-preinscription&amp;id='. $pre->id() .'" title="Modifier"';
    $result .= ' class="btn btn-small';
    if ($pre->status() !=  Preinscription::AWAITING)
      $result .= ' disabled';
    $result .= '"><i class="icon-pencil"></i></a>';
    $result .= '<a href="#confirmRemoveInscription'. $pre->id() .'" role="button" data-toggle="modal" title="Supprimer" class="btn btn-small"><i class="icon-trash"></i></a>';
    $result .= '</div>';
    return $result;

  }
  // generation du cadre (pre-)adhérent ou (pre-)membre
  function  getWellMember() {
    global $pre;
    $result = '<div class="alert';
    switch ($pre->status()) {
      case Preinscription::VALIDATED:
      case Preinscription::INCOMPLETE:
        $result .= (!$pre->adherent() ? ' alert-warning' : ' alert-success');
        $result .= '"><strong>'. ($pre->adherent() ? 'Adhérent' : 'Membre') .'</strong> [<a href="?page=member&amp;id='. $pre->id_member() .'">#'. $pre->id_member() .'</a>]';
        break;

      case Preinscription::AWAITING;
        $result .= ($pre->adherent() ? ' alert-info' : ' alert-well');
        $result .= '">'. ($pre->adherent() ? 'Pré-adhérent' : 'Pré-membre');
        break;


      default:
        $result .= ' alert-error"><strong>Rejected</strong>';
    }
    $result .= '</div>';
    return $result;
  }
  // generation des boutons des activités en fonction du status (préinscription & participation)
  function getButtonsParticipant($p) {
    global $pre;
    switch ($pre->status()) {
      case Preinscription::AWAITING:
        return '<em>Préinscription non validée</em>';
        break;

      case Preinscription::INCOMPLETE:
      case Preinscription::VALIDATED:
        if ($p->status() == Preinscription::AWAITING)
          return '<a href="?page=validate-future-participant&amp;id='. $p->id() .'" class="btn btn-small"><i class="icon-plus"></i></a> <a href="#confirmBoxP'. $p->id() .'"  role="button" data-toggle="modal" class="btn btn-small"><i class="icon-trash"></i></a>';
        return '<a class="btn btn-small disabled"><i class="icon-plus"></i></a> <a class="btn btn-small disabled"><i class="icon-trash"></i></a>';
        break;

      case Preinscription::REJECTED:
      default:
        return 'not allowed';
        break;
    }
  }


  // Activités
  $count_activites = FutureParticipant::countActivities($pre->id());
  $plural_count_activities = $count_activites > 1 ? 's' : '';
  
  $display_participatitions = '';
  if ($count_activites >= 1) {
    $display_participatitions = '<table class="table table-striped"><thead>
      <tr>
        <th>Activité</th>
        <th>Horaire</th>
        <th class="center">Statut</th>
      </tr>
      </thead><tbody>
    ';
    foreach (FutureParticipant::Activities($pre->id()) as $p) {
      $a = new Activity($p->activity());
      if (!$a->aggregate())
        $s = new Schedule($p->schedule());
      $horaire = isset($s) && $a->aggregate() == 0 ? Display::Day($s->day()).' &rsaquo; '. $s->time_begin() .' à '. $s->time_end() . ($s->more() != NULL ? '&nbsp; &nbsp;('.$s->more().')' : '') : '<em>Pratique libre</em>';
      $horaire = isset($s) && $s->description() != NULL ? $s->description() : $horaire;
      $display_participatitions .= '
        <tr>
          <td><a href="'. _GESTION_ .'/?page=activity&amp;id='. $a->id() .'">'. $a->name() .'</a></td>
          <td>'. $horaire .'</td>
          <td class="center status">'. Preinscription::StatusTooltip($p->status()) .'</td>
          <td class="center">'. getButtonsParticipant($p) .'</td>
        </tr>
      ';
    }
    $display_participatitions .= '</tbody></table>';
    $_SCRIPT[] = '<script>$(function(){ $(\'table td.status span\').tooltip(); });</script>';

    foreach (FutureParticipant::Activities($pre->id()) as $p)
      $display_participatitions .= '
      <div id="confirmBoxP'. $p->id() .'" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="ConfirmDelParticipant'. $p->id() .'" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 id="ConfirmDelParticipant'. $p->id() .'">'. $pre->name() .'</h3>
        </div>
        <div class="modal-body">
          <p class="text-error">Êtes-vous sûr de vouloir désinscrire ce membre de l’activité ?</p>
        </div>
        <div class="modal-footer">
          <a class="btn" data-dismiss="modal" aria-hidden="true">Annuler</a>
          <a href="'. _GESTION_ .'/?page=validate-future-participant&amp;id='. $p->id() .'&amp;action=delete" class="btn btn-danger">Confirmer</a>
        </div>
      </div>
      ';
  }
  else {
    $display_participatitions = 'Aucune';
  }


  $_SCRIPT[] = '<script>$(function(){ $(\'.status-tooltip span\').tooltip({"placement": "right"}); });</script>';

  
}
else {
  quit();
}

?>