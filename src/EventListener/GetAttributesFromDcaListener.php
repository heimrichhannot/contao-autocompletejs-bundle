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
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GetAttributesFromDcaListener
{
    /**
     * @var bool
     */
    protected $closed = false;
    private $pageParents = null;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var FrontendAsset
     */
    private $frontendAsset;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * GetAttributesFromDcaListener constructor.
     *
     * @param null $pageParents
     */
    public function __construct(ContainerInterface $container, FrontendAsset $frontendAsset, EventDispatcherInterface $eventDispatcher)
    {
        $this->container = $container;
        $this->frontendAsset = $frontendAsset;
        $this->eventDispatcher = $eventDispatcher;
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
        if ($this->closed || !$this->container->get('huh.utils.container')->isFrontend() || !\in_array($attributes['type'], ['select', 'text'])) {
            $this->open();

            return $attributes;
        }

        $this->getPageWithParents();

        if (null !== $this->pageParents) {
            if ('text' === $attributes['type']) {
                $property = $this->container->get('huh.utils.dca')->getOverridableProperty('useAutocompletejsForText', $this->pageParents);

                if (true === (bool) $property) {
                    if ($attributes['autocompletejs']['active']) {
                        $attributes['data-autocompletejs'] = true;
                        $this->frontendAsset->addFrontendAssets();
                    }
                }
            }
        }

        $customOptions = [];

        if (isset($attributes['autocompletejs']['options']) && \is_array($attributes['autocompletejs']['options'])) {
            $customOptions = $attributes['autocompletejs']['options'];
        }

        if ($attributes['autocompletejs']['active']) {
            $customOptions = $this->container->get('huh.autocompletejs.manager.autocomplete_manager')->getOptionsAsArray($customOptions);
        }
        $event = $this->eventDispatcher->dispatch(CustomizeAutocompletejsOptionsEvent::NAME, new CustomizeAutocompletejsOptionsEvent(
            $customOptions,
            $attributes,
            $dc
        ));

        if ($attributes['autocompletejs']['active']) {
            $attributes['data-autocompletejs-options'] = json_encode($event->getAutocompletejsOptions());
        }

        return $attributes;
    }

    protected function getPageWithParents()
    {
        /* @var PageModel $objPage */
        global $objPage;

        if (null === $this->pageParents && null !== $objPage) {
            $this->pageParents = $this->container->get('huh.utils.model')->findParentsRecursively('pid', 'tl_page', $objPage);
            $this->pageParents[] = $objPage;
        }

        return $this->pageParents;
    }
}
