##
# Basic make file for Drupal using composer as a dependency manager.
##

install:
	make updb
	make cim
	make webservice
	make translations
	make front

post-install:
	make updb
	make cim
	make webservice
	make translations
	make cr

webservice:
	ddev exec "cp docroot/modules/custom/generali_webservice/config/WebsalesCommon/CommonExternalWS01.`ddev drush config-get generali_webservice.settings websalescommon_env --include-overridden --format=string`.wsdl docroot/modules/custom/generali_webservice/config/WebsalesCommon/CommonExternalWS01.wsdl"
	ddev exec "cd docroot && ../vendor/bin/soap-client generate modules/custom/generali_webservice/config/WebsalesCommon/config.yml --dest-class=Drupal/generali_webservice/WebsalesCommon/SoapClientContainer modules/custom/generali_webservice/src/WebsalesCommon"

# Perform cache rebuild.
cr:
	ddev drush cr

# Perform database updates.
updb:
	ddev drush updb -y

# Import configuration.
cim:
	ddev drush cim -y

# Export configuration
cex:
	ddev drush cex -y

# Checks for available translation updates and imports the available translation updates.
translations:
	ddev drush locale:check
	ddev drush locale:update

front:
	# Frontend
	ddev exec "cd docroot/themes/custom/athora/gulp && npm ci"
	ddev exec "cd docroot/themes/custom/athora/gulp && ../node_modules/.bin/gulp build"

	# Clear theme cache
	ddev drush cc css-js

front-live:
	ddev exec "cd docroot/themes/custom/athora/gulp && npm ci"
	ddev exec "cd docroot/themes/custom/athora/gulp && ../node_modules/.bin/gulp"
