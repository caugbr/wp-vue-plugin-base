<template>
    <div ref="wrapper" :class="wrapperClass">
        <div class="box-title">
            <button type="button" aria-expanded="true" class="toggle-button" @click="toggleBox">
                <span aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <path d="M6.5 12.4L12 8l5.5 4.4-.9 1.2L12 10l-4.5 3.6-1-1.2z"></path>
                    </svg>
                </span>
                {{ boxTitle }}
            </button>
        </div>
        <div class="box-content" v-show="isOpened">
            <slot />
        </div>
    </div>
</template>

<script>
export default {
    name: 'WpMetaBox',
    props: {
        boxTitle: {
            type: String
        }
    },
    data() {
        return {
            isOpened: true
        };
    },
    methods: {
        toggleBox() {
            this.isOpened = !this.isOpened;
        }
    },
    computed: {
        wrapperClass() {
            let cls = ['wp-meta-box'];
            if (this.isOpened) {
                cls.push('is-opened');
            }
            return cls;
        }
    },
    watch: {
        isOpened() {
            this.$nextTick(() => {
                this.$parent.saveBoxesState();
            });
        }
    },
    mounted() {
        this.$el.setOpened = stt => this.isOpened = stt;
    }
}
</script>

<style lang="scss">
.wp-meta-box {
    padding: 0;
    margin: 0;
    border-bottom: 1px solid #e0e0e0;
    .box-title {
        display: block;
        padding: 0;
        font-size: inherit;
        margin-top: 0;
        margin-bottom: 0;
        transition: background-color 0.1s ease-in-out 0s;
        button {
            cursor: pointer;
            position: relative;
            padding: 16px 48px 16px 16px;
            outline: none;
            width: 100%;
            font-weight: 500;
            text-align: left;
            color: #1e1e1e;
            background-color: #FFFFFF;
            border: none;
            box-shadow: none;
            transition: background-color .1s ease-in-out;
            height: auto;
            box-sizing: border-box;
            &:hover {
                background-color: #f0f0f0;
            }
            span svg {
                position: absolute;
                right: 16px;
                top: 50%;
                transform: translateY(-50%)  rotate(180deg);
                color: #1e1e1e;
                fill: currentColor;
                transition: color .1s ease-in-out;
            }
        }
    }
    .box-content {
        padding: 12px 16px 16px;
    }
    &.is-opened {
        span svg {
            transform: translateY(-50%) rotate(0deg) !important;
        }
    }
}
</style>