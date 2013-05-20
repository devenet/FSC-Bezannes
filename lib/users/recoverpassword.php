<?php

namespace lib\users;
use lib\db\SQL;
use lib\mail\Mail;

abstract class RecoverPassword {
  
  static protected $delay = 43200;
  static protected $messages = array('de préinscription', 'de gestion', 'd’administration');
  static protected $links = array(_INSCRIPTION_, _GESTION_, '404');
  
  static public function insert($user, $email, $type = 0) {
    self::clear();
    /*
    * type
    * 0: inscription, 1: gestion, 2: admin
    */
    $data = array(
      'id_user' => $user,
      'type' => $type,
      'token' => sha1(uniqid(mt_rand(), true)),
      'date' => (time() + self::$delay)
    );

    if (! self::asked($data['id_user'])) {
      // insertion into the DB
      $query = SQL::sql()->prepare('INSERT INTO fsc_users_recover_passwords(type, id_user, token, date) VALUES(:type, :id_user, :token, :date)');
      $query->execute($data);
      $query->closeCursor();
      // send email
      $body = 'Bonjour,

Vous avez demandé la réinitialisation de votre mot de passe pour votre compte '. self::$messages[$type] .' sur le site du FSC Bezannes.

Si cette demande vient bien de vous, merci de vous rendre à l’adresse suivante pour réinitialiser votre mot de passe :
http:'. self::$links[$type] .'/recovery.php?token='. $data['token'] .'&user='. $email .'
Attention, le lien est valide pendant '. (self::$delay / 3600) .' heures.

Si vous n’avez pas effectué cette demande, vous pouvez ignorer cet email.
Pensez cependant à changer régulièrement votre mot de passe, en y insérant des caractères spéciaux.';

      Mail::text($email, 'Réinitialisation de votre mot de passe', $body);
    }
    else {
      throw new \Exception('Une demande de réinitialisation de mot de passe pour ce compte a déjà été envoyée.
        <br /><br />Merci de vérifier vos emails (peut-être même votre dossier “indésirables”...).
        <br />Vous pourrez faire une nouvelle demande d’ici '. (self::$delay / 3600) .' heures.');
    }
  }


  static protected function asked($user) {
    $users = array();
    $query = SQL::sql()->query('SELECT id_user FROM fsc_users_recover_passwords');
    while ($data = $query->fetch())
      $users[] = $data['id_user'];
    $query->closeCursor();

    return in_array($user, $users); 
  }

  static protected function clear() {
    $query = SQL::sql()->prepare('DELETE FROM fsc_users_recover_passwords WHERE date < :date');
    $query->execute(array(
      'date' => time()
      ));
    $query->closeCursor();
  }

  static public function accept($token, $user) {
    self::clear();
    $auth = array();
    $query = SQL::sql()->query('SELECT token, id_user FROM fsc_users_recover_passwords');
    while ($data = $query->fetch())
      $auth[] = array(
          'token' => $data['token'],
          'user' => $data['id_user']
        );
    $query->closeCursor();

    return in_array(array('token' => $token, 'user' => $user), $auth);
  }

  static public function remove($user) {
    $query = SQL::sql()->prepare('DELETE FROM fsc_users_recover_passwords WHERE id_user = :id');
    $query->execute(array('id' => $user));
    $query->closeCursor();
    self::clear();
  }

}

?>