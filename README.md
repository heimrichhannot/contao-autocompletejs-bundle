# Contao autoCompleteJs Bundle

This bundle offers support for the JavaScript library [autoComplete.js](https://github.com/TarekRaafat/autoComplete.js) for the Contao CMS.

## Features

- activate autocompletejs support on page level (with inheritance and override option)
- customize options from dca

## Installation

Install via composer: `composer require heimrichhannot/contao-autocompletejs-bundle` and update your database.

## Usage

Active or deactivate autocompletejs support on page level (Layout section). You can overwrite settings from a parent page.

## Configuration
To activate autocompletejs on a dca field add  

Add `autocompletejs` configuration to the `eval` array of dca field :
```php
    'fieldName' => [
        'eval' = [
            'autocompletejs' => [ 
                'active' => true,
                'options' => []
            ]
        ]
    ]
```

Possible configuration options:

Option name|Type|Value
---|----|---
data|Array|type, url, key, cache
data.type|String|'array','function'
data.url|String|url to be fetched for data
data.key|Array|keys of the data array if available
data.cache|Boolean|cache the input
searchEngine|String|'strict'
placeHolder|String|placeholder of the input field
selector|String|id of the input field
threshold|Integer|minimum number of characters to trigger autocomplete
debounce|Integer|idle time after entering new character (milliseconds) 
maxResults|Integer|maximum number of results shown in the dropdown menu
highlight|Boolean|show entered characters in the results dropdown menu

## JavaScript Events
Following events can be used to further customize the choices instances: 

Event name | Data | Description
---------- | ---- | -----------
huh.autocompletejs.adjust_result_item | field, item | Customize matched Item in the dropdown menu (`resultItem` configuration option of the autoComplete object)
huh.autocompletejs.onselection | source, data | Customize selection behavior of selected Item (`onSelection` configuration options of the autoComplete object)