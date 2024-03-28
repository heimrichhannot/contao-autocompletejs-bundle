import * as AutoComplete from "@tarekraafat/autocomplete.js";

class AutocompletejsBundle {
    static init() {

        const turnOffAutocomplete = (node) => {
            node.setAttribute('autocomplete', 'off');
        };

        setTimeout(() => {
            let autocompleteFields = document.querySelectorAll("input[data-autocompletejs='1']");

            if (autocompleteFields.length !== 0) {
                autocompleteFields.forEach((field) => {

                    let options = JSON.parse(field.dataset.autocompletejsOptions);

                    if (typeof options.selector === 'undefined' && typeof field.id !== 'undefined') {
                        options.selector = '#' + field.id;
                    }

                    turnOffAutocomplete(field);

                    this.configureData(options);


                    // decrease threshold since
                    options.threshold = options.threshold - 1;

                    let maxResults = (options.resultsList && options.resultsList.maxResults) ? options.resultsList.maxResults : options.maxResults ?? 5;
                    delete options.maxResults;

                    // configuration of the resultsList to prevent PAIN
                    options.resultsList = {
                        tabSelection: true,
                        id: 'autocomplete_' + field.id,
                        class: 'autocomplete_results_container',
                        position: 'afterend',
                        maxResults: maxResults,
                    };

                    // configuration of resultItem
                    // with CustomEvent to modify results
                    options.resultItem = {
                        tag: 'li',
                        id: 'autoComplete_result_' + field.id,
                        class: 'autoComplete_result',
                        element: (item, data) => {

                            if(typeof data.match === 'object') {
                                item.innerHTML = Object.values(data.match)[0];
                            } else {
                                item.innerHTML = data.match;
                            }

                            document.dispatchEvent(
                                new CustomEvent('huh.autocompletejs.adjust_result_item', {
                                    bubbles: true,
                                    cancelable: true,
                                    detail: {
                                        source: item,
                                        data: data
                                    }
                                })
                            );
                        },
                        highlight: options.highlight
                    };

                    // remove searchEngine if set to none
                    if (options.searchEngine === 'none') {
                        options.searchEngine = (query, record) => {
                            return record;
                        };
                    }

                    let autoComplete = new AutoComplete(options);

                    field.addEventListener('focus', (e) => {
                        autoComplete.start();
                    });

                    field.addEventListener('blur', (e) => {
                        autoComplete.close();
                    });

                    field.addEventListener('selection', e => {

                        let value = e.detail.selection.value;

                        if (options.data.key) {
                            value = e.detail.selection.value[options.data.key[0]];
                        }

                        // document.querySelector('#' + field.id).value = value;
                        field.value = value;
                        document.dispatchEvent(
                            new CustomEvent('huh.autocompletejs.onselection', {
                                bubbles: true,
                                cancelable: true,
                                detail: {
                                    field: field,
                                    item: e.detail
                                }
                            })
                        );
                    });
                });
            }
        }, 100);

    }

    static configureData(options) {
        let data = options.data;

        // fallback for data.url. Could be removed or better implemented in the future
        if (!('src' in data) && ('url' in data)) {
            let url = data.url;
            data.src = async (query) => {
                try {
                    const source = await fetch(url.replace('{query}', query));
                    console.log(source);
                    let result = await source.json();
                    return result;
                    // return await source.json();
                } catch (error) {
                    return error;
                }
            };
            delete data.url;
        }

        if ('key' in data && !('keys' in data)) {
            console.log('Using data.key is deprecated. Please use data.keys instead.');
            data.keys = data.key;
        }

        options.data = data;
    }
}

export {AutocompletejsBundle};
