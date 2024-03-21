<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AutocompletejsBundle\EventListener;

use HeimrichHannot\AutocompletejsBundle\Util\AutocompleteUtil;
use HeimrichHannot\FilterBundle\Event\AdjustFilterOptionsEvent;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;

/**
 * @ServiceTag("kernel.event_listener", event="huh.filter.event.adjust_filter_options_event")
 */
class AdjustFilterOptionsEventListener
{
    /**
     * @var AutocompleteUtil
     */
    private $autocompleteUtil;

    public function __construct(AutocompleteUtil $autocompleteUtil)
    {
        $this->autocompleteUtil = $autocompleteUtil;
    }

    public function __invoke(AdjustFilterOptionsEvent $event)
    {
        if (empty($attributes = $this->getAutocompleteAttributes($event))) {
            return;
        }

        $options = $event->getOptions();
        $options['attr'] = array_merge($options['attr'], $this->autocompleteUtil->getAutocompleteConfig($attributes));

        $event->setOptions($options);
    }

    /**
     * get autocomplete attribute config from dca field configuration.
     */
    public function getAutocompleteAttributes(AdjustFilterOptionsEvent $event): array
    {
        $dcaField = $this->getDcaFieldForElement($event);

        if (!$dcaField || !($dcaField['eval']['autocompletejs'] ?? null)) {
            return [];
        }

        return [
            'type' => ($dcaField['inputType'] ?? null),
            'autocompletejs' => $dcaField['eval']['autocompletejs'],
        ];
    }

    /**
     * get the dca field config by the current filter element.
     *
     * @return mixed
     */
    protected function getDcaFieldForElement(AdjustFilterOptionsEvent $event): ?array
    {
        $element = $event->getElement();
        $filterConfig = $event->getConfig();
        $filter = $filterConfig->getFilter();
        $field = $element->field ?: $element->name;

        if (!$GLOBALS['TL_DCA'][$filter['dataContainer']]) {
            return [];
        }

        return $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$field] ?? null;
    }
}
