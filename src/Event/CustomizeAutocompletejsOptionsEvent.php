<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AutocompletejsBundle\Event;

use Contao\DataContainer;
use Symfony\Contracts\EventDispatcher\Event;

class CustomizeAutocompletejsOptionsEvent extends Event
{
    const NAME = 'huh.autocompletejs.customize_autocompletejs_options';

    /**
     * @var array
     */
    private $autocompletejsOptions;

    /**
     * @var array
     */
    private $fieldAttributes;

    /**
     * @var DataContainer|null
     */
    private $dc;

    /**
     * CustomizeChoicesOptionsEvent constructor.
     *
     * @param DataContainer|null $dc
     */
    public function __construct(array $customOptions, array $fieldAttributes, $dc)
    {
        $this->autocompletejsOptions = $customOptions;
        $this->fieldAttributes = $fieldAttributes;
    }

    public function getAutocompletejsOptions(): array
    {
        return $this->autocompletejsOptions;
    }

    public function setAutocompletejsOptions(array $options)
    {
        $this->autocompletejsOptions = $options;
    }

    public function getFieldAttributes(): array
    {
        return $this->fieldAttributes;
    }

    public function getDc()
    {
        return $this->dc;
    }
}
