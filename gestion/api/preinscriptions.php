<?php

namespace lib;
use lib\preinscriptions\Preinscription;

set_include_path('../../');
spl_autoload_extensions('.php');
spl_autoload_register();
error_reporting (0);

session_name('fsc_gestion');
session_start();

require '../../config/config.php';

if (!isset($_SESSION['authentificated']) || !$_SESSION['authentificated']) {
  header('Location: '. _GESTION_ .'/login.php');
  exit();
}
else {

  header('Content-type: application/json');

  $data = array();

  foreach (Preinscription::Members() as $m) {
    $data[] = array (
      "name" => $m->name(),
      "url" => _GESTION_ .'/?page=preinscription&id='. $m->id()
    );
  }

  $data = json_encode($data);
  exit($data);

}

?>