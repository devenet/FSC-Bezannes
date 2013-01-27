<?php

namespace lib\members;
use lib\db\SQL;
use lib\laravel\Str;

class Member {
  protected $id;
  protected $gender; // 0: M.; 1: Mme; 2: Mlle
  protected $last_name;
  protected $first_name;
  protected $date_birthday;
  protected $address_number;
  protected $address_street;
  protected $address_further;
  protected $address_zip_code;
  protected $address_town;
  protected $email;
  protected $phone;
  protected $mobile;
  protected $bezannais;
  protected $minor;
  protected $responsible;
  protected $address_different;
  protected $adherent;
  protected $date_registration;
  protected $date_creation;
  private $created;
  
  public function __construct($id = null) {
    if (is_int($id+0) && $this->isMember($id+0)) {
      $query = SQL::sql()->query('SELECT gender, last_name, first_name, date_birthday, address_number, address_street, address_further, address_zip_code, address_town, phone, email, mobile, bezannais, minor, responsible, address_different, adherent, date_registration, date_creation FROM fsc_members WHERE id = '. $id);
      $member = $query->fetch();
      $this->id = $id+0;
      $this->gender = $member['gender'];
      $this->last_name = stripslashes($member['last_name']);
      $this->first_name = stripslashes($member['first_name']);
      $this->date_birthday = $member['date_birthday'];
      $this->address_number = $member['address_number'];
      $this->address_street = stripslashes($member['address_street']);
      $this->address_further = stripslashes($member['address_further']);
      $this->address_zip_code = $member['address_zip_code'];
      $this->address_town = stripslashes($member['address_town']);
      $this->phone = $member['phone'];
      $this->email = $member['email'];
      $this->mobile = $member['mobile'];
      $this->bezannais = $member['bezannais'];
      $this->minor = $member['minor'];
      $this->responsible = $member['responsible'];
      $this->address_different = $member['address_different'];
      $this->adherent = $member['adherent'];
      $this->date_registration = $member['date_registration'];
      $this->date_creation = $member['date_creation'];
      $this->created = true;
      $query->closeCursor();
    }
    else
      $created = false;
  }
  
  public function id() {
    return $this->id;
  }
  
  public function gender() {
    return $this->gender;
  }
  public function setGender($gender) {
    if ($gender != null && $gender >= 0 && $gender <= 2) {
      $this->gender = $gender+0;
      return true;
    }
    return false;
  }
  
  public function last_name() {
    return $this->last_name;
  }
  public function setLastName($name) {
    if ($name != null) {
      //$this->last_name = ucwords(mb_strtolower(htmlspecialchars($name), 'UTF-8'));
      $this->last_name = Str::title(htmlspecialchars($name));
      return true;
    }
    return false;
  }
  
  public function first_name() {
    return $this->first_name;
  }
  public function setFirstName($name) {
    if ($name != null) {
      //$this->first_name = ucwords(mb_strtolower(htmlspecialchars($name), 'UTF-8'));
      $this->first_name = Str::title(htmlspecialchars($name));
      return true;
    }
    return false;
  }
  
  public function name() {
    return $this->last_name .' '. $this->first_name;
  }
  
  public function date_birthday() {
    return $this->date_birthday;
  }
  public function setDateBirthday($year, $month, $day) {
    if ($year != null && $month != null && $day != null && $year >= 1920 && $year <= date('Y')-1 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
      if ($month == 2) {
        // bissextile (et 29 days max) ou 28 days max
        if ((date("L", mktime(0, 0, 0, 1, 1, $year)) == 1 && $day <= 29) || $day <= 28) {
          $this->date_birthday = $year.'-'.$month.'-'.$day;
          return true;
        }
        else
          return false;
      }
      else {
        $this->date_birthday = $year.'-'.$month.'-'.$day;
        return true;
      }
    }
    return false;
  }
  public function age() {
    $date = explode('-', $this->date_birthday);
    return (date('m') >= $date[1] ? date('Y') - $date[0] : date('Y') - $date[0] - 1);
  }
  public function date_birthday_year() {
    $date = explode('-', $this->date_birthday);
    return $date[0];
  }
  public function date_birthday_month() {
    $date = explode('-', $this->date_birthday);
    return $date[1];
  }
  public function date_birthday_day() {
    $date = explode('-', $this->date_birthday);
    return $date[2];
  }
  
  public function minor() {
    return $this->minor;
  }
  public function setMinor() {
    // Majeur avant le 1er septembre exclu
    $date = explode('-', $this->date_birthday);
    if (_YEAR_ - $date[0] > 18) {
      $this->minor = 0;
      $this->address_different = 0;
    }
    elseif (_YEAR_ - $date[0] == 18) {
      if ($date[1] < 9) {
        $this->minor = 0;
        $this->address_different = 0;
      }
      else
        $this->minor = 1;
    }
    else
      $this->minor = 1;
  }
  
  public function address_different() {
    return $this->address_different;
  }
  public function setAddressDifferent($bool = null) {
    if (!$this->minor()) {
      $this->address_different = 0;
      return true;
    }
    else {
      $this->address_different = ($bool == 1 ? 1 : 0);
      return true;
    }
    return false;
  }
  
  public function address_number() {
    return $this->address_number;
  }
  public function setAddressNumber($number) {
    // peut etre 1bis
    if ($number != null) {
      $this->address_number = htmlspecialchars($number);
      return true;
    }
    return false;
  }
  
  public function address_street() {
    return $this->address_street;
  }
  public function setAddressStreet($street) {
    if ($street != null) {
      $this->address_street = ucwords(mb_strtolower(htmlspecialchars($street), 'UTF-8'));
      return true;
    }
    return false;
  }
  
  public function address_further() {
    return $this->address_further;
  }
  public function setAddressFurther($further = null) {
    $this->address_further = htmlspecialchars($further);
    return true;
  }
  
  public function address_zip_code() {
    return $this->address_zip_code;
  }
  public function setAddressZipCode($zip) {
    if ($zip != null && $zip > 0 && $zip <= 99000) {
      $this->address_zip_code = $zip;
      return true;
    }
    return false;
  }
  
  public function address_town() {
    return $this->address_town;
  }
  public function setAddressTown($town) {
    if ($town != null) {
      $this->address_town = preg_replace('#((\s)+$)|(^(\s)+)#', '', ucwords(mb_strtolower(htmlspecialchars($town), 'UTF-8')));
      return true;
    }
    return false;
  }
  
  public function address() {
    return $this->address_number .' '. $this->address_street .'<br />'. ($this->address_further != null ? $this->address_further.'<br />' : null) . $this->address_zip_code .' '. $this->address_town;
  }
  
  public function bezannais() {
    return $this->bezannais;
  }
  public function setBezannais() {
    // mineur
    if ($this->minor) {
      $respo = new Member($this->responsible);
      if ($respo->bezannais())
        $this->bezannais = 1;
      elseif ($this->address_different && $this->address_zip_code == 51430 && $this->address_town == 'Bezannes')
        $this->bezannais = 1;
      else
        $this->bezannais = 0;
    }
    // majeur
    else {
      if ($this->address_zip_code == 51430 && $this->address_town == 'Bezannes')
        $this->bezannais = 1;
      else
        $this->bezannais = 0;
    }
  }
  public function setForceBezannais($bool) {
    $this->bezannais = ($bool == 1 ? 1 : 0);
  }
  
  public function email() {
    return $this->email;
  }
  public function setEmail($email) {
    if ($email != null && preg_match('#^[a-z0-9._\+-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#', strtolower(htmlspecialchars($email)))) {
      $this->email = strtolower(htmlspecialchars($email));
      return true;
    }
    else
      return false;
  }
  
  public function phone() {
    return $this->phone;
  }
  public function setPhone($phone) {
    if ($phone != null && preg_match('#^0[1-9]([0-9]{2}){4}$#', $phone)) {
      $this->phone = htmlspecialchars($phone);
      return true;
    }
    return false;
  }
  
  public function mobile() {
    return $this->mobile;
  }
  public function setMobile($phone = null) {
    if ($phone != null) {
      if (preg_match('#^0[1-9]([0-9]{2}){4}$#', $phone)) {
        $this->mobile = htmlspecialchars($phone);
        return true;
      }
      else
        return false;
    }
    else {
      $this->mobile = '';
      return true;
    }
  }
  
  public function responsible() {
    return $this->responsible;
  }
  public function setResponsible($id) {
    if ($id != null && $this->isAdult($id+0)) {
      $this->responsible = $id+0;
      return true;
    }
    return false;
  }
  
  public function adherent() {
    return $this->adherent;
  }
  public function setAdherent($bool) {
    $this->adherent = ($bool == 1) ? 1 : 0;
    if (!$this->adherent) {
      $query = SQL::sql()->prepare('DELETE FROM fsc_participants WHERE adherent = :adherent');
      $query->execute(array('adherent' => $this->id));
      $query = SQL::sql()->prepare('DELETE FROM fsc_payments_transactions WHERE adherent = :adherent');
      $query->execute(array('adherent' => $this->id));
      $query->closeCursor();
    }
  }
  
  public function date_registration() {
    return $this->date_registration;
  }
  public function setDateRegistration($year, $month, $day) {
    if ($year != null && $month != null && $day != null && $year >= _YEAR_ && $year <= _YEAR_+1 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
      $this->date_registration = $year.'-'.$month.'-'.$day;
      return true;
    }
    return false;
  }
  public function date_registration_year() {
    $date = explode('-', $this->date_registration);
    return $this->date_registration != null ? $date[0] : null;
  }
    public function date_registration_month() {
    $date = explode('-', $this->date_registration);
    return $this->date_registration != null ? $date[1] : null;
  }
  public function date_registration_day() {
    $date = explode('-', $this->date_registration);
    return $this->date_registration != null ? $date[2] : null;
  }
  
  public function date_creation() {
    return $this->date_creation;
  }

  private function update_sql($field, $data) {
    $demande = 'UPDATE fsc_members SET '. $field .' = \''. $data .'\' WHERE id = '. $this->id .'';
    $query = SQL::sql()->query($demande);
    $query->closeCursor();
  }
  
  public function update() {
    if ($this->created) {
      $this->update_sql('gender', $this->gender);
      $this->update_sql('last_name', addslashes($this->last_name));
      $this->update_sql('first_name', addslashes($this->first_name));
      $this->update_sql('date_birthday', $this->date_birthday);
      $this->update_sql('address_number', addslashes($this->address_number));
      $this->update_sql('address_street', addslashes($this->address_street));
      $this->update_sql('address_further', addslashes($this->address_further));
      $this->update_sql('address_zip_code', $this->address_zip_code);
      $this->update_sql('phone', $this->phone);
      $this->update_sql('email', $this->email);
      $this->update_sql('mobile', $this->mobile);
      $this->update_sql('address_town', addslashes($this->address_town));
      $this->update_sql('bezannais', $this->bezannais);
      $this->update_sql('minor', $this->minor);
      $this->update_sql('responsible', $this->responsible);
      $this->update_sql('address_different', $this->address_different);
      $this->update_sql('adherent', $this->adherent);
      $this->update_sql('date_registration', $this->date_registration);
    }
  }
  
  public function create() {
    if (!$this->created) {
      $fake_id = time()-rand(1000, 100000);      
      $query = SQL::sql()->prepare('INSERT INTO fsc_members(gender, last_name, first_name, date_birthday, address_number, address_street, address_further, address_zip_code, address_town, phone, email, mobile, bezannais, minor, responsible, address_different, adherent, date_registration, date_creation, fake_id) VALUES(:gender, :last_name, :first_name, :date_birthday, :address_number, :address_street, :address_further, :address_zip_code, :address_town, :phone, :email, :mobile, :bezannais, :minor, :responsible, :address_different, :adherent, :date_registration, :date_creation, :fake_id)');
      $prepare = array(
        'gender' => $this->gender,
        'last_name' => addslashes($this->last_name),
        'first_name' => addslashes($this->first_name),
        'date_birthday' => $this->date_birthday,
        'address_number' => addslashes($this->address_number),
        'address_street' => addslashes($this->address_street),
        'address_further' => addslashes($this->address_further),
        'address_zip_code' => $this->address_zip_code == null ? '' : $this->address_zip_code,
        'address_town' => addslashes($this->address_town),
        'phone' => $this->phone,
        'email' => $this->email,
        'mobile' => $this->mobile,
        'bezannais' => $this->bezannais,
        'minor' => $this->minor,
        'responsible' => $this->responsible,
        'address_different' => $this->address_different,
        'adherent' => $this->adherent,
        'date_registration' => $this->date_registration,
        'date_creation' => date('Y').':'.date('m').':'.date('d'),
        'fake_id' => $fake_id
        );
      $query->execute($prepare);
      $query->closeCursor();
      $query = SQL::sql()->prepare('SELECT id FROM fsc_members WHERE fake_id = ?');
      $query->execute(array($fake_id));
      $data = $query->fetch();
      $this->id = $data['id'];
      $query->closeCursor();
      $this->update_sql('fake_id', '');
      $this->created = true;
    }
  }
  
  public function delete($bool = false) {
    if ($bool && $this->created) {
      // suppression membre
      $query = SQL::sql()->prepare('DELETE FROM fsc_members WHERE id = :id');
      $query->execute(array('id' => $this->id));
      // suppression participant
      $query = SQL::sql()->prepare('DELETE FROM fsc_participants WHERE adherent = :id');
      $query->execute(array('id' => $this->id));
      // suppression referent
      $query = SQL::sql()->prepare('DELETE FROM fsc_referents WHERE member = :id');
      $query->execute(array('id' => $this->id));
      // suppression transactions
      $query = SQL::sql()->prepare('DELETE FROM fsc_payments_transactions WHERE adherent = :id');
      $query->execute(array('id' => $this->id));
      // suppression advantages
      $query = SQL::sql()->prepare('DELETE FROM fsc_payments_advantages WHERE adherent = :id');
      $query->execute(array('id' => $this->id));
      $query->closeCursor();
      return true;
    }
    return false;
  }
  
  static public function isMember($id) {
    $query = SQL::sql()->query('SELECT id FROM fsc_members');
    $ids = array();
    while ($data = $query->fetch())
      $ids[] = $data['id'];
    return in_array($id, $ids);
  }
  
  static public function isAdherent($id) {
    $query = SQL::sql()->query('SELECT id FROM fsc_members WHERE adherent = 1');
    $ids = array();
    while ($data = $query->fetch())
      $ids[] = $data['id'];
    return in_array($id, $ids);
  }
  
  static public function isAdult($id) {
    $query = SQL::sql()->query('SELECT id FROM fsc_members WHERE minor = 0');
    $ids = array();
    while ($data = $query->fetch())
      $ids[] = $data['id'];
    return in_array($id, $ids);
  }
  
  static public function Members() {
    $return = array();
    $query = SQL::sql()->query('SELECT id FROM fsc_members');
    while ($data = $query->fetch())
      $return[] = new Member($data['id']); 
    $query->closeCursor();
    return $return;
  }
  
  static public function MembersByName($sens = true) {
    $return = array();
    $query = SQL::sql()->query('SELECT id FROM fsc_members ORDER BY last_name '. ($sens ? '' :  'DESC') .', first_name');
    while ($data = $query->fetch())
      $return[] = new Member($data['id']); 
    $query->closeCursor();
    return $return;
  }
  
  static public function MembersByAdherent($sens = true) {
    $return = array();
    $query = SQL::sql()->query('SELECT id FROM fsc_members ORDER BY adherent '. ($sens ? 'DESC' :  '') .', last_name, first_name');
    while ($data = $query->fetch())
      $return[] = new Member($data['id']); 
    $query->closeCursor();
    return $return;
  }
  
  static public function MembersByBezannais($sens = true) {
    $return = array();
    $query = SQL::sql()->query('SELECT id FROM fsc_members ORDER BY bezannais '. ($sens ? 'DESC' :  '') .', last_name, first_name');
    while ($data = $query->fetch())
      $return[] = new Member($data['id']); 
    $query->closeCursor();
    return $return;
  }
  
  static public function MembersByAdult($sens = true) {
    $return = array();
    $query = SQL::sql()->query('SELECT id FROM fsc_members ORDER BY minor '. ($sens ? '' :  'DESC') .', last_name, first_name');
    while ($data = $query->fetch())
      $return[] = new Member($data['id']); 
    $query->closeCursor();
    return $return;
  }
  
  static public function Adults() {
    $return = array();
    $query = SQL::sql()->query('SELECT id FROM fsc_members WHERE minor = 0 ORDER BY last_name, first_name');
    while ($data = $query->fetch())
      $return[] = new Member($data['id']); 
    $query->closeCursor();
    return $return;
  }
  
  static public function Teenagers() {
    $return = array();
    $query = SQL::sql()->query('SELECT id FROM fsc_members WHERE minor = 1');
    while ($data = $query->fetch())
      $return[] = new Member($data['id']); 
    $query->closeCursor();
    return $return;
  }
  
  static public function Adherents() {
    $return = array();
    $query = SQL::sql()->query('SELECT id FROM fsc_members WHERE adherent = 1 ORDER BY last_name, first_name');
    while ($data = $query->fetch())
      $return[] = new Member($data['id']); 
    $query->closeCursor();
    return $return;
  }
  
  public function Responsabilities() {
    $return = array();
    $query = SQL::sql()->prepare('SELECT id FROM fsc_members WHERE responsible = :id');
    $query->execute(array('id' => $this->id));
    while ($data = $query->fetch())
      $return[] = new Member($data['id']); 
    $query->closeCursor();
    return $return;
  }
  
  static public function countMembers() {
    $query = SQL::sql()->query('SELECT COUNT(id) AS total FROM fsc_members');
    $data = $query->fetch();
    $query->closeCursor();
    return $data['total'];
  }
  
  static public function countAdults() {
    $query = SQL::sql()->query('SELECT COUNT(id) AS total FROM fsc_members WHERE minor = 0');
    $data = $query->fetch();
    $query->closeCursor();
    return $data['total'];
  }
  
  static public function countAdherents() {
    $query = SQL::sql()->query('SELECT COUNT(id) AS total FROM fsc_members WHERE adherent = 1');
    $data = $query->fetch();
    $query->closeCursor();
    return $data['total'];
  }
  
  public function countResponsabilities() {
    $query = SQL::sql()->prepare('SELECT COUNT(id) AS total FROM fsc_members WHERE responsible = :id');
    $query->execute(array('id' => $this->id));
    $data = $query->fetch();
    $query->closeCursor();
    return $data['total'];
  }
}


?>