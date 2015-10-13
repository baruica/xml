#!/usr/bin/env sh

#composer --quiet --no-interaction update --optimize-autoloader > /dev/null

[ -x vendor/bin/phpspec ] && vendor/bin/phpspec --no-interaction run -f dot
