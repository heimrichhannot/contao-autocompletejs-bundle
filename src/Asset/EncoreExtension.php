<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AutocompletejsBundle\Asset;

use HeimrichHannot\AutocompletejsBundle\HeimrichHannotAutocompletejsBundle;
use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;

class EncoreExtension implements EncoreExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function getBundle(): string
    {
        return HeimrichHannotAutocompletejsBundle::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getEntries(): array
    {
        return [
            EncoreEntry::create('contao-autocompletejs-bundle', 'src/Resources/assets/js/contao-autocompletejs-bundle-init.js')
                ->addJsEntryToRemoveFromGlobals('contao-autocompletejs-bundle')
                ->addJsEntryToRemoveFromGlobals('contao-autocompletejs-bundle--library'),
            EncoreEntry::create('contao-autocompletejs-bundle-theme', 'src/Resources/assets/js/contao-autocompletejs-bundle-theme.js')
                ->setRequiresCss(true)
                ->addCssEntryToRemoveFromGlobals('contao-autocompletejs-bundle'),
        ];
    }
}
