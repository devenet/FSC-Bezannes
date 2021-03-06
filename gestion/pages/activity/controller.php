<?php

use lib\content\Page;
use lib\activities\Activity;
use lib\activities\Schedule;
use lib\members\Member;
use lib\members\Participant;
use lib\members\Referent;
use lib\content\Message;
use lib\content\Display;
use lib\db\SQL;
use lib\preinscriptions\Preinscription;

function quit() {
	header('Location: '. _GESTION_ .'/?page=activities');
	exit();
}

if (isset($_GET['id']) && Activity::isActivity($_GET['id']+0)) {
	
	$act = new Activity($_GET['id']+0);
	
	// suppression activité
	if (isset($_GET['action']) && $_GET['action'] == 'delete') {
		$name = $act->name();
		if ($act->delete(true)) {
			$_SESSION['msg'] = new Message('L’activité <em>'. $name .'</em> a bien été supprimée <i class="icon-meh"></i>', 1, 'Suppression réussie !');
			quit();
		}
		else {
			$_SESSION['msg'] = new Message('Impossible de supprimer l’activité <em>'. $name .'</em>', -1, 'Suppression impossible :/');
			quit();
		}
	}
	// changement activé <-> pas activé
	if (isset($_GET['action']) && $_GET['action'] == 'change-status') {
		if ($act->active()) {
			$act->changeActive();
			$_SESSION['msg'] = new Message('L’activité a bien été désactivée !', 1, 'Changement effectué');
			header ('Location: '. _GESTION_ .'/?page=activity&id='.$act->id());
			exit();
		}
		else {
			if (Schedule::countSchedules($act->id())>0 && Referent::countReferents($act->id())>0) {
				$act->changeActive();
				$_SESSION['msg'] = new Message('L’activité a bien été activée !', 1, 'Changement effectué');
				header ('Location: '. _GESTION_ .'/?page=activity&id='.$act->id());
				exit();
			}
			else {
				$_SESSION['msg'] = new Message('L’activité ne possède aucun horaire ou aucun référent !', -1, 'Impossible d’effectuer le changement');
				header ('Location: '. _GESTION_ .'/?page=activity&id='.$act->id());
				exit();
			}
		}
	}
	// suppression de l'image (miniature)
	if (isset($_GET['action']) && $_GET['action'] == 'delete-image') {
		$img = dirname(__FILE__).'/../../../'._PATH_UPLOADS_FULL_.'/activities/'.$act->id().'.jpg';
		if(unlink($img)) {
			$_SESSION['msg'] = new Message('L’image a bien été supprimée.', 1, 'Suppression réussie !');
				header ('Location: '. _GESTION_ .'/?page=activity&id='.$act->id());
				exit();
		} else {
			$_SESSION['msg'] = new Message('L’application n’a pas été capable de supprimer l’image.', -1, 'Suppression impossible');
				header ('Location: '. _GESTION_ .'/?page=activity&id='.$act->id());
				exit();
		}
	}
	
	
	$pageInfos = array(
	 'name' => $act->name(),
	 'url' => _GESTION_.'/?page=activities'
	);
	$page = new Page($pageInfos['name'], $pageInfos['url'], array(array('name' => 'Activités', 'url' => '?page=activities'), $pageInfos));
	
	/* preparation affichage schedules */
	$display_schedules = '';
	if (Schedule::countSchedules($act->id()) > 0) {
		if (Schedule::countSchedulesDays($act->id()) > 0) {
			$display_schedules .= '
				<table class="table table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>Jour</th>
							<th>Horaire</th>
							<th>Complément</th>
							<th> </th>
						</tr>
					</thead>
					<tbody>
			';
			foreach (Schedule::SchedulesDays($act->id()) as $s) {
				$display_schedules .= '
					<tr>
						<td>'. $s->id() .'</td>
						<td>'. Display::Day($s->day()) .'</td>
						<td>'. $s->time_begin() .' à '. $s->time_end() .'</td>
						<td>'. $s->more() .'</td>
						<td style="width:45px; text-align:center;">
							<a href="./?page=edit-schedule&amp;id='. $s->id() .'" class="normal"><i class="icon-pencil"></i></a>&nbsp;&nbsp;
							<a href="#confirmBoxS'. $s->id() .'" role="button" data-toggle="modal" class="normal"><i class="icon-trash"></i></a>
						</td>
					</tr>
				';
			}
			$display_schedules .= '</tbody></table>';
		}
		if (Schedule::countSchedulesFree($act->id())>0) {
			$display_schedules .= '
				<table class="table table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>Description</th>
							<th> </th>
						</tr>
					</thead>
					<tbody>
			';
			foreach (Schedule::SchedulesFree($act->id()) as $s) {
				$display_schedules .= '
					<tr>
						<td>'. $s->id() .'</td>
						<td>'. $s->description() .'</td>
						<td style="width:45px; text-align:center;">
							<a href="./?page=edit-schedule&amp;id='. $s->id() .'" class="normal"><i class="icon-pencil"></i></a>&nbsp;&nbsp;
							<a href="#confirmBoxS'. $s->id() .'" role="button" data-toggle="modal" class="normal"><i class="icon-trash"></i></a>
						</td>
					</tr>
				';
			}
			$display_schedules .= '</tbody></table>';
		}
		foreach (Schedule::Schedules($act->id()) as $s) {
			$display_schedules .= '
				<div id="confirmBoxS'. $s->id() .'" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="ConfirmDelSchedule'. $s->id() .'" aria-hidden="true">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3 id="ConfirmDelSchedule'. $s->id() .'">'. $act->name() .'</h3>
					</div>
					<div class="modal-body">
						<p class="text-error">Êtes-vous sûr de vouloir supprimer cet horaire ?</p>
					</div>
					<div class="modal-footer">
						<a class="btn" data-dismiss="modal" aria-hidden="true">Annuler</a>
						<a href="./?page=edit-schedule&amp;id='. $s->id() .'&amp;action=delete" class="btn btn-danger">Confirmer</a>
					</div>
				</div>
			';
		}
	}
	else
		$display_schedules = '<div class="alert">Aucun horaire pour l’instant !</div>';
	
	
	// preparation affichage referents
	$count_referents = Referent::countReferents($act->id());
	$plural_count_referents = $count_referents > 1 ? 's' : '';
	
	$display_referents = '';
	if ($count_referents >= 1) {
		$display_referents = '<table class="table table-striped"><thead>
			<tr>
				<th>#</th>
				<th>Membre</th>
				<th>Référent</th>
				<th style="text-align:center;">Téléphone</th>
				<th></th>
			</tr>
			</thead><tbody>
		';
		foreach (Referent::Referents($act->id()) as $r) {
			$m = new Member($r->member());
			$display_referents .= '
				<tr>
					<td>'. $r->id() .'</td>
					<td><a href="./?page=member&amp;id='. $m->id() .'">'. $m->name() .'</a></td>
					<td>'. ucfirst(Display::Referent($r->type(), $m->gender())) .'</td>
					<td style="text-align:center;">'. ($r->display_phone() ? $m->phone() : '<small>non affiché</small>') .'</td>
					<td><a href="#confirmBoxR'. $r->id() .'"  role="button" data-toggle="modal" class="normal"><i class="icon-trash"></i></a></td>
				</tr>
			';
		}
		$display_referents .= '</tbody></table>';
		foreach (Referent::Referents($act->id()) as $p)
			$display_referents .= '
			<div id="confirmBoxR'. $p->id() .'" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="ConfirmDelReferent'. $p->id() .'" aria-hidden="true">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 id="ConfirmDelReferent'. $p->id() .'">'. $act->name() .'</h3>
				</div>
				<div class="modal-body">
					<p class="text-error">Êtes-vous sûr de vouloir supprimer ce référent ?</p>
				</div>
				<div class="modal-footer">
					<a class="btn" data-dismiss="modal" aria-hidden="true">Annuler</a>
					<a href="./?page=edit-referent&amp;id='. $p->id() .'&amp;action=delete" class="btn btn-danger">Confirmer</a>
				</div>
			</div>
			';
	}
	
	// preparation affichage participants
	$count_participants = Participant::countAdherents($act->id());
	$plural_count_participants = $count_participants > 1 ? 's' : '';
	$display_participants = '';
	if ($act->aggregate()) {
		if ($count_participants == 0) {
			$display_participants = '<div class="alert">Aucun participant pour l’instant !</div>';
		} else {
			$display_participants = '<table class="table table-striped"><thead>
			<tr>
				<th>#</th>
				<th>Membre</th>
				<th class="center">Bezannais</th>
				<th class="center">Catégorie</th>
			</tr>
			</thead><tbody>';
			foreach(Participant::Adherents($act->id()) as $p) {
				$m = new Member($p->adherent());
				$display_participants .= '
					<tr>
						<td>'. $p->id() .'</td>
						<td><a href="./?page=member&amp;id='. $m->id() .'">'. $m->name() .'</a></td>
						<td class="center">'. ($m->bezannais() ? '<i class="icon-ok"></i>' : '&ndash;') .'</td>
						<td class="center">'. ($m->minor() ? 'e' : 'A') .'</td>
					</tr>
				';
			}
			$display_participants .= '</tbody></table>';
		}
	}
	else {
		foreach (Schedule::Schedules($act->id()) as $s) {
			$display_participants .= '<h5><i class="icon-time"></i> '. (!$s->type() ? (ucfirst(Display::Day($s->day())) .' &rsaquo; '. $s->time_begin() .' à '. $s->time_end() .' <span style="font-weight:normal;margin-left:10px;font-size:12px;">'.$s->more().'</span>') : $s->description()) .'</h5>';
			if (Participant::countAdherents($act->id(), $s->id()) > 0) {
				$display_participants .= '<table class="table table-striped"><thead>
				<tr>
					<th>#</th>
					<th>Membre</th>
					<th style="text-align:center;">Bezannais</th>
					<th style="text-align:center;">Catégorie</th>
				</tr>
				</thead><tbody>';
				foreach(Participant::Adherents($act->id(), $s->id()) as $p) {
					$m = new Member($p->adherent());
					$display_participants .= '
						<tr>
							<td>'. $p->id() .'</td>
							<td><a href="./?page=member&amp;id='. $m->id() .'">'. $m->name() .'</a></td>
							<td style="text-align:center;">'. ($m->bezannais() ? '<i class="icon-ok"></i>' : '&ndash;') .'</td>
							<td style="text-align:center;">'. ($m->minor() ? 'e' : 'A') .'</td>
						</tr>
					';
				}
				$display_participants .= '</tbody></table>';
			}
			else
				$display_participants .= '<div class="alert">Aucun participant pour cet horaire !</div>';
		}
	}
		
	// preparation affichage préinscriptions
	$query = SQL::sql()->prepare('SELECT COUNT(id) AS total FROM fsc_participants_inscription WHERE activity = ?');
	$query->execute(array($act->id()));
	$data = $query->fetch();
	$query->closeCursor();
	$count_preinscriptions = $data['total'];
	$plural_count_preinscriptions = $count_preinscriptions > 1 ? 's' : '';
	$display_preinscriptions = '';
	if ($act->aggregate()) {
		if ($count_preinscriptions == 0) {
			$display_preinscriptions = '<div class="alert">Aucune préinscription pour l’instant !</div>';
		} else {
			$display_preinscriptions = '<table class="table table-striped"><thead>
			<tr>
				<th>#</th>
				<th>Préinscription</th>
				<th class="center">Bezannais</th>
				<th class="center">Catégorie</th>
				<th class="center">Statut</th>
			</tr>
			</thead><tbody>';

			$query = SQL::sql()->prepare('SELECT adherent, status FROM fsc_participants_inscription WHERE activity = ?');
			$query->execute(array($act->id()));			
			while($data = $query->fetch()) {
				$m = new Preinscription($data['adherent']);
				$display_preinscriptions .= '
					<tr>
						<td>'. $m->id() .'</td>
						<td><a href="./?page=preinscription&amp;id='. $m->id() .'">'. $m->name() .'</a></td>
						<td class="center">'. ($m->bezannais() ? '<i class="icon-ok"></i>' : '&ndash;') .'</td>
						<td class="center">'. ($m->minor() ? 'e' : 'A') .'</td>
						<td class="center status">'.Preinscription::StatusTooltip($data['status']).'</td>
					</tr>
				';
			}
			$query->closeCursor();
			$display_preinscriptions .= '</tbody></table>';
		}
	}
	else {
		foreach (Schedule::Schedules($act->id()) as $s) {
			$display_preinscriptions .= '<h5><i class="icon-time"></i> '. (!$s->type() ? (ucfirst(Display::Day($s->day())) .' &rsaquo; '. $s->time_begin() .' à '. $s->time_end() .' <span style="font-weight:normal;margin-left:10px;font-size:12px;">'.$s->more().'</span>') : $s->description()) .'</h5>';
			$query = SQL::sql()->prepare('SELECT COUNT(id) AS total FROM fsc_participants_inscription WHERE activity = ? AND schedule = ?');
			$query->execute(array($act->id(), $s->id()));
			$data = $query->fetch();
			$query->closeCursor();
			if ($data['total'] > 0) {
				$display_preinscriptions .= '<table class="table table-striped"><thead>
				<tr>
					<th>#</th>
					<th>Préinscription</th>
					<th class="center">Bezannais</th>
					<th class="center">Catégorie</th>
					<th class="center">Statut</th>
				</tr>
				</thead><tbody>';
				$query = SQL::sql()->prepare('SELECT adherent, status FROM fsc_participants_inscription WHERE activity = ? AND schedule = ?');
			$query->execute(array($act->id(), $s->id()));
			while($data = $query->fetch()) {
				$m = new Preinscription($data['adherent']);
					$display_preinscriptions .= '
						<tr>
							<td>'. $m->id() .'</td>
							<td><a href="./?page=preinscription&amp;id='. $m->id() .'">'. $m->name() .'</a></td>
							<td class="center">'. ($m->bezannais() ? '<i class="icon-ok"></i>' : '&ndash;') .'</td>
							<td class="center">'. ($m->minor() ? 'e' : 'A') .'</td>
							<td class="center status">'.Preinscription::StatusTooltip($data['status']).'</td>
						</tr>
					';
				}
				$display_preinscriptions .= '</tbody></table>';
			}
			else
				$display_preinscriptions .= '<div class="alert">Aucune préinscription pour cet horaire !</div>';
		}
	}
	
}
else {
	quit();
}

?>