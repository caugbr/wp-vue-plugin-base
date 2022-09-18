<template>
    <div id="vue-app-admin" class="app">
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
        <edit-layer
            :postId="postId" 
            :postTitle="postTitle" 
            :asideVisible="asideVisible"
            @closePanel="closePanel"
        />
    </div>
</template>

<script>
import WaitOverlay from '../components/WaitOverlay.vue';
import UserMessage from '../components/UserMessage.vue';
import EditLayer from '../components/EditLayer.vue';

export default {
    name: 'Backend',
    components: { WaitOverlay, UserMessage, EditLayer },
    data() {
        return {
            asideVisible: true
        }
    },
    computed: {
        postId() {
            return window.wpVue.postId;
        },
        postTitle() {
            return window.wpVue.postTitle;
        },
    },
    methods: {
        closePanel() {
            document.body.classList.remove('admin-layout-visible');
        },
        updatePost() {
            this.closePanel();
            setTimeout(() => {
                document.querySelector('button.editor-post-publish-button').click();
            }, 250);
        },
        toggleSidebar(ev) {
            const el = ev.target.closest('button');
            const close = el.classList.contains('is-pressed');
            el.classList.toggle('is-pressed');
            this.asideVisible = !close;
        }
    },
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
            console.error('WP-Vue variable was not found!');
            return;
        }
        document.querySelector('#vc-admin-wrapper').style.display = 'block';
        document.querySelector('#vc-admin-error').style.display = 'none';
    }
}
</script>

<style lang="scss">
    #vue-app-admin {
        opacity: 0;
        pointer-events: none;
        transition: opacity 250ms ease-in 0s;
    }
    .admin-layout-visible {
        height: 100%;
        #vue-app-admin {
            opacity: 1;
            pointer-events: all;
            height: 100%;
        }
    }
</style>
