<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AutocompletejsBundle\EventListener;

use Contao\DataContainer;
use Contao\PageModel;
use HeimrichHannot\AutocompletejsBundle\Asset\FrontendAsset;
use HeimrichHannot\AutocompletejsBundle\Event\CustomizeAutocompletejsOptionsEvent;
use HeimrichHannot\AutocompletejsBundle\Util\AutocompleteUtil;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GetAttributesFromDcaListener
{
    /**
     * @var bool
     */
    protected $closed = false;
    /**
     * @var AutocompleteUtil
     */
    private $autocompleteUtil;
    /**
     * @var ContainerUtil
     */
    private $containerUtil;

    /**
     * GetAttributesFromDcaListener constructor.
     *
     * @param null $pageParents
     */
    public function __construct(AutocompleteUtil $autocompleteUtil, ContainerUtil $containerUtil)
    {
        $this->autocompleteUtil = $autocompleteUtil;
        $this->containerUtil = $containerUtil;
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
     * @Hook("getAttributesFromDca")
     *
     * @param DataContainer $dc
     */
    public function onGetAttributesFromDca(array $attributes, $dc = null): array
    {
        if(!$attributes['autocompletejs']) {
            return $attributes;
        }

        if ($this->closed || !$this->containerUtil->isFrontend() || !\in_array($attributes['type'], ['select', 'text'])) {
            $this->open();

            return $attributes;
        }

        return array_merge($attributes, $this->autocompleteUtil->getAutocompleteConfig($attributes, $dc));
    }
}
