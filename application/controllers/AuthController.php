<?php
/**
 * @author Dani Remeseiro
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * 
 */
class AuthController extends Zend_Controller_Action {

    public function init() {
        $this->lang = $this->view->lang = $this->_helper->checklang->check();
        $this->location = $this->_helper->checklocation->check();

        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->mensajes = $this->_flashMessenger->getMessages();
    }

    
    public function indexAction() {
        $this->_redirect('/');
    }

    
    public function loginAction() {

        $this->view->page_title .= $this->view->translate ( 'Login' );

        //if the user is logged already redir to home
        $auth = Zend_Auth::getInstance ();
        if ($auth->hasIdentity()) {
            $this->_redirect('/' . $this->lang . '/woeid/' . $this->location . '/give');
        }

        $request = $this->getRequest();
        $form = $this->_getUserLoginForm();

        
        if ($this->getRequest()->isPost()) {

            if ($form->isValid($request->getPost())) {

                $f = new Zend_Filter_StripTags ( );
                $email = $f->filter($this->_request->getPost('email'));
                $password = $f->filter($this->_request->getPost('password'));

                //DDBB validation
                // setup Zend_Auth adapter for a database table
                $readConf = new Zend_Config_Ini(APPLICATION_PATH . '/config/nolotiro.ini', 'production');
                $dbAdapter = $dbAdapter = Zend_Db::factory($readConf->resources->db);

                $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
                $authAdapter->setTableName('users');
                $authAdapter->setIdentityColumn('email');
                $authAdapter->setCredentialColumn('password');
                // Set the input credential values to authenticate against
                $authAdapter->setIdentity($email);
                $authAdapter->setCredential(md5(trim($password))); //trim whitespaces from copy&pasting the pass from email
                // do the authentication
                $auth = Zend_Auth::getInstance ();

                //check first if the user is activated (by confirmed email)
                $select = $authAdapter->getDbSelect();
                $select->where('active > 0');
                //check if the user is not locked (spammers, bad users, etc)
                $select->where('locked = 0');

                $result = $authAdapter->authenticate();
                if ($result->isValid()) {
                    // success: store database row to auth's storage
                    // system. (Not the password though!)
                    $data = $authAdapter->getResultRowObject(null, 'password');
                    $auth->getStorage()->write($data);

                    $woeid = $this->_helper->CheckWoeidUser->checkUserLogged($auth->getIdentity()->id);

                    $this->_helper->_flashMessenger->addMessage($this->view->translate('Welcome,') . ' ' . $auth->getIdentity()->username);



                    Zend_Session::start();
                    //check if user wants to be remembered by 7 days
                    $seconds = 60 * 60 * 24 * 7;

                    if ($this->_request->getPost('rememberme') == "1") {
                        Zend_Session::RememberMe($seconds);
                    } else {

                        Zend_Session::ForgetMe();
                    }


                    //check the redir value if setted
                    $aNamespace = new Zend_Session_Namespace('Nolotiro');
                    $redir = $aNamespace->redir;

                    if ($redir !== null) {

                        $aNamespace->redir = null; //reset redir value
                        $this->_redirect($redir);
                    } else {

                        //if redir empty goto main home ads and set the welcome logged in message
                        $this->_redirect('/' . $this->lang . '/woeid/' . $woeid . '/give');
                    }
                } else {
                    // failure: wrong username
                    $view = $this->initView();
                    $view->error = $this->view->translate('Wrong email or password, please try again');
                }

                //_redirect('/');
            }
        }
        // assign the form to the view
        $this->view->form = $form;
    }

    /**
     *
     * @return Form_UserLogin
     */
    protected function _getUserLoginForm() {
        require_once APPLICATION_PATH . '/forms/UserLogin.php';
        $form = new Form_UserLogin ( );
        return $form;
    }

    /**
     * Log out - clear the session, then redirect to
     * the log in page.
     */
    public function logoutAction() {
        Zend_Auth::getInstance ()->clearIdentity();
        $this->session->logged_in = false;
        $this->session->username = false;
        $this->_redirect('/');
    }

}
