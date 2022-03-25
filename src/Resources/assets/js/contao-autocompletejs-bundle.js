import * as AutoComplete from "@tarekraafat/autocomplete.js";

class AutocompletejsBundle {
    static init() {

        const turnOffAutocomplete = (node) => {
            node.setAttribute('autocomplete', 'off');
        }

        setTimeout(() => {
            let autocompleteFields = document.querySelectorAll("input[data-autocompletejs='1']");

            if (autocompleteFields.length !== 0) {
                autocompleteFields.forEach((field) => {

                    let options = JSON.parse(field.dataset.autocompletejsOptions);

                    if (options.selector === '' && field.id !== '') {
                        options.selector = '#' + field.id;
                    }

                    turnOffAutocomplete(field)

                    // decrease threshold since
                    options.threshold = options.threshold - 1;

                    // configuration of the resultsList to prevent PAIN
                    options.resultsList = {
                        tabSelection: true,
                        id: 'autocomplete_' + field.id,
                        class: 'autocomplete_results_container',
                        position: 'afterend',
                        maxResults: options.maxResults || 5
                    };

                    // configuration of resultItem
                    // with CustomEvent to modify results
                    options.resultItem = {
                        tag: 'li',
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
                        }
                    };

                    // remove searchEngine if set to none
                    if (options.searchEngine === 'none') {
                        options.searchEngine = (query, record) => {
                            return record;
                        }
                    }

                    // preparing settings from the dca field
                    if (!Array.isArray(options.data.src) && options.data.type === 'function') {

                        if (!options.data.url) {
                            return [];
                        }

                        let data = [];

                        options.data.src = (query) => {

                            if (query.length <= options.threshold) {
                                return Promise.resolve([]);
                            }
                            // fetch data if source-url is set
                            AutocompletejsBundle.getData(options.data.url.replace('{query}', query), (err, res) => {
                                if (err) {
                                    return err;
                                }

                                data = JSON.parse(res);
                            });
                            return Promise.resolve(data);
                        };
                    }

                    let autoComplete = new AutoComplete(options);

                    field.addEventListener('focus', (e) => {
                        autoComplete .open();
                    });

                    field.addEventListener('blur', (e) => {
                        autoComplete .close();
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

    static getData(url, cb) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', encodeURI(url), false);
        xhr.onload = () => {
            if (xhr.status === 200) {
                cb(null, xhr.response);
            } else {
                cb(xhr.status, xhr.response);
            }
        };
        xhr.send();
    }
}

export {AutocompletejsBundle};
