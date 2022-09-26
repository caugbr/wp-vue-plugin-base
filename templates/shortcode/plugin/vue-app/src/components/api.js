import axios from 'axios';

class WpAPI {
    apiUrl;
    nonce;
    namespace;

    constructor(apiInfo) {
        this.setApiInfo(apiInfo);
    }

    setApiInfo(info) {
        this.apiUrl = info.root;
        this.nonce = info.nonce;
        this.namespace = info.namespace || 'wp/v2';
    }

    /**
     * Generic GET request
     * @param {string} url URL to get
     * @returns Promise
     */
    _get(url) {
        const config = { headers: { "X-WP-Nonce": this.nonce } };
        return axios.get(this.apiUrl + this.namespace + url, config);
    }

    /**
     * 
     * @param {string} url      URL to get
     * @param {object} data     payload
     * @param {string} method   alternative method (PUT, PATCH, DELETE, etc)
     * @returns Promise
     */
    _post(url, data = {}, method = 'POST') {
        const config = { headers: { "X-WP-Nonce": this.nonce } };
        if (method !== 'POST') {
            config.headers['X-HTTP-Method-Override'] = method;
        }
        return axios.post(this.apiUrl + this.namespace + url, data, config);
    }
}

export default WpAPI;