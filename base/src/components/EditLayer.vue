<template>
    <wp-admin-layout class="edit-layer">
        <template #header>
            <h1>{{ t('Editing post') }} <em>{{ postTitle }}</em></h1>
        </template>
        <template #stage>
            <p class="welcome">
                {{ t('welcome_text') }}
            </p>
            <p><strong>{{ t('Things you have in this package') }}</strong></p>
            <p>
                <a href="#" @click="showLoading">{{ t('Loading overlay') }}</a>
            </p>
            <p>
                <a href="#" @click="userMessage(t('Some message to the user...'))">{{ t('User message bar') }}</a>
            </p>
        </template>
        <template #sidebar>
            <wp-meta-box :boxTitle="t('Sample box')">
                {{ t('Box content') }}
            </wp-meta-box>
            <wp-meta-box :boxTitle="t('Return to editor')">
                <button type="button" class="components-button is-primary" id="hide_app" @click="hideApp">
                    {{ t("Hide Vue app") }}
                </button>
            </wp-meta-box>
        </template>
        <template #footer>
            {{ t('You can use this space to display some useful information') }}
        </template>

        <template #off-layout>
        </template>
    </wp-admin-layout>
</template>

<script>
import WpAPI from "./Wp/api.js";
import WpAdminLayout from './Wp/AdminLayout.vue';
import WpMetaBox from './Wp/MetaBox.vue';

export default {
    name: 'EditLayer',
    components: {
        WpAdminLayout,
        WpMetaBox
    },
    props: {
        postId: {
            type: [String, Number],
            default: 0
        },
        postTitle: {
            type: String,
            default: ''
        },
        asideVisible: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            wpApi: null,
            modalVisible: false
        };
    },
    methods: {
        showLoading() {
            this.loading(true);
            setTimeout(() => {
                this.loading(false);
            }, 2000);
        },
        hideApp() {
            document.body.classList.remove('admin-layout-visible');
        }
    },
    watch: {
        apiSettings() {
            this.wpApi = new WpAPI(this.apiSettings);
        }
    }
}
</script>

<style lang="scss">
.edit-layer {
    aside {
        position: relative;
        .box-content {
            .buttons {
                margin-bottom: 0;
            } 
            .control {
                margin-top: 0.75rem !important;
            }
        }
    }
    .aside-section {
        margin-bottom: 0.75rem;
        > * {
            width: 86%;
            margin: 0.5rem auto;
        }
        .control {
            margin-top: 1rem;
        }
        .components-button {
            padding: 0 0.8rem;
        }
        .empty {
            color: #fc1e12;
        }
    }
    .section-head {
        .components-button {
            padding: 0;
            float: right;
        }
        h3 {
            font-size: 0.9rem !important;
            text-align: left;
            margin: 0;
            padding: 0.5rem 0;
        }
    }

    .stage p {
        margin: 1rem 0.5rem;
        font-size: 16px;
    }
}
</style>