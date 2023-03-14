<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AutocompletejsBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use HeimrichHannot\AutocompletejsBundle\Util\AutocompleteUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * @Hook("getAttributesFromDca")
 */
class GetAttributesFromDcaListener
{
    protected bool $closed = false;
    private AutocompleteUtil $autocompleteUtil;
    private Utils $utils;

    public function __construct(AutocompleteUtil $autocompleteUtil, Utils $utils)
    {
        $this->autocompleteUtil = $autocompleteUtil;
        $this->utils = $utils;
    }

    public function close()
    {
        $this->closed = true;
    }

    public function open()
    {
        $this->closed = false;
    }

    /**
     * @param DataContainer $dc
     */
    public function __invoke(array $attributes, $dc = null): array
    {
        if(!($attributes['autocompletejs'] ?? false)) {
            return $attributes;
        }

        if ($this->closed || !$this->utils->container()->isFrontend() || !\in_array($attributes['type'], ['select', 'text'])) {
            $this->open();

            return $attributes;
        }

        return array_merge($attributes, $this->autocompleteUtil->getAutocompleteConfig($attributes, $dc));
    }
}
