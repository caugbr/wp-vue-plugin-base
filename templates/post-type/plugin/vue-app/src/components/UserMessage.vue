<template>
    <div class="user-message-holder">
        <div
            :class="topClass"
            :style="{ backgroundColor: theme }"
        >
            <div class="message-content">
                <div class="flex-center">
                    <strong v-if="title">{{ title }}</strong>
                    <span v-if="routerPath">
                        <router-link :to="{ path: routerPath }" @click.native="closeAlertEmit">{{ message }}</router-link>
                    </span>
                    <span v-else>{{ message }}</span>
                </div>
            </div>
            <a 
                href="#"
                v-if="dismissible" 
                class="close-message-button alert-del" 
                @click.prevent="closeAlertEmit"
            >
                &times;
            </a>
        </div>
    </div>
</template>

<script>
export default {
    name: "user-message",
    props: {
        message: {
            type: String,
            default: '',
        },
        title: {
            type: String,
            default: '',
        },
        messageType: {
            type: String,
            default: 'success',
            validator: (value) => {
                return ["success", "info", "error", "warning"].indexOf(value) !== -1;
            },
        },
        routerPath: {
            type: String,
            default: ''
        },
        dismissible: {
            type: Boolean,
            default: true
        },
        autoClose: {
            type: Number,
            default: 0
        }
    },
    data () {
        return {
            isOpen: false
        };
    },
    computed: {
        topClass() {
            let cls = 'user-message';
            if (this.isOpen) {
                cls += ' open';
            }
            return cls;
        },
        theme () {
            switch (this.messageType) {
                case 'success':
                    return '#69B7B0'
                case 'info':
                    return '#4193D6'
                case 'warning':
                    return '#EABE5E'
                default:
                    return '#DB7B8F'
            }
        }
    },
    methods: {
        closeAlertEmit () {
            this.$emit("close-alert", false);
        },
        setAutoClose() {
            if (this.autoClose > 0) {
                setTimeout(() => this.closeAlertEmit(), this.autoClose);
            }
        }
    },
    watch: {
        message(val) {
            if (val) {
                this.isOpen = true;
                this.setAutoClose();
            } else {
                this.isOpen = false;
            }
        }
    }
};
</script>

<style lang="scss">
.user-message-holder {
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 999999;
    box-sizing: border-box;
}
.user-message {
    margin-top: 0px;
    transition: all 200ms ease-in-out 0s;
    z-index: 9999;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    width: -webkit-fill-available;
    transform: translateY(-100%);
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    margin: 0.75rem;
    border-radius: 0.25rem;
    align-items: center;
    color: #FFFFFF;
    pointer-events: none;
    opacity: 0;
    box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;

    &.open {
        transform: translateY(0);
        pointer-events: all;
        opacity: 1;
    }

    a, a:active a:visited {
        color: #FFFFFF;
        text-decoration: none;

        &:hover {
            color: #999999;
        }
    }

    .close-message-button {
        font-size: 25px;
        color: #FFFFFF !important;
        &:focus, &:active {
            outline: none !important;
        }
    }

    .message-content span {
        font-size: 1.2rem;
    }
}
</style>