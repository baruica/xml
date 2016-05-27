tests:
	[ -x vendor/bin/phpspec ] && vendor/bin/phpspec --no-interaction run -f dot
