<?php

use lib\preinscriptions\FutureParticipant;
use lib\content\Message;

if (isset($_SESSION['authentificated']) && $_SESSION['authentificated']) {

  if (isset($_GET['rel']) && FutureParticipant::isParticipant($_GET['rel']+0)) {
    $p = new FutureParticipant($_GET['rel']+0);
    $member = $p->adherent();
    $p->delete(true);
    $_SESSION['msg'] = new Message('Le membre a bien été désinscrit de l’activité', 1, 'Suppression réussie');
    header ('Location: '. _PREINSCRIPTION_ .'/preinscription/'.$member);
    exit();
  }
  else {
    header ('Location: '. _PREINSCRIPTION_ .'/list');
    exit();
  }

}
else {
  header ('Location: '. _PREINSCRIPTION_ .'/login');
  exit();
}
?>