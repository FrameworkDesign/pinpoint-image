<template>
    <div class="px-1 py-1 bg-white border-b border-r border-l flex-1">
        <div class="flex flex-col ">
            <h5 v-text="`Point ${itemIndex + 1}`" class="text-xs mb-0"></h5>
            <h6 v-text="item.data.heading" class="break-all" style="font-size:10px;"></h6>
            <div class="flex flex-row flex-wrap justify-between w-full-- mt-4">
                <a href="#" @click.prevent="edit" class="text-xs flex items-center justify-center" v-tooltip="'Edit Point Data'">
                    <svg-icon name="form" class="w-4 h-4" style="min-width: 10px;" />
                    <span class="text-xs ml-1">Edit</span>
                </a>
                <a href="#" @click.prevent="remove" class="text-xs flex items-center justify-center mt-2" v-tooltip="'Delete Point'">
                    <svg-icon name="trash" class="w-4 h-4" style="min-width: 10px;" />
                    <span class="text-xs ml-1">Delete</span>
                </a>
            </div>
        </div>
        <modal
            v-if="modalOpen"
            name="updater-composer-output"
            :overflow="false"
            width="75%"
        >
            <div class="p-3 relative" style="overflow-y: auto;max-height: 700px;">
                <button
                    class="btn-close absolute top-0 right-0 mt-2 mr-2"
                    :aria-label="__('Close')"
                    @click="modalOpen = false"
                    v-html="'&times'" />
                <div class="content mt-0 mb-2">
                    <h2>Content</h2> <p class="max-w-lg">Edit your pinpoints content here:</p>
                </div>
                <div>
                    <label class="text-base font-bold mb-1">Heading</label>
                    <input type="text" v-model="item.data.heading" class="input-text mb-5">

                    <label class="text-base font-bold mb-1">Fields:</label>
                    <div style="overflow-y: auto;max-height: 400px;" v-if="hasFields">
                        <div class="border rounded p-2 mb-4" v-for="(field, fIndex) in item.data.fields"
                             :key="fIndex">
                            <div class="mb-1 flex items-center justify-start">
                                <label :for="`${field.value}_field_label`" class="font-size: 12px;">Name:</label>
                                <input class="input-text ml-1" :name="`${field.value}_field_label`" v-model="field.label" style="padding: 5px;font-size: 12px;height: 30px; max-width:250px;"/>
                            </div>
                            <div
                                class="w-full pb-4 flex"
                            >
                                <div class="w-full">
                                    <component
                                        :is="`${field.value}-fieldtype`"
                                        :value.sync="field.content"
                                        :handle="`${field.value}_field`"
                                        :meta="field.meta"
                                        :config="field.config"
                                        name-prefix="pinpoint-"
                                        error-key-prefix="pinpoint-"
                                        :read-only="false"
                                        @input="updateFieldContent($event, field, fIndex)"
                                        @meta-updated="$emit('meta-updated', $event)"
                                        @focus="focused"
                                    />
                                </div>
                                <div class="flex justify-end mt-1 ml-1">
                                    <a href="#" @click.prevent="removeField(field, fIndex)" class="text-xs" v-tooltip="'Delete field'">
                                        <svg-icon name="trash" class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <div class="mt-5">
                    <div v-if="isSelectingNewFieldtype">
                        <div class="fieldtype-selector">
                            <div class="fieldtype-list">
                                <div class="p-1" v-for="(fieldtype, findex) in fieldtypes" :key="findex">
                                    <button
                                        class="bg-white border border-grey-50 flex items-center group w-full rounded hover:border-grey-60 shadow-sm hover:shadow-md pr-1.5"
                                        @click="select(fieldtype)"
                                    >
                                        <div class="p-1 flex items-center border-r border-grey-50 group-hover:border-grey-60 bg-grey-20 rounded-l">
                                            <svg-icon class="h-5 w-5 text-grey-80" :name="fieldtype.icon" default="generic-field"></svg-icon>
                                        </div>
                                        <span class="pl-1.5 text-grey-80 text-md group-hover:text-grey-90">{{ fieldtype.text }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-row justify-between mt-5">
                        <button class="btn w-auto flex justify-center items-center" @click="isSelectingNewFieldtype = true;">
                            <svg-icon name="wireframe" class="mr-1 w-4 h-4" />
                            {{ __('Add Field') }}
                        </button>
                        <button class="btn-primary w-auto ml-auto flex justify-center items-center" @click="modalOpen = false">
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </div>
        </modal>
    </div>
</template>
<script>

import {
    SortableList,
    SortableItem,
    SortableHelpers,
} from "./../../../sortable/Sortable";

export default {
    components: {SortableList},
    props: {
        item: {
            type: Object,
            default: () => ({
                x: 0,
                y: 0,
                data: {
                    heading: '',
                    fields: []
                },
            })
        },
        itemIndex: {
            type: Number,
            default: 0
        },
        meta: {
            type: Object,
            default: () => ({})
        }
    },
    data() {
        return {
            isSelectingNewFieldtype: false,
            modalOpen: false,
            heading: this.item.data.heading,
            fields: this.item.data.fields,
            fieldtypes: [
                { icon:"markdown", text:"Markdown", value:"markdown", content:'' },
                { icon:"text", text:"Text", value:"text", content:'' },
                { icon:"textarea", text:"Textarea", value:"textarea", content:'' },
                { icon:"link", text:"Link", value:"link", content:'', meta: this.meta.linkConfig.meta, config: this.meta.linkConfig.config},
                { icon:"bard", text:"Bard", value:"bard", content:[], config: this.meta.bardConfig },
                { icon:"code", text:"Code", value:"code", content: { code:null, mode:"htmlmixed"}, config: this.meta.codeConfig},
            ]
        }
    },
    computed: {
        hasFields() {
            return (this.item.data.fields !== undefined && this.item.data.fields.length > 0)
        }
    },
    methods: {
        select(fieldType) {
            if(this.item.data.fields === undefined) {
                this.item.data.fields = []
            }
            this.item.content = ''
            this.item.data.fields.push(this.cleanObject(fieldType))
            this.isSelectingNewFieldtype = false
        },
        testInput: _.debounce(function (logItem) {
          console.log('testInput', logItem)
            this.item.data.fields = logItem
            this.$emit('updateorder', {item: this.item, itemIndex: this.itemIndex})
        }, 500),
        updateOrder: _.debounce(function () {
            console.log('updateOrder', this.fields, this.item.data.fields)
            // this.fields =

        }, 500),
        updateFieldContent($event, field, fIndex) {
            field.content = $event
        },
        edit() {
            this.modalOpen = true
        },
        removeField(field, fIndex) {
            if (! confirm('Are you sure?')) {
                return
            }
            this.item.data.fields = this.item.data.fields.filter((item, index) => {
                return index !== fIndex
            })
        },
        remove() {
            if (confirm('Are you sure?')) {
                this.$emit('delete', this.index);
            }
        },
        cleanObject(obj) {
            return JSON.parse(JSON.stringify(obj))
        },
        focused() {
            if (!this.isLocked) {
                this.$emit('focus');
            }
        },
    }
}
</script>
<style>
.pinpoint-drag-handle-fields {
    width: 20px;
    border-width: 1px;
    height: auto;
    padding: 8px;
    cursor: -webkit-grab;
    cursor: grab;
    background: #f5f8fc url("/vendor/pinpoint-image/img/drag-dots.svg") 50%
    no-repeat;
}
</style>
