<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_HOOKS']['loadDataContainer']['autocompletejs'] = [\HeimrichHannot\AutocompletejsBundle\EventListener\LoadDataContainerListener::class, 'onLoadDataContainer'];
$GLOBALS['TL_HOOKS']['getAttributesFromDca']['autocompletejs'] = [\HeimrichHannot\AutocompletejsBundle\EventListener\GetAttributesFromDcaListener::class, 'onGetAttributesFromDca'];
