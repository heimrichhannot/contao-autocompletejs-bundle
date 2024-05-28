import autoComplete from "@tarekraafat/autocomplete.js/src/autoComplete.js";

class AutocompletejsBundle {
    static init() {
        console.log('init')

        const turnOffAutocomplete = (node) => {
            node.setAttribute('autocomplete', 'off');
        };

        setTimeout(() => {
            let autocompleteFields = document.querySelectorAll("input[data-autocompletejs='1']");

            if (autocompleteFields.length !== 0) {
                autocompleteFields.forEach((field) => {

                    let ctx = JSON.parse(field.dataset.autocompletejsOptions);

                    if (!ctx.selector) {
                        if (typeof ctx.selector === 'undefined' && typeof field.id !== 'undefined') {
                            ctx.selector = '#' + field.id;
                        } else {
                            ctx.selector = () => field;
                        }
                    }

                    turnOffAutocomplete(field);

                    AutocompletejsBundle.configureData(ctx);

                    // decrease threshold since
                    ctx.threshold = ctx.threshold - 1;

                    let maxResults = (ctx.resultsList && ctx.resultsList.maxResults) ? ctx.resultsList.maxResults : ctx.maxResults ?? 5;
                    delete ctx.maxResults;

                    // configuration of the resultsList to prevent PAIN
                    ctx.resultsList = {
                        tabSelection: true,
                        id: 'autocomplete_' + field.id,
                        class: 'autocomplete_results_container',
                        position: 'afterend',
                        maxResults: maxResults,
                    };

                    // configuration of resultItem
                    // with CustomEvent to modify results
                    ctx.resultItem = {
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
                        highlight: ctx.highlight
                    };

                    // remove searchEngine if set to none
                    if (ctx.searchEngine === 'none') {
                        ctx.searchEngine = (query, record) => {
                            return record;
                        };
                    }

                    let ac = new autoComplete(ctx);

                    field.addEventListener('focus', (e) => {
                        ac.start();
                    });

                    field.addEventListener('blur', (e) => {
                        ac.close();
                    });

                    field.addEventListener('selection', e => {

                        let value = e.detail.selection.value;

                        if (ctx.data.keys) {
                            value = e.detail.selection.value[ctx.data.keys[0]];
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
                    // console.log(source);
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
