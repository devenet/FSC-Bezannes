<?php

use lib\users\UserInscription;
use lib\preinscriptions\Preinscription;
use lib\preinscriptions\FutureParticipant;
use lib\content\Page;
use lib\content\Pagination;
use lib\content\Sort;
use lib\content\Display;
use lib\content\Message;


function quit() {
	header('Location: '. _GESTION_ .'/?page=preinscriptions');
	exit();
}

// if status refresh asked
if (isset($_GET['check-status'])) {
	foreach (Preinscription::Accounts('all') as $p)
		$p->checkStatus();
	header('Location: '. substr($_SERVER['REQUEST_URI'], 0, -(1+strlen('check-status'))));
	exit();
}

$required_view = 'pre';
// account page ie: list preinscriptions for an account
if (isset($_GET['account'])) {

	if (UserInscription::isUser(UserInscription::getLogin($_GET['account']+0))) {
		
		$u = new UserInscription($_GET['account']+0);
		$u->checkStatus();

		// suppression d'un account de préinscriptions
		if (isset($_GET['action']) && $_GET['action'] == 'delete') {
			try {
				$email = $u->login();

				if (! $u->delete(true))
					throw new \Exception('Une erreure applicative est survenue...');

				$_SESSION['msg'] = new Message('Le compte '. $email .' ainsi que les préinscriptions associées ont bien été supprimées.', 1, 'Suppression réussie');
				quit();
			}
			catch(\Exception $e) {
				$_SESSION['msg'] = new Message($e->getMesssage(), -1, 'Suppression impossible');
				quit();
			}
		}
			
		$pageInfos = array(
		 'name' => $u->login(),
		 'url' => _GESTION_.'/?page=preinscriptions'
		);
		$page = new Page($pageInfos['name'], $pageInfos['url'], array(array('name' => 'Préinscriptions', 'url' => '?page=preinscriptions'), $pageInfos));

		$required_view = 'account';

		$count_members = Preinscription::countMembers($u->id());
		$count_activities = 99999;
		$count_adherents = 0;
		$preinscriptions = array();
		// affichage des préinscriptions du compte
		if ($count_members == 0) {
			$display_members = '<div class="span8"><div class="alert">Aucune préinscription enregistrée</div></div>';
		}
		else {
			$preinscriptions = Preinscription::Members($u->id());
			$display_members = '<div class="span12">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>#</th>
						<th>Nom</th>
						<th>Prénom</th>
						<th style="width:120px;" class="center">Pré-adhérent</th>
						<th class="center"><i class="icon-globe"></i> Activités</th>
						<th class="center">Cat.</th>
						<th class="center">Statut</th>
						<th></th>
					</tr>
				</thead>
				<tbody>';
			foreach ($preinscriptions as $m) {
				$act = FutureParticipant::countActivities($m->id());
				if ($m->adherent()) {
					$count_adherents++;
					$count_activities = min($act, $count_activities);
				}
				if ($m->minor())
					$resp = new Preinscription($m->responsible());
				$display_members .= '
					<tr>
						<td>'. $m->id() .'</td>
						<td><a href="'. _GESTION_ .'/?page=preinscription&amp;id='. $m->id() .'" >'. $m->last_name() .'</a></td>
						<td><a href="'. _GESTION_ .'/?page=preinscription&amp;id='. $m->id() .'" >'. $m->first_name() .'</a></td>
						<td style="width:120px;" class="center">'. ($m->adherent() ? '<i class="icon-ok" style="color:#444;"></i>' : '') .'</td>
						<td style="text-align:center;">'. ($m->adherent() ? '<span class="label label-'. Preinscription::StatusColor($m->status()) .'">'. $act .'</span>' : '') .'</td>
						<td class="center">'. ($m->minor() ? 'e' : ($m->countResponsabilities() > 0 ? 'A <span style="position:absolute; padding-left:5px; color:#333;">&bull;</span>' : 'A')) .'</td>
						<td class="status center">'. Preinscription::StatusTooltip($m->status()) .'</td>
						<td class="center" style="padding-left:0; padding-right:0;">
							<a'. ($m->status() == Preinscription::AWAITING ? ' href="'. _GESTION_ .'/?page=validate-preinscription&amp;id='. $m->id() .'"' : NULL) .' class="btn btn-small'. ($m->status() == Preinscription::AWAITING ? '' : ' disabled') .'" title="Valider"><i class="icon-plus"></i></a>
							<div class="btn-group">
								<a href="'. _GESTION_ .'/?page=preinscription&amp;id='. $m->id() .'" class="btn btn-small" title="Voir"><i class="icon-eye-open"></i></a>
								<a'. ($m->status() == Preinscription::AWAITING ? ' href="'. _GESTION_ .'/?page=edit-preinscription&amp;id='. $m->id() .'"':'') .' class="btn btn-small'. ($m->status() == Preinscription::AWAITING ? '' : ' disabled') .'" title="Modifier"><i class="icon-pencil"></i></a>
							</div>
							<a href="#confirmBox'. $m->id() .'" data-toggle="modal" role="button" class="btn btn-small" title="Supprimer"><i class="icon-trash"></i></a>
						</td>
					</tr>
				';
			}
			$display_members .= '
				</tbody>
			</table>
			</div>';

			$_SCRIPT[] = '<script>$(function(){ $(\'table td.status span\').tooltip(); });</script>';
		}

		
	}
	else {
		quit();
	}

// default page: list accounts
} else {

	$pageInfos = array(
		'name' => 'Préinscriptions',
		'url' => _GESTION_.'/?page=preinscriptions'
	);
	$page = new Page($pageInfos['name'], $pageInfos['url'], array($pageInfos));

	// set actual page
	$pages = ceil(Preinscription::countAccounts() / Pagination::step());
	$browse = 1;
	if (isset($_GET['browse']) && $_GET['browse'] != NULL)
		$browse = min($pages, max(1, $_GET['browse']+0));

	$type = NULL;
	$sens = true;
	$url = NULL;
	if (isset($_GET['sort'])) {
		$data = explode('-', htmlspecialchars($_GET['sort']));
		$type = $data[0];
		$sens = isset($data[1]) && $data[1] == 'desc' ? false : true;
	}

	$sort = array(
		'login' => new Sort(),
		'status' => new Sort()
	);

	switch($type) {
		case 'login':
			$preinscriptions = Preinscription::AccountsByLogin(($browse-1) * Pagination::step(), $sens);
			$url = '&amp;sort=login-' . ($sens ? 'asc' : 'desc');
			break;
		case 'status':
			$preinscriptions = Preinscription::AccountsByStatus(($browse-1) * Pagination::step(), $sens);
			$url = '&amp;sort=status-' . ($sens ? 'asc' : 'desc');
			break;
		default:
			$preinscriptions = Preinscription::Accounts(($browse-1) * Pagination::step());
	}
	if ($type != NULL) $sort[$type]->sens($sens ? 'asc' : 'desc');

	// pagination
	$display_pagination = '';
	if ($pages > 0) {
		$display_pagination = '<li '. ($browse == 1 ? ' class="disabled"><span>' : '><a href="./?page=preinscriptions'. $url .'">') .'<i class="icon-double-angle-left"></i>'. ($browse == 1 ? '</span>' : '</a>') .'</li>' ;
		for ($i = 1; $i <= $pages; $i++) {
		 $display_pagination .= '
		 <li '. ($i != $browse ?: ' class="active"') .'><a href="./?page=preinscriptions'. $url . ($i != 1 ? '&amp;browse='. $i : '') .'">'. $i .'</a></li>
		 ';
		}
		$display_pagination .= '<li '. ($browse == $pages ? ' class="disabled"><span>' : '><a href="./?page=preinscriptions'. $url .'&browse='. $pages .'">') .'<i class="icon-double-angle-right"></i>'. ($browse == $pages ? '</span>' : '</a>') .'</li>' ;
	}

	$_SCRIPT[] = '<script src="'. _STATIC_ .'/js/hogan-'. _VERSION_JS_ .'.min.js"></script>';
	$_SCRIPT[] = '<script src="'. _STATIC_ .'/js/typeahead-'. _VERSION_JS_ .'.min.js"></script>';
	$_SCRIPT[] = "
<script>
$(document).ready(function() {

$('input.search-preinscriptions').typeahead([
	{
	name: 'account',
	valueKey: 'login',
	prefetch: {
		'url': 'http:". _PRIVATE_API_ ."/accounts.php',
		'ttl': 5000
		},
	template: '<a href=\"{{url}}\">{{login}} <i class=\"icon-share-alt\" style=\"font-size:14px; margin-left:5px;\"></i></a>',
	engine: Hogan,
	header: '<h5>Comptes</h5>'
	},
	{
	name: 'preinscription',
	valueKey: 'name',
	prefetch: {
		'url': 'http:". _PRIVATE_API_ ."/preinscriptions.php',
		'ttl': 5000
		},
	template: '<a href=\"{{url}}\">{{name}} <i class=\"icon-share-alt\" style=\"font-size:14px; margin-left:5px;\"></i></a>',
	engine: Hogan,
	header: '<h5>Préinscriptions membre</h5>'
	}
]);

$('input.search-preinscriptions').on(['typeahead:autocompleted', 'typeahead:selected'].join(' '), function (e) {
	var v = [].slice.call(arguments, 1);
	document.location.href = v[0].url;
});
});
</script>
";

}

?>