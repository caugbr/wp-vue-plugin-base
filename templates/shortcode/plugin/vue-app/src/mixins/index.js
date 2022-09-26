const globalMixins = {
    computed: {
        waitOverlayInfo() {
            return this.$store.state.waitOverlayInfo;
        },
        userMessageInfo() {
            return this.$store.state.userMessageInfo;
        },
        lang() {
            return this.$store.state.lang;
        },
        pluginUrl() {
            return this.$store.state.pluginUrl;
        },
        apiSettings() {
            return this.$store.state.apiSettings;
        }
    },
    methods: {
        loading(obj) {
            this.$store.dispatch('setWaitOverlayInfo', obj);
        },
        userMessage(obj = {}, messageType = 'success', autoClose = 0) {
            if (typeof obj === 'string') {
                obj = { message: obj, messageType, autoClose };
            }
            this.$store.dispatch('setUserMessage', obj);
        },
        t(str) {
            return this.i18n.tl(str, this.lang);
        },
        tl(str, lang = this.lang) {
            return this.i18n.tl(str, lang);
        },
        getEnv(name) {
            return process.env[name] ?? undefined;
        },
        waitFor(condition, action, delay = 400, limit= 40, count = 0) {
            count++;
            if (condition()) {
                action();
            } else {
                if (count <= limit) {
                    setTimeout(() => {
                        this.waitFor(condition, action, delay, limit, count);
                    }, delay);
                }
            }
        }
    }
};

export default globalMixins;