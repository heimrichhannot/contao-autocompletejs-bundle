<?php


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
     * get config data for autocompletejs
     *
     * @param array $attributes
     * @param DataContainer|null $dc
     * @return array
     */
    public function getAutocompleteConfig(array $attributes = [], $dc = null): array
    {
        if(empty($attributes) || !$attributes['autocompletejs']) {
            return [];
        }

        if(!$attributes['autocompletejs']['active']) {
            return [];
        }

        if(null !== ($pages = $this->getPageWithParents())) {
            $this->addFrontendAssets($pages, $attributes);
        }

        $options = $this->autocompleteManager->getOptionsAsArray($attributes['autocompletejs']['options']);

        $event = $this->eventDispatcher->dispatch(CustomizeAutocompletejsOptionsEvent::NAME, new CustomizeAutocompletejsOptionsEvent(
            $options,
            $attributes,
            $dc
        ));

        return [
            'data-autocompletejs' => '1',
            'data-autocompletejs-options' => json_encode($event->getAutocompletejsOptions())
        ];
    }

    /**
     * @return array|null
     */
    protected function getPageWithParents(): ?array
    {
        /* @var PageModel $objPage */
        global $objPage;

        if(null === $objPage) {
            return null;
        }

        $pageParents = $this->modelUtil->findParentsRecursively('pid', 'tl_page', $objPage);
        $pageParents[] = $objPage;

        return $pageParents;
    }

    /**
     *
     * @param array $pages
     * @param array $attributes
     */
    protected function addFrontendAssets(array $pages, array &$attributes)
    {
        if('text' !== $attributes['type']) {
            return;
        }

        if(false === ($property = $this->dcaUtil->getOverridableProperty('useAutocompletejsForText', $pages))) {
            return;
        }

        if (!$attributes['autocompletejs']['active']) {
            return;
        }

        $this->frontendAsset->addFrontendAssets();
    }
}