<?

namespace Lean;

class Validator {

  private $errorMessage = '';

  private $regexps = array(
    'email' => '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/i',
    'phone' => '/^\d{11,14}$/',
    'password' => '/^\S{8,}$/'
  );

  public static function instance(){
    return new static;
  }

  protected function __construct(){

  }

  private function validate($type,$data){
    return preg_match($this->regexps[$type], $data);
  }

  private function find($needles, array $haystack){
    $_needles = explode(' ', $needles);
    foreach($_needles as $needle)
      if (empty($haystack[$needle]))
        return $needle;
    return true;
  }

  public function validatePresence($needles, array $haystack){
    $missing = $this->find($needles, $haystack);
    if ($missing === true) return true;
    $this->errorMessage = "{$missing} is required";
  }

  public function validateEmail($str){
    if ($this->validate('email', $str)) return true;
    $this->errorMessage = 'Email is invalid';
  }

  public function validateEmpty($str, $field){
    if ($str == '' || $str == null) return false;
    $this->errorMessage = "$field cannot be empty";
  }

  public function validatePassword($str){
    if ($this->validate('password', $str)) return true;
    $this->errorMessage = 'Password must be at least 8 characters, and cannot contain spaces';
  }

  public function validatePasswords(array $attrs){
    if ($attrs['password'] === $attrs['password_confirmation']) return true;
    $this->errorMessage = 'Passwords do not match';
  }

  public function validateLength($str, $len){
    //return $this->validate('password', $str);
  }

  public function validatePhone($str){
    if ($this->validate('phone', $str)) return true;
    $this->errorMessage = 'Phone number is invalid';
  }

  public function validateFilePresence(array $file){
    if (isset($file) && !empty($file['name'])) return true;
    $this->errorMessage = 'File is not selected for upload';
  }

  public function validateFileSize(array $file, $maxSize = 2621440) {
    if ($file['size'] < $maxSize) return true;
    $this->errorMessage = 'File is too large';
  }

  public function validateFileType(array $file, $allowedTypes = '') {
    if (in_array(strtolower ( strrchr ( $file['name'], '.' ) ), explode(' ', $allowedTypes)))
      return true;
    $this->errorMessage = 'File format is not acceptable';
  }

  public function getError(){
    return $this->errorMessage;
  }

  public function setError($message){
    $this->errorMessage = $message;
  }

}