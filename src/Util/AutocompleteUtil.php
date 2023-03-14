<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AutocompletejsBundle\Util;

use Contao\DataContainer;
use Contao\PageModel;
use HeimrichHannot\AutocompletejsBundle\Asset\FrontendAsset;
use HeimrichHannot\AutocompletejsBundle\Event\CustomizeAutocompletejsOptionsEvent;
use HeimrichHannot\AutocompletejsBundle\Manager\AutocompleteManager;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AutocompleteUtil
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var DcaUtil
     */
    private $dcaUtil;
    /**
     * @var FrontendAsset
     */
    private $frontendAsset;
    /**
     * @var AutocompleteManager
     */
    private $autocompleteManager;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(ModelUtil $modelUtil, DcaUtil $dcaUtil, FrontendAsset $frontendAsset, AutocompleteManager $autocompleteManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->modelUtil = $modelUtil;
        $this->dcaUtil = $dcaUtil;
        $this->frontendAsset = $frontendAsset;
        $this->autocompleteManager = $autocompleteManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * get config data for autocompletejs.
     *
     * @param DataContainer|null $dc
     */
    public function getAutocompleteConfig(array $attributes = [], $dc = null): array
    {
        if (empty($attributes) || !$attributes['autocompletejs']) {
            return [];
        }

        if (!$attributes['autocompletejs']['active']) {
            return [];
        }

        if (null !== ($pages = $this->getPageWithParents())) {
            $this->addFrontendAssets($pages, $attributes);
        }

        $options = $this->autocompleteManager->getOptionsAsArray($attributes['autocompletejs']['options']);

        $event = new CustomizeAutocompletejsOptionsEvent(
            $options,
            $attributes,
            $dc
        );

        /*
         * @todo Removed in next major version
         */
        if ($this->eventDispatcher->hasListeners(CustomizeAutocompletejsOptionsEvent::NAME)) {
            $this->eventDispatcher->dispatch($event, CustomizeAutocompletejsOptionsEvent::NAME);
            trigger_deprecation('heimrichhannot/contao-autocompletejs-bundle', '0.3.5', 'Use FQCN as event name instead.');
        }

        $this->eventDispatcher->dispatch($event);

        return [
            'data-autocompletejs' => '1',
            'data-autocompletejs-options' => json_encode($event->getAutocompletejsOptions()),
        ];
    }

    protected function getPageWithParents(): ?array
    {
        /* @var PageModel $objPage */
        global $objPage;

        if (null === $objPage) {
            return null;
        }

        $pageParents = $this->modelUtil->findParentsRecursively('pid', 'tl_page', $objPage);
        $pageParents[] = $objPage;

        return $pageParents;
    }

    protected function addFrontendAssets(array $pages, array &$attributes)
    {
        if ('text' !== $attributes['type']) {
            return;
        }

        if (false === ($property = $this->dcaUtil->getOverridableProperty('useAutocompletejsForText', $pages))) {
            return;
        }

        if (!$attributes['autocompletejs']['active']) {
            return;
        }

        $this->frontendAsset->addFrontendAssets();
    }
}
