import * as AutoComplete from "@tarekraafat/autocomplete.js";

class AutocompletejsBundle {
    static init() {
        setTimeout(() => {
            let autocompleteFields = document.querySelectorAll("input[data-autocompletejs='1']");
            if (autocompleteFields.length !== 0) {
                autocompleteFields.forEach((field) => {

                    let options = JSON.parse(field.dataset.autocompletejsOptions);

                    if (options.selector === '' && field.id !== '') {
                        options.selector = '#' + field.id;
                    }

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
                            item.innerHTML = data.match;
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

                    new AutoComplete(options);

                    field.addEventListener('focus', (e) => {
                        let results = document.querySelector('#autocomplete_' + field.id);
                        results.classList.add('show');
                    });

                    field.addEventListener('blur', (e) => {
                        let results = document.querySelector('#autocomplete_' + field.id);
                        results.classList.remove('show');
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
