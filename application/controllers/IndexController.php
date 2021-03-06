<?php

class IndexController extends Zend_Controller_Action {

    public function init() {

        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $this->view->user = Zend_Auth::getInstance ()->getIdentity();
        $this->lang = $this->view->lang = $this->_helper->checklang->check();
        $this->location = $this->_helper->checklocation->check();

        $this->view->checkMessages = $this->_helper->checkMessages->check();

        if ($this->view->checkMessages > 0) {
            $this->_helper->_flashMessenger->addMessage($this->view->translate('You have') . ' ' .
                    '<b><a href="/' . $this->view->lang . '/message/received">' . $this->view->translate('new messages') . ' (' . $this->view->checkMessages . ')</a></b>');
        }
    }

    public function indexAction() {

       $this->_helper->layout()->setLayout('home');
       $this->view->suggestIP = $this->_helper->getLocationGeoIP->suggest();
       $this->view->page_title .= $this->view->translate('no lo tiro, te lo regalo (sin condiciones)');

        //check if user is locked
        $locked = $this->_helper->checkLockedUser->check();
        if ($locked == 1) {
            $this->_redirect('/' . $this->view->lang . '/auth/logout');
        }

        //if user is logged the redir to proper location, if not stand on not logged home view (index)
       $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {
            $this->_redirect('/' . $this->view->lang . '/woeid/' . $this->location . '/give');
        }

        $modelAd = new Model_Ad();
        $this->view->allGives = $modelAd->getAdListAllHome(1, $status);

        $this->view->rankingWoeid = $modelAd->getRankingWoeid($limit=170);
        $this->view->rankingUsers = $modelAd->getRankingUsers($limit=80);
        
    }


    public function setlangAction()
    {
        $this->referer = $_SERVER['HTTP_REFERER'];
        $lang = $this->_getParam("language");
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity())
        {
            $umodel = new Model_User();
            $data = (array)$auth->getIdentity();
            $data['lang'] = $lang;
            $umodel->update($data);
            $auth->getStorage()->write((object)$data);
        }

        setcookie ( "lang", $lang, null, '/' );

        if ($this->hasValidReferer())
        {
            $new_url = explode("/", $this->referer);
            if (count($new_url)>3 && strlen($new_url[3])>0) $new_url[3] = $lang;
            $this->_redirect(join("/",$new_url));
        }
        else
            $this->_redirect ( '/' );
    }

    function hasValidReferer()
    {
        if (!$this->referer) return false;

        # invalid if is the same URL
        $currentURI = $_SERVER['SCRIPT_URI'];
        if (strcmp($this->referer, $currentURI) == 0) return false;

        # invalid if is not in this site
        $barpos = strpos($currentURI, "/", 8);
        if (strncmp($this->referer, $currentURI, $barpos ) != 0) return false;

        return true;
    }



}