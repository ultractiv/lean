<?

namespace Lean;

class View {

  public $host = HOST;
  public $user = array(); // collects user info stored in session
  private $admin = false;

  public $success = false;
  public $data = array();
  public $error = false;
  private $title = '';

  public static function instance($title){
    return new static($title);
  }

  private function __construct($title){
    session_start();
    header('cache-control: no-cache');
    $this->title = $title;

    if(isset($_SESSION['success'])) $this->success = $_SESSION['success'];
    if(isset($_SESSION['error']))   $this->error = $_SESSION['error'];
    if(isset($_SESSION['data']))    $this->data = $_SESSION['data'];
    if(isset($_SESSION['user']))    $this->user = $_SESSION['user'];
    if($this->user) $this->admin = $this->user['level'] > 1;

  }

  public function header($view,$hideMetaTags=false){
    print <<< EOD
             <!DOCTYPE html>
				<html language='en'>
				<head>
				<meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <meta name='language' content='en' /> 
				<meta http-equiv='expires' value='Thu, 16 Mar 2000 11:00:00 GMT' />
				<meta http-equiv='pragma' content='no-cache' />
                <link rel='shortcut icon' href='{$this->host}ico/favicon.png'>
				<title>Annals of Tropical Pathology Journal - {$this->title}</title>
EOD;
    if (!$hideMetaTags) print <<< EOD
 
            <meta name='keywords' content='annalsoftropicalpathology.org, Annals of Tropical Pathology, tropical pathology, pathology, pathology journal, nigerian pathologists, nigerian post graduate college of medicine, nmpcn' />
			<meta name='author' content='Yemi Agbetunsin, Ultractiv, UltrActiv Web Services, www.ultractiv.com' />
			<meta name='robots' content='index, follow' />
EOD;

    print <<< EOD
          
          <link href='{$this->host}css/style.css' rel='stylesheet' /></head>
			<body id='$view'>
              <div class='navbar navbar-inverse navbar-fixed-top'>
                <div class='container'>
                  <div class='navbar-header'>
                    <button type='button' class='navbar-toggle' data-toggle='collapse' data-target='.navbar-collapse'>
                      <span class='icon-bar'></span>
                      <span class='icon-bar'></span>
                      <span class='icon-bar'></span>
                    </button>
                    <a class='navbar-brand' href='{$this->host}'>ATP Journal</a>
                  </div>
                  <div class='navbar-collapse collapse'>
                    <ul class='nav navbar-nav'>
                      <li rel='home'><a href='{$this->host}'>Home</a></li>
                      <li rel='about' class='dropdown'>
                        <a href='#' class='dropdown-toggle' data-toggle='dropdown'>About <b class='caret'></b></a>
                        <ul class='dropdown-menu'>
                          <li><a href='{$this->host}about/journal.php'>The Journal</a></li>
                          <li><a href='{$this->host}about/editorial.php'>Editorial Board</a></li>
                          <li><a href='{$this->host}about/management.php'>Management Board</a></li>
                          <li class='divider'></li>
                          <li><a href='#'>Grants, Lectures &amp; Awards</a></li>
                          <li><a href='#'>Journal Subscription</a></li>
                        </ul>
                      </li>
                      <li rel='contact'><a href='{$this->host}contact.php'>Contact</a></li>
                      <li rel='papers' class='dropdown'>
                        <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Papers <b class='caret'></b></a>
                        <ul class='dropdown-menu'>
                        
EOD;

    if ($this->user) print <<< EOD
                    
                          <li><a href='{$this->host}papers/contribute.php'>Submit Paper</a></li>
                          <li class='divider'></li>
                          
EOD;
    print <<< EOD
                          
                          <li class='dropdown-header'>For Contributors</li>
                          <li><a href='{$this->host}papers/guidelines.php'>Submission Guidelines</a></li>
                          <li><a href='{$this->host}papers/faqs.php'>F.A.Qs</a></li>
                          <li class='divider'></li>
                          <li class='dropdown-header'>For Editors</li>
                          <li><a href='{$this->host}papers/editors.php'>Editorial Assessment</a></li>
                        </ul>
                      </li>
                    </ul>
                    
EOD;
    if (!$this->user) print <<< EOD
 
                    <form name='create-session' class='navbar-form navbar-right'>
                      <div class='form-group'>
                        <input name='email' required type='text' placeholder='Email address' class='form-control'>
                      </div>
                      <div class='form-group'>
                        <input name='password' required type='password' placeholder='Password' class='form-control'>
                      </div>
                      <button class='btn btn-success'>Sign in</button>
                    </form>
                    
EOD;
    else {

      print <<< EOD
                    
                    <ul class='nav navbar-nav navbar-right'>
                      <li rel='account' class='dropdown'>
                        <a href='#' class='dropdown-toggle' data-toggle='dropdown'>{$this->user['name']} <b class='caret'></b></a>
                        <ul class='dropdown-menu'>
                          <li><a href='{$this->host}papers/contribute.php'>Submit Paper</a></li>
                          <li><a href='{$this->host}papers/collections.php'><span class='badge pull-right'>{$this->user['papers']}</span> My Papers</a></li>
                          <li><a href='{$this->host}account.php'>Update Account</a></li>
                        </ul>
                      </li>
EOD;

      if ($this->admin) {
        $admin = $this->host . 'admin';
        print "<li rel='admin' class='dropdown'>
                        <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Admin. <b class='caret'></b></a>
                        <ul class='dropdown-menu'>
                          <li><a href='{$admin}'>Dashboard</a></li>
                          <li><a href='{$admin}/assessments.php'>Assessments</a></li>
                          <li><a href='{$admin}/assignments.php'>Assignments</a></li>
                          <li><a href='{$admin}/contacts.php'>Contacts</a></li> 
                          <li><a href='{$admin}/papers.php'>Papers</a></li>
                          <li><a href='{$admin}/users.php'>Users</a></li>
                        </ul></li>" ;
      }

      print "<li><button class='btn btn-danger btn-sm' id='signout'>Sign Out</button></li></ul>";

    }

    print"</div><!--/.navbar-collapse -->
                    </div>
                  </div>";
  }

  public function body($htmlText){
    print <<< EOD
          
          <div class='container'>
              <div class='row'>{$htmlText}</div>
              <hr>
              <footer>
                <p>
                 <nav class='pull-right'>
                   <a href='{$this->host}terms.php'>Terms</a>
                   <a href='{$this->host}privacy.php'>Privacy</a>
                 </nav>
                &copy; Annals of Tropical Pathology Journal, 2013                  
                </p>                
              </footer>
            </div> <!-- /container -->
            <script type='text/javascript' src='{$this->host}js/html5shiv.js'></script>
            <script type='text/javascript' src='{$this->host}js/underscore-min.js'></script>
            <script type='text/javascript' src='{$this->host}js/jquery-1.10.1.min.js'></script>
            <script type='text/javascript' src='{$this->host}js/raphael-min.js'></script>            
            <script type='text/javascript' src='{$this->host}js/bootstrap.min.js'></script>
            <script type='text/javascript' src='{$this->host}js/holder.js'></script>
            <script type='text/javascript' src='{$this->host}js/typeahead.js'></script>
            <script type='text/javascript' src='{$this->host}js/jquery.autosize.min.js'></script>
            <script type='text/javascript' src='{$this->host}js/jquery.inputlimiter.1.3.1.min.js'></script>
            <script type='text/javascript' src='{$this->host}js/morris.min.js'></script>
            <script type='text/javascript'> window.host = '{$this->host}';</script>
            <script type='text/javascript' src='{$this->host}js/script.js'></script>
          </body></html>

EOD;
    $this->clearOnExit();

  }

  private function redirect(){
    header("location: {$this->host}");
    exit();
  }

  public function redirector($condition, $location){
    if ($condition == true) {
      if ($location) header("location: $location") && exit();
      else $this->redirect();
    }
  }

  public function redirectIfNoSession(){
    if (!$this->user)
      return $this->redirect();
  }

  public function redirectIfSession(){
    if ($this->user)
      return $this->redirect();
  }

  public function redirectIfNotAdmin(){
    if (!$this->admin)
      return $this->redirect();
  }

  private function clearOnExit(){
    if ($this->data)    unset($_SESSION['data']);
    if ($this->success) unset($_SESSION['success']);
    if ($this->error)   unset($_SESSION['error']);
  }

}