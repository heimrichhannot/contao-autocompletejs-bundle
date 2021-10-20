import { AutocompletejsBundle } from './contao-autocompletejs-bundle';

document.addEventListener('DOMContentLoaded', AutocompletejsBundle.init);
document.addEventListener('formhybrid_ajax_complete', AutocompletejsBundle.init);
