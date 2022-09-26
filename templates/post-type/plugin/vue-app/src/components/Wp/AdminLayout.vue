<template>
    <div :class="['wp-admin-layout', isFullScreen ? 'is-fullscreen' : '']">
        <header>
            <div class="header-left">
                <a href="#" @click.prevent="closePanel">
                    <svg v-if="isFullScreen" width="36px" height="36px" xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" aria-hidden="true" focusable="false">
                        <path d="M20 10c0-5.51-4.49-10-10-10C4.48 0 0 4.49 0 10c0 5.52 4.48 10 10 10 5.51 0 10-4.48 10-10zM7.78 15.37L4.37 6.22c.55-.02 1.17-.08 1.17-.08.5-.06.44-1.13-.06-1.11 0 0-1.45.11-2.37.11-.18 0-.37 0-.58-.01C4.12 2.69 6.87 1.11 10 1.11c2.33 0 4.45.87 6.05 2.34-.68-.11-1.65.39-1.65 1.58 0 .74.45 1.36.9 2.1.35.61.55 1.36.55 2.46 0 1.49-1.4 5-1.4 5l-3.03-8.37c.54-.02.82-.17.82-.17.5-.05.44-1.25-.06-1.22 0 0-1.44.12-2.38.12-.87 0-2.33-.12-2.33-.12-.5-.03-.56 1.2-.06 1.22l.92.08 1.26 3.41zM17.41 10c.24-.64.74-1.87.43-4.25.7 1.29 1.05 2.71 1.05 4.25 0 3.29-1.73 6.24-4.4 7.78.97-2.59 1.94-5.2 2.92-7.78zM6.1 18.09C3.12 16.65 1.11 13.53 1.11 10c0-1.3.23-2.48.72-3.59C3.25 10.3 4.67 14.2 6.1 18.09zm4.03-6.63l2.58 6.98c-.86.29-1.76.45-2.71.45-.79 0-1.57-.11-2.29-.33.81-2.38 1.62-4.74 2.42-7.1z"></path>
                    </svg>
                    <svg v-else width="36px" height="36px" xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" aria-hidden="true" focusable="false">
                        <path d="M 4.8611767,10.028235 12.541176,-0.02352899 h 2.541177 L 7.4588234,10.028235 15.082353,19.995294 h -2.541177 z" style="fill:#000000;stroke:#000000;stroke-width:0.666667px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;fill-opacity:1"></path>
                    </svg>
                </a>
            </div>
            <div class="header-middle">
                <slot name="header"></slot>
            </div>
            <div class="header-right">
                <slot name="header-right"></slot>
                <button type="button" class="components-button is-primary" @click="updatePost">{{  buttonLabel }}</button>
                <button type="button" class="components-button is-pressed has-icon" @click="toggleSidebar">
                    <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" focusable="false">
                        <path fill-rule="evenodd" d="M10.289 4.836A1 1 0 0111.275 4h1.306a1 1 0 01.987.836l.244 1.466c.787.26 1.503.679 2.108 1.218l1.393-.522a1 1 0 011.216.437l.653 1.13a1 1 0 01-.23 1.273l-1.148.944a6.025 6.025 0 010 2.435l1.149.946a1 1 0 01.23 1.272l-.653 1.13a1 1 0 01-1.216.437l-1.394-.522c-.605.54-1.32.958-2.108 1.218l-.244 1.466a1 1 0 01-.987.836h-1.306a1 1 0 01-.986-.836l-.244-1.466a5.995 5.995 0 01-2.108-1.218l-1.394.522a1 1 0 01-1.217-.436l-.653-1.131a1 1 0 01.23-1.272l1.149-.946a6.026 6.026 0 010-2.435l-1.148-.944a1 1 0 01-.23-1.272l.653-1.131a1 1 0 011.217-.437l1.393.522a5.994 5.994 0 012.108-1.218l.244-1.466zM14.929 12a3 3 0 11-6 0 3 3 0 016 0z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </header>
        <div class="content">
            <div class="stage">
                <slot name="stage"></slot>
            </div>
            <aside class="sidebar" v-show="asideVisible">
                <div class="inner">
                    <slot name="sidebar"></slot>
                </div>
            </aside>
        </div>
        <footer>
            <slot name="footer"></slot>
        </footer>
        <slot name="off-layout"></slot>
    </div>
</template>

<script>
export default {
    name: 'WpAdminLayout',
    data() {
        return {
            asideVisible: true,
            buttonLabel: '',
            isFullScreen: document.querySelector('body').classList.contains('is-fullscreen-mode')
        };
    },
    methods: {
        updatePost() {
            this.closePanel();
            setTimeout(() => {
                document.querySelector('.edit-post-header__settings button.is-primary').click();
            }, 250);
        },
        toggleSidebar(ev) {
            const el = ev.target.closest('.components-button');
            el.classList.toggle('is-pressed');
            this.asideVisible = el.classList.contains('is-pressed');
        },
        closePanel() {
            document.body.classList.remove('admin-layout-visible');
        },
        observeBodyClass() {
            const body = document.querySelector("body");
            let prevFsState = body.classList.contains('is-fullscreen-mode');
            let prevFmState = body.classList.contains('folded');
            (new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    if (mutation.attributeName == "class") {
                        const currentFsState = mutation.target.classList.contains('is-fullscreen-mode');
                        const currentFmState = mutation.target.classList.contains('folded');
                        if (prevFsState !== currentFsState) {
                            prevFsState = currentFsState;
                            this.fullscreen(!!currentFsState);
                        }
                        if (prevFmState !== currentFmState) {
                            prevFmState = currentFmState;
                            this.foldedMenu = !!currentFmState;
                            this.fullscreen(false);
                        }
                    }
                });
            })).observe(body, { attributes: true });
        },
        fullscreen(set) {
            this.isFullScreen = set;
            if (this.isFullScreen) {
                this.$el.style = {};
            } else {
                const editor = document.querySelector('#editor');
                const info = editor.getBoundingClientRect();
                this.$el.style.position = 'fixed';
                this.$el.style.top = `${info.top}px`;
                this.$el.style.left = `${info.left}px`;
                this.$el.style.width = `${info.width}px`;
                this.$el.style.height = `${info.height}px`;
            }
        },
        saveBoxesState() {
            let metaboxes = [];
            Array.from(document.querySelectorAll('.wp-meta-box')).forEach((e, index) => {
                metaboxes[index] = e.classList.contains('is-opened');
            });
            window.localStorage.setItem('wpAdminLayoutBoxes', JSON.stringify(metaboxes));
        },
        setBoxesState() {
            const item = window.localStorage.getItem('wpAdminLayoutBoxes');
            if (item) {
                const metaboxes = JSON.parse(item);
                metaboxes.forEach((e, index) => {
                    if (e) {
                        document.querySelector(`.wp-meta-box:nth-child(${index + 1})`).setOpened(true);
                    } else {
                        document.querySelector(`.wp-meta-box:nth-child(${index + 1})`).setOpened(false);
                    }
                });
            }
        }
    },
    mounted() {
        this.observeBodyClass();
        this.setBoxesState();
        setTimeout(() => {
            this.buttonLabel = document.querySelector('.edit-post-header__settings button.is-primary').innerHTML.trim();
        }, 500);
    }
}
</script>

<style lang="scss">
.admin-layout-visible {
    .interface-interface-skeleton__footer,
    .interface-interface-skeleton__editor .edit-post-sidebar__panel-tabs {
        opacity: 0 !important;
        pointer-events: none !important;
    }
    &.is-fullscreen-mode {
        #adminmenuwrap,
        #adminmenuback,
        #wpadminbar {
            opacity: 0 !important;
            pointer-events: none !important;
        }
    }
}
.wp-admin-layout {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #FFF;

    > header {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 60px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        flex-direction: row;
        z-index: 10;

        .header-left {
            background-color: #FFFFFF;
            width: 60px;
            flex-shrink: 0;
            flex-grow: 0;

            a {
                display: flex;
                align-items: center;
                align-self: stretch;
                border: none;
                background-color: #FFFFFF;
                color: #fff;
                border-radius: 0;
                width: 60px;
                position: relative;
                height: 59px;

                svg {
                    fill: currentColor;
                    outline: none;
                    margin: auto;
                }
            }
        }

        .header-middle {
            flex-shrink: 1;
            flex-grow: 1;
            text-align: left;

            h1 {
                margin: 0 0 0 1.2rem;
                line-height: 60px;
            }
        }

        .header-right {
            width: auto;
            flex-shrink: 0;
            flex-grow: 0;
            display: flex;
            align-items: center;
            padding: 0 0.8rem;
            button {
                padding: 0;
                margin-left: 12px;
                &.is-primary {
                    padding: 0 0.8rem;
                }
            }
        }
    }

    .content {
        height: calc(100% - 84px);
        display: flex;
        flex-direction: row;
        margin-top: 60px;

        .stage {
            flex-grow: 1;
            flex-shrink: 1;
            overflow: auto;
            position: relative;
        }

        aside {
            flex-grow: 0;
            flex-shrink: 0;
            border-left: 1px solid #e0e0e0;
            overflow: auto;
            .inner {
                width: 280px;
            }
        }
    }

    > footer {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 24px;
        line-height: 24px;
        padding: 0 0.5rem;
        border-top: 1px solid #e0e0e0;
    }
}

.is-fullscreen-mode {
    .header-left {
        background-color: #1e1e1e !important;
        a {
            background-color: #1e1e1e !important;
        }
    }
}
</style>