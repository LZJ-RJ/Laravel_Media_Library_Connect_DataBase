<template>
    <div class="__box-img">
        <img v-if="src"
             ref="img"
             :src="src"
             :alt="file.name"
             :style="imgStyle"
             loading="lazy">
    </div>
</template>

<script>
import lazy from '../../mixins/lazy'

export default {
    mixins: [lazy],
    props: ['checkForDimensions'],
    data() {
        return {
            applyStyles: false
        }
    },
    computed: {
        imgStyle() {
            return this.applyStyles
                ? {
                    objectFit: 'cover',
                    opacity: ''
                }
                : {
                    opacity: 0
                }
        }
    },
    methods: {
        sendDimensionsToParent() {
            const library = this
            let url = this.src

            this.$refs.img.addEventListener('load', function() {
                library.applyStyles = true
                library.$el.style.border = 'none'

                if (!library.checkForDimensions(url)) {
                    EventHub.fire('save-image-dimensions', {
                        url: url,
                        val: `${this.naturalWidth} x ${this.naturalHeight}`
                    })
                }
            })

        }
    },
    watch: {
        intersected: {
            immediate: true,
            handler(val) {
                if (val) {
                    this.src = this.file.path
                    this.$nextTick(() => this.sendDimensionsToParent())
                }
            }
        }
    }
}
</script>
