<?php

/**
 * @var AutoLoad[] $autoloadCollection
 */
$autoloadCollection = array(
	new AutoLoad( "point", X_POINT_ROOT_DIR . DS . "libs" )
);

foreach ( $autoloadCollection as $autoload )
	$autoload->register();

// init Cache
\point\Main\Cache\Manager::getInstance()->initProvider(
	new \point\Main\Cache\Provider\APC()
);