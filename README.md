# Anmeldemaske für das FPraktikum
Dateien liegen im Rootverzeichnis des Webservers unter `Customizing/global/include/fpraktikum/`. Erreichbar (momentan) über http://5-1.ilias.physikelearning.de/goto.php?target=cat_11819&client_id=FB13-PhysikOnline

	|--database
	|  |--class.Database.php	# main class for database
	|  |--class.FP-Database.php # custom class for interaction with database, FP related
	|--js
	|  |--fp-anmeldung.js		# js file for registration form
	|  |--fp-abmeldung.js
	|--admin
	|  |--fp-admin.php			# admin site for adding institutes
	|--submit
	|  |--fp-submit.php			# file responsible for writing registration to DB
	|  |--fp-abmeldung.php			# file for deleting registration from DB
	|  |--fp-partner-anmeldung.php # file for handling partner registration
	|--fp-anmeldung.php			# main file containing the actual form
	|--fp-ajax-request.php		# file handling ajax requests

