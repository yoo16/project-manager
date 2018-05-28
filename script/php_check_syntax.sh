#!/bin/sh
BASE=`dirname $0`'/../';

find ${BASE}lib    -name \*.php   -exec php -l {} \; | sed -e '/^No/d'
find ${BASE}public -name \*.php   -exec php -l {} \; | sed -e '/^No/d'
find ${BASE}app    -name \*.php   -exec php -l {} \; | sed -e '/^No/d'
find ${BASE}app    -name \*.phtml -exec php -l {} \; | sed -e '/^No/d'
