<?php


namespace HeimrichHannot\AutocompletejsBundle\EventListener;


use HeimrichHannot\AutocompletejsBundle\Util\AutocompleteUtil;
use HeimrichHannot\FilterBundle\Event\AdjustFilterOptionsEvent;

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

        $options         = $event->getOptions();
        $options['attr'] = array_merge($options['attr'], $this->autocompleteUtil->getAutocompleteConfig($attributes));

        $event->setOptions($options);
    }

    /**
     * get autocomplete attribute config from dca field configuration
     *
     * @param AdjustFilterOptionsEvent $event
     * @return array
     */
    public function getAutocompleteAttributes(AdjustFilterOptionsEvent $event): array
    {
        $dcaField = $this->getDcaFieldForElement($event);

        if (!$dcaField || !$dcaField['eval']['autocompletejs']) {
            return [];
        }

        return [
            'type' => $dcaField['inputType'],
            'autocompletejs' => $dcaField['eval']['autocompletejs']
        ];
    }

    /**
     * get the dca field config by the current filter element
     *
     * @param AdjustFilterOptionsEvent $event
     * @return mixed
     */
    protected function getDcaFieldForElement(AdjustFilterOptionsEvent $event): ?array
    {
        $element      = $event->getElement();
        $filterConfig = $event->getConfig();
        $filter       = $filterConfig->getFilter();
        $field        = $element->field ?: $element->name;

        if (!$GLOBALS['TL_DCA'][$filter['dataContainer']]) {
            return [];
        }

        return $GLOBALS['TL_DCA'][$filter['dataContainer']]['fields'][$field];
    }
}