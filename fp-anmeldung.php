<?php

error_reporting(-1);

// ilUser global
$ilUser;

class fpraktikum
{
	protected $html = "";
	protected $ilUser;

	protected $user_firstname;
	protected $user_lastname;
	protected $user_matrikel;

	public function __construct() {
		global $ilUser;
		$this->ilUser = $ilUser;

		$this->user_firstname = $ilUser->getFirstname();
		$this->user_lastname = $ilUser->getLastname();
		$this->user_matrikel = $ilUser->getMatriculation();

		// test html-text
		//$this->html = file_get_contents('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/POIncludes/classes/fp-form.html', true);
		$this->html = "
		  <small>Dateien liegen in der VM unter local/includes/fpraktikum/</small>
		  <form action='gibtsnochnicht.php' method='post'>
		    <p>Folgende Daten werden automatisch mitgeschickt:</p>
		    
		    <input type='hidden' name='firstname' value='".$this->user_firstname."'>
		    <input type='hidden' name='lastname' value='".$this->user_lastname."'>
		    <input type='hidden' name='matrikel' value='".$this->user_matrikel."'>
		    
		    <p>Name: ".$this->user_firstname." ".$this->user_lastname."</p>
		    <p>Matrikelnummer: ".$this->user_matrikel."</p><br>

		    <p>Dein Studiengang: 
		      <input onchange=institutWahl('bachelor') type='radio' name='studiengang' value='Bachelor'>Bachelor
		      <input onchange=institutWahl('master') type='radio' name='studiengang' value='Master'>Master
		    </p>
		    <br>
		    <span id='institutWahl'></span>
		    
		    Dein Lieblingspartner: <input type='text'><br>
		    <input class='submit' type='submit'>
		  </form>";
		
		$this->js = file_get_contents('./local/includes/fpraktikum/fp-anmeldung.js');
		$this->html .= "<script>".$this->js."</script>";
	}

	/**
	* Returns the actual html form for the registration side.
	* Should be the only function being called from outside.
	*/
	public function htmlReturn() {
		return $this->html;
	}
}
