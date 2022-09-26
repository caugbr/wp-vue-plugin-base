import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        lang: 'en_US',
        waitOverlayInfo: {
            visible: false,
            message: '',
            dismissible: false
        },
        userMessageInfo: {
            title: '',
            message: '',
            messageType: 'success',
            dismissible: true,
            autoClose: 0,
            routerPath: ''
        },
        pluginUrl: '',
        apiSettings: {
            root:'',
            nonce: '',
            namespace: 'wp/v2'
        }
    },
    mutations: {
        WAIT_OVERLAY_INFO(state, obj) {
            const defs = { visible: false, message: '', dismissible: false };
            state.waitOverlayInfo = { ...defs, ...obj };
        },
        UPDATE_MESSAGE(state, info) {
            state.userMessageInfo = {
                ...{
                    title: '',
                    message: '',
                    messageType: 'success',
                    dismissible: true,
                    autoClose: 0,
                    routerPath: ''
                },
                ...info
            };
        },
        SET_PLUGIN_URL(state, url) {
            state.pluginUrl = url;
        },
        SET_API_SETTINGS(state, obj) {
            state.apiSettings = { ...state.apiSettings, ...obj };
        },
        SET_LANG(state, lng) {
            state.lang = lng;
        }
    },
    actions: {
        setWaitOverlayInfo({ commit }, obj) {
            if (typeof obj === 'boolean') {
                obj = { visible: obj };
            }
            if (typeof obj === 'string') {
                obj = { visible: true, message: obj };
            }
            commit('WAIT_OVERLAY_INFO', obj);
        },
        setUserMessage({ commit }, obj) {
            commit('UPDATE_MESSAGE', obj);
        },
        clearUserMessage({ commit }) {
            commit('UPDATE_MESSAGE', {});
        },
        setPluginUrl({ commit }, url) {
            commit('SET_PLUGIN_URL', url);
        },
        setApiSettings({ commit }, obj) {
            commit('SET_API_SETTINGS', obj);
        },
        setLang({ commit }, lng) {
            commit('SET_LANG', lng);
        }
    }
});
