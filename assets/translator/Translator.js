const __TRANS = {
    en: {
        'row_selected': {
            '1' : '%c% row selected.',
            '_': '%c% rows selected.'
        },
        'cancel': 'Cancel',
        'confirm': 'Confirm',
        'unauthorized_error': 'You are disconnected. Refresh page to login',
        'forbidden_error': 'You are not allowed to perform this action',
        'notfound_error': 'Unable to contact server',
        'other_error' : 'An error occured',
        'loading_data_error': 'Unable to load data',
        'no_results': 'No results found'
    }
}

import TRANSLATIONS from './umbrella-admin-translations.json'

export default class Translator {

    constructor(locale, fallbackLocale = 'en') {
        this.locale = locale;
        this.fallbackLocale = locale
    }


    trans(key, params = {}, locale = null) {

        if (null === locale) {
            locale = this.locale
        }

        // search for locale
        let translation = this._search(key, locale)

        // search for fallback locale
        if (locale !== this.fallbackLocale && false === translation) {
            translation = this._search(key, this.fallbackLocale)
        }

        // no translation found
        if (false === translation) {
            return key
        }

        // replace params
        for (const [k, v] of Object.entries(params)) {
            translation = translation.replace(k, v)
        }

        return translation;
    }

    _search(key, locale) {
        return TRANSLATIONS[locale] && TRANSLATIONS[locale][key] && typeof TRANSLATIONS[locale][key] === 'string'
            ? TRANSLATIONS[locale][key]
            : false
    }
}
