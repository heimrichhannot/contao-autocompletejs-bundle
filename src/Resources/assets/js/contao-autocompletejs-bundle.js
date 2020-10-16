import * as autoComplete from "@tarekraafat/autocomplete.js";

class AutocompletejsBundle {
    static init() {
        let autocompleteFields = document.querySelectorAll("input[data-autocompletejs='1']");
        if (autocompleteFields.length !== 0) {
            autocompleteFields.forEach((field) => {
                let options = JSON.parse(field.dataset.autocompletejsOptions);

                // decrease threshold since
                options.threshold = options.threshold - 1;

                // configuration of the resultsList to prevent PAIN
                options.resultsList = {
                    render: true,
                    container: source => {
                        source.setAttribute('id', 'autocomplete_' + field.id);
                        source.setAttribute('class', 'autocomplete_results_container');
                    },
                    position: 'afterend',
                    element: 'ul'
                }

                // configuration of resultItem
                // with CustomEvent to modify results
                options.resultItem = {
                    element: 'li',
                    content: (data, source) => {
                        source.innerHTML = data.match;

                        document.dispatchEvent(
                            new CustomEvent('huh.autocompletejs.adjust_result_item', {
                                bubbles: true,
                                cancelable: true,
                                detail: {
                                    source: source,
                                    data: data,
                                }
                            })
                        );
                    }
                }

                // CustomEvent to modify behavior on selecting an item
                options.onSelection = (item) => {
                    let value = item.selection.value;

                    if(item.selection.key) {
                        value = item.selection.value[item.selection.key];
                    }

                    document.querySelector('#' + field.id).value = value;

                    document.dispatchEvent(
                        new CustomEvent('huh.autocompletejs.onselection', {
                            bubbles: true,
                            cancelable: true,
                            detail: {
                                field: field,
                                item: item
                            }
                        })
                    );
                }

                // preparing settings from the dca field
                if (!Array.isArray(options.data.src) && options.data.type === 'function') {

                    if (!options.data.url) {
                        return {};
                    }

                    let data = {};

                    options.data.src = () => {
                        let query = document.querySelector('#' + field.id).value;

                        if (query.length <= options.threshold) {
                            return [];
                        }

                        // fetch data if source-url is set
                        AutocompletejsBundle.getData(options.data.url.replace('{query}', query), (err, res) => {
                            if (err) {
                                return err;
                            }

                            data = JSON.parse(res);
                        });

                        return data;
                    };
                }

                new autoComplete(options);

                field.addEventListener('focus', (e) => {
                    let results = document.querySelector('#autocomplete_'+field.id);
                    results.classList.add('show');
                } )
                field.addEventListener('blur', (e) => {
                    let results = document.querySelector('#autocomplete_'+field.id);
                    results.classList.remove('show');
                })
            })
        }
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

export { AutocompletejsBundle };