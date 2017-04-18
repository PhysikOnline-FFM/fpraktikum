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
	|  |--class.fp_register.php		# class for all registration processes
	|  |--fp-submit.php			# file responsible for writing registration to DB
	|  |--fp-abmeldung.php			# file for deleting registration from DB
	|--fp-anmeldung.php			# main file containing the actual form
	|--fp-ajax-request.php		# file handling ajax requests
	|--include
	|  |--class.exporter.php	# class to export the registrations out of the Database in a clean file
	|  |--class.fp_error.php	# class for Errorreporting within the Fp
	|  |--class.helper.php		# class with a few static methods for "Helping"
	|  |--class.logger.php		# class to log the registrations
	|  |--class.mail.php		# class for automatically mailing the registrant and/or partner
	|  |--class.template.php	#
	|  |--footer.php		# footer for the registration
	|  |--fp_constants.php		# file containing necessary constants
	|  |--header.php		# file containing the header for the FP
	|--templates
	|  |--mail.tpl					# mail template
	|  |--mail_partner_accepts.tpl			# mail template if partner accepts (to partner)
	|  |--mail_partner_accepts_registrant.tpl	# mail template if partner accepts (to registrant)
	|  |--mail_partner_denies.tpl			# mail template if partner denies (to partner)
	|  |--mail_partner_denies_registrant.tpl	# mail template if partner accepts (to registrant)
	|  |--mail_partner_inform.tpl			# mail template if partner was set (to inform partner)
	|  |--mail_partner_registers.tpl		# mail template if partner registers (to partner)
	|  |--mail_register.tpl				# mail template if a user has registered (to user)
	|  |--mail_signoff_registrant.tpl		# mail template if a user has signed off (to user)
	|  |--mail_signoff_registrant_partner.tpl	# mail template if registrant signed off (to partner)
	
	
	
