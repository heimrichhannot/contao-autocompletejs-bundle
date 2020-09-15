<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\AutocompletejsBundle\Manager;

use Contao\Controller;

class AutocompleteManager
{
    public function getOptionsAsArray(array $customOptions = [], string $table = '', string $field = ''): array
    {
        $options = $this->getDefaultOptions();

        if (!empty($table) && !empty($field)) {
            $options = array_merge($options, $this->getOptionsFromDca($table, $field));
        }
        $options = array_merge($options, $customOptions);

        return $options;
    }

    public function getOptionsFromDca(string $table, string $field): array
    {
        Controller::loadDataContainer($table);
        $options = [];
        $dca = &$GLOBALS['TL_DCA'][$table];

        if (isset($dca['fields'][$field]['eval']['autocompletejsOptions']) && \is_array($dca['fields'][$field]['eval']['autocompletejsOptions'])) {
            $options = $dca['fields'][$field]['eval']['autocompletejsOptions'];
        }

        return $options;
    }

    public function getDefaultOptions(): array
    {
        return [
            'data' => [
                'src' => [],
                'key' => [],
                'cache' => false,
            ],
        ];
    }
}
