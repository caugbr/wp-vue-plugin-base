<template>
    <div :class="['wait-overlay', visible ? 'open' : '']" @click="clickOverlay">
        <div class="inner-content">
            <div v-if="message" class="text">{{ message }}</div>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                <g>
                    <path d="M50 15A35 35 0 1 0 74.74873734152916 25.251262658470843" fill="none" stroke="#ffffff" stroke-width="12"></path>
                    <path d="M49 3L49 27L61 15L49 3" fill="#ffffff"></path>
                    <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform>
                </g>
            </svg>
        </div>
    </div>
</template>

<script>
export default {
    name: 'WaitOverlay',
    props: {
        message: {
            type: String,
            default: ''
        },
        dismissible: {
            type: Boolean,
            default: false
        },
        visible: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            
        };
    },
    computed: {
        info() {
            return this.$store.state.waitOverlayInfo;
        }
    },
    methods: {
        clickOverlay() {
            if (this.dismissible) {
                this.visible = false;
            }
        }
    }
}
</script>

<style lang="scss">
.wait-overlay {
    opacity: 0;
    pointer-events: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    transition: opacity 250ms ease-in 0s;
    z-index: 99999;

    &.open {
        opacity: 1;
        pointer-events: unset;
    }

    .inner-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: inline-block;
        width: 40px;
        height: 40px;

        svg {
            width: 56px;
            height: 56px;
            margin: auto; 
            display: block; 
            shape-rendering: auto;
        }
    }
}
</style>