<?php

use lib\users\UserInscription;
use lib\preinscriptions\Preinscription;
use lib\content\Page;
use lib\content\Pagination;

$pageInfos = array(
  'name' => 'Préinscriptions',
  'url' => _GESTION_.'/?page=preinscriptions'
);
$page = new Page($pageInfos['name'], $pageInfos['url'], array($pageInfos));

// set actual page
/*
$pages = ceil(Preinscription::countAccounts() / Pagination::step());
$browse = 1;
if (isset($_GET['browse']) && $_GET['browse'] != null)
  $browse = min($pages, max(1, $_GET['browse']+0));

$type = null;
$sens = true;
$url = null;
if (isset($_GET['sort'])) {
  $data = explode('-', htmlspecialchars($_GET['sort']));
  $type = $data[0];
  $sens = isset($data[1]) && $data[1] == 'desc' ? false : true;
}

switch($type) {
  case 'id':
    $preinscriptions = Preinscription::PreinscriptionsById(($browse-1) * Pagination::step(), $sens);
    $url = '&amp;sort=id-' . ($sens ? 'asc' : 'desc');
    break;
  case 'login':
    $preinscriptions = Preinscription::PreinscriptionsByLogin(($browse-1) * Pagination::step(), $sens);
    $url = '&amp;sort=name-' . ($sens ? 'asc' : 'desc');
    break;
  default:
    $preinscriptions = Preinscription::Preinscriptions(($browse-1) * Pagination::step());
}
*/

$preinscriptions = Preinscription::Preinscriptions();

/*
// pagination
$display_pagination = '<li '. ($browse == 1 ? ' class="disabled"><span>' : '><a href="./?page=preinscriptions'. $url .'">') .'<i class="icon-double-angle-left"></i>'. ($browse == 1 ? '</span>' : '</a>') .'</li>' ;
for ($i = 1; $i <= $pages; $i++) {
  $display_pagination .= '
  <li '. ($i != $browse ?: ' class="active"') .'><a href="./?page=preinscriptions'. $url . ($i != 1 ? '&amp;browse='. $i : '') .'">'. $i .'</a></li>
  ';
}
$display_pagination .= '<li '. ($browse == $pages ? ' class="disabled"><span>' : '><a href="./?page=preinscriptions'. $url .'&browse='. $pages .'">') .'<i class="icon-double-angle-right"></i>'. ($browse == $pages ? '</span>' : '</a>') .'</li>' ;
*/

$_SCRIPT[] = '<script src="'. _FSC_ .'/js/hogan.js"></script>' . "\n";
$_SCRIPT[] = "\t" . '<script src="'. _FSC_ .'/js/typeahead.min.js"></script>';
$_SCRIPT[] = "
    <script>
      $(document).ready(function() {
        //$('input.search-preinscriptions').typeahead('destroy');
        $('input.search-preinscriptions').typeahead({
          name: 'preinscriptions',
          valueKey: 'login',
          prefetch: {
            'url': 'http:". _PRIVATE_API_ ."/preinscriptions.php',
            'ttl': 5000
            },
          template: '<a href=\"{{url}}\">{{login}} <i class=\"icon-share-alt\" style=\"font-size:14px; margin-left:5px;\"></i></a>',
          engine: Hogan
        });

        $('input.search-preinscriptions').on(['typeahead:autocompleted', 'typeahead:selected'].join(' '), function (e) {
          var v = [].slice.call(arguments, 1);
          document.location.href = v[0].url;
        });
      });
    </script>
";

?>