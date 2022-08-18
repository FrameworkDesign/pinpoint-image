<template>
    <div class="px-1 py-1 bg-white border-b border-r border-l flex-1">
        <div class="flex flex-col ">
            <h5 v-text="`Point ${itemIndex + 1}`" class="text-xs mb-0"></h5>
            <h6 v-text="heading" style="font-size:10px;"></h6>
            <div class="flex justify-between w-full--">
                <a href="#" @click.prevent="edit" class="text-xs" v-tooltip="'Edit Point Data'">
                    <svg-icon name="form" class="w-4 h-4" />
                </a>
                <a href="#" @click.prevent="remove" class="text-xs" v-tooltip="'Delete Point'">
                    <svg-icon name="trash" class="w-4 h-4" />
                </a>
            </div>
        </div>
        <modal
            v-if="modalOpen"
            name="updater-composer-output"
            :overflow="false"
            width="75%"
        >
            <div class="p-3 relative">
                <input type="text" v-model="heading" class="input-text">
                <button
                    class="btn-close absolute top-0 right-0 mt-2 mr-2"
                    :aria-label="__('Close')"
                    @click="modalOpen = false"
                    v-html="'&times'" />
            </div>
        </modal>
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
            modalOpen: false,
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
    },
    methods: {
        edit() {
            this.modalOpen = true
            console.log('edit me', this.cleanObject(this.item), this.itemIndex)
        },
        remove() {
            if (confirm('Are you sure?')) {
                this.$emit('delete', this.index);
            }
        },
        cleanObject(obj) {
            return JSON.parse(JSON.stringify(obj))
        }
    }
}
</script>
