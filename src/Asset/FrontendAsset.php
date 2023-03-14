<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AutocompletejsBundle\Asset;

use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class FrontendAsset implements ServiceSubscriberInterface
{
    use PageAssetsTrait;

    public function addFrontendAssets()
    {
        $this->addPageEntrypoint('contao-autocompletejs-bundle', [
            'TL_JAVASCRIPT' => [
                'contao-autocompletejs-bundle' => 'bundles/heimrichhannotautocompletejs/assets/contao-autocompletejs-bundle.js',
                'contao-autocompletejs-bundle-theme' => 'bundles/heimrichhannotautocompletejs/assets/contao-autocompletejs-bundle-theme.js',
            ],
        ]);
        $this->addPageEntrypoint('contao-autocompletejs-bundle-theme', [
            'TL_CSS' => [
                'contao-autocompletejs-bundle' => 'bundles/heimrichhannotautocompletejs/assets/contao-autocompletejs-bundle-theme.css',
            ],
        ]);
    }
}
