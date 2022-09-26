<template>
    <div id="app">
        <wait-overlay
            :visible="waitOverlayInfo.visible"
            :message="waitOverlayInfo.message"
            :dismissible="waitOverlayInfo.dismissible"
        />
        <user-message
            :title="userMessageInfo.title"
            :message="userMessageInfo.message"
            :messageType="userMessageInfo.messageType"
            :dismissible="userMessageInfo.dismissible"
            :autoClose="userMessageInfo.autoClose"
            :routerPath="userMessageInfo.routerPath"
            @close-alert="userMessage()"
        />
        <p>
            {{ t('Here is your Vue app!') }}
        </p>
    </div>
</template>

<script>
import WaitOverlay from '../components/WaitOverlay.vue';
import UserMessage from '../components/UserMessage.vue';

export default {
    name: 'Frontend',
    components: { WaitOverlay, UserMessage },
    mounted() {
        if (window.wpVue) {
            const cond = () => (undefined !== window.wpVue.pluginDirUrl);
            const act = () => {
                if (window.wpVue.pluginDirUrl) {
                    this.$store.dispatch('setPluginUrl', window.wpVue.pluginDirUrl);
                }
                if (window.wpVue.wpLang) {
                    this.$store.dispatch('setLang', window.wpVue.wpLang);
                }
                if (window.wpVue.wpApiSettings) {
                    this.$store.dispatch('setApiSettings', window.wpVue.wpApiSettings);
                }
            };
            this.waitFor(cond, act);
        } else {
            console.error(this.t('WP-Vue variable was not found!'));
            return;
        }
        document.querySelector('#wp-vue-error').style.display = 'none';
        // remove it
        this.userMessage(this.t("You are in the Frontend view of your Vue app!"), "info");
    }
}
</script>

<style lang="scss">
    @import "../scss/app.scss";
</style>
