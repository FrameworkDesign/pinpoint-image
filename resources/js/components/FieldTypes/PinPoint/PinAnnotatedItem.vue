<template>
    <div class="px-2 py-1 bg-white border-b border-r border-l">
        <h5 v-text="`Marker ${itemIndex + 1}`" class="mb-0"></h5>
        <input type="text" v-model="heading" class="input-text">
    </div>
</template>
<script>
export default {
    props: {
        item: {
            type: Object,
            default: () => ({
                x: 0,
                y: 0,
                data: {
                    heading: ''
                }
            })
        },
        itemIndex: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            heading: this.item.data.heading
        }
    },
    watch: {
        heading(newValue) {
            this.$emit('updated', {
                index: this.itemIndex,
                data: {
                    x: this.item.x,
                    y: this.item.y,
                    data: {
                        heading: newValue
                    }
                }
            })
        }
    }
}
</script>
