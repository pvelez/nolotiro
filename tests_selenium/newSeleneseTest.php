<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * Description of newSeleneseTest
 *
 * @author dani
 */
class newSeleneseTest extends PHPUnit_Extensions_SeleniumTestCase {
    
    function setUp() {
        $this->setBrowser("*firefox");
        $this->setBrowserUrl("http://nolotiro/");
    }


    function testLoginUserDani6Case(){
    $this->open("/es/ad/list/woeid/766273/ad_type/give");
    $this->click("link=acceder");
    $this->waitForPageToLoad("30000");
    $this->type("email", "daniel.remeseiro+test6@gmail.com");
    $this->type("password", "9t4wgq");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
  }


    function testChangeLocationBarcelonaCase() {
        $this->open("/es/ad/list/woeid/766273/ad_type/give");
        $this->click("link=cambiar ubicación");
        $this->waitForPageToLoad("30000");
        $this->type("location", "barcelona");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
        $this->click("submit");
        $this->waitForPageToLoad("30000");
    }




}
?>