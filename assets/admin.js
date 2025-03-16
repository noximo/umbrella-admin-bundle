import './scss/admin.scss'

import Translator from './translator/Translator';
import Spinner from './ui/Spinner'
import ConfirmModal from './ui/ConfirmModal'
import Toast from './ui/Toast'
import JsResponseHandler from './jsresponse/JsResponseHandler';
import configureHandler from './jsresponse/Configure'
import UmbrellaDataTable from './datatable/UmbrellaDataTable';
import UmbrellaCollection from './form/UmbrellaCollection';
import DatePicker from './form/DatePicker';
import PasswordTogglable from './form/PasswordTogglable';
import BindUtils from './utils/BindUtils';
import UmbrellaNotification from './UmbrellaNotification';
import UmbrellaSidebar from './UmbrellaSidebar';
import UmbrellaAutocomplete from './form/UmbrellaAutocomplete';

const locale = document.querySelector('html').getAttribute('lang') || 'en'

window.umbrella = {
    locale,
    translator: new Translator(locale),
    spinner: Spinner,
    confirmModal: ConfirmModal,
    toast: Toast
}

// --- DataTable.js
customElements.define('umbrella-datatable', UmbrellaDataTable);

// --- Forms
customElements.define('umbrella-datepicker', DatePicker, {extends: 'input'});
customElements.define('umbrella-collection', UmbrellaCollection);
customElements.define('umbrella-autocomplete', UmbrellaAutocomplete, {extends: 'select'});
customElements.define('password-togglable', PasswordTogglable, {extends: 'div'});

// --- Admin components
customElements.define('umbrella-notification', UmbrellaNotification, {extends: 'li'});
customElements.define('umbrella-sidebar', UmbrellaSidebar, {extends: 'nav'});

// --- JsResponseHandler
const jsResponseHandler = new JsResponseHandler();
configureHandler(jsResponseHandler);

window.umbrella.jsResponseHandler = jsResponseHandler

// --- Bind some elements
BindUtils.enableAll();
