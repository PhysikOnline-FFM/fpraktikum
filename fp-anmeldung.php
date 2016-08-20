<?php
/**
 * Anmeldungsmaske für das FPraktikum des FB Physik. Die Datei wird über das POInclude plugins
 * eingebunden (<PO:Include id="fpraktikum">). Dabei wird nur die Funktion htmlReturn() aufgerufen.
 * August 2016
 */
error_reporting(-1);

// ilUser global
//$ilUser;

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

		$this->html = "
		  <small>Dateien liegen in der VM unter Customizing/global/include/fpraktikum/</small>
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
				<span id='institutWahl'></span>
        <br>
        <input onchange=partnerWahl(this) type='checkbox' name='check-partner'>Ich möchte eine Partnerin/einen Partner angeben.</input>
        <br>
        <span id='partnerWahl'></span>


		    <input class='submit' type='submit'>
		  </form>
			<script type='text/javascript' src='./Customizing/global/include/fpraktikum/fp-anmeldung.js'></script>
			";
	}

	/**
	* Returns the actual html form for the registration side.
	* Should be the only function being called from outside.
	*/
	public function htmlReturn() {
		return $this->html;
	}
}
