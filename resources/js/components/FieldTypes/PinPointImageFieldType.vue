<template>
    <div class="field-type-pinpoint-image">
        <div class="assets-fieldtype-picker">
            <assets-fieldtype
                :value="assetImage"
                :config="config"
                :meta="meta"
                :handle="handle"
                :readOnly="readOnly"
                @input="updateKey($event)"
            ></assets-fieldtype>
        </div>
        <div class="flex flex-row parent-wrap" v-show="hasImage">
            <div class="w-1/5 pin-annotated-items">
                <div class="flex flex-row " v-if="annotations.length" v-for="(item, index) in annotations">
                    <pin-annotated-item @updated="updateAnnotation" @delete="remove(index)" :key="index" :item="item" :item-index="index"></pin-annotated-item>
                </div>
            </div>
            <div class="w-4/5 pin-point-image-image">

                <div class="floorplan-preview relative" >
                    <div class="border-b border-r border-l">
                        <img :src="imageUrl" ref="floorplan" @click="getClickedPosition">
                    </div>
                    <div
                        v-if="annotations.length"
                        v-for="(item, index) in annotations"
                        :style="{ top: item.y + '%', left: item.x + '%' }"
                        class="floorplan-annotate"
                        @click="remove(index)"
                    >
                        <span v-text="`${index + 1}`"></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import PinAnnotatedItem from "./PinPoint/PinAnnotatedItem";
export default {

    components: {
        PinAnnotatedItem
    },

    mixins: [Fieldtype],

    mounted() {
        if (this.value !== null && this.value.image && this.value.image.length > 0) {
           this.getImageAsset(this.value.image)
        }

        if (this.value !== null && this.value.annotations && this.value.annotations.length > 0) {
            this.annotations = this.value.annotations
        }
    },

    data() {
        return {
            fieldValue: this.value || { "image": {}, "annotations": []},
            hasImage: false,
            containerWidth: null,
            image: [],
            annotations: [],
            loading: false,
            assets: null
        };
    },

    watch: {
        fieldValue: {
            deep: true,
            handler (data) {
                this.updateDebounced(data);
            }
        },
    },

    computed: {
        imageUrl() {
            if (this.hasImage && this.image && this.image.data && this.image.data.length > 0) {
                return this.image.data[0].thumbnail
            }
            return null;
        },
        assetImage() {
            return (this.fieldValue.image === null) ? [] : [this.fieldValue.image]
        }
    },
    methods: {
        updateAnnotation(updatedData) {
            this.$set(this.annotations, updatedData.index, updatedData.data);
        },
        cleanOutImage() {
            this.image = null;
            this.fieldValue.image = null;
            let newMeta = this.refreshObject(this.meta);
            newMeta.data = []
            this.updateMeta(newMeta)
            this.update([]);
        },
        updateKey(value) {
            if (value === null) {
                this.cleanOutImage()
                return;
            }
            if (value.length === 0) {
                this.cleanOutImage()
                return;
            }
            this.getImageAsset(value[0])
        },

        getImageAsset(value) {
            this.loading = true
            this.$axios.get(this.cpUrl('assets-fieldtype'), {
                params: { assets: value }
            }).then(response => {
                this.image = {
                    container: "assets",
                    data: response.data
                }
                this.fieldValue.image = value;
                this.hasImage = true
            })
            .finally(() => {
                this.loading = false
            });
        },

        cpUrl(url) {
            url = Statamic.$config.get('cpUrl') + '/' + url;
            return url.replace(/([^:])(\/\/+)/g, '$1/');
        },

        getClickedPosition(e) {
            const position = this.getPosition(e.currentTarget)
            const xPosition = e.clientX - position.x - 40 / 2
            const yPosition = e.clientY - position.y - 40 / 2

            const width = this.$refs.floorplan.clientWidth;
            const height = this.$refs.floorplan.clientHeight;

            // convert position to percentage values
            let x = this.roundUp((xPosition / width) * 100, 0)
            let y = this.roundUp((yPosition / height) * 100, 0)

            this.annotations.push({
                x: x,
                y: y,
                data: {
                    heading: ''
                }
            })
            this.fieldValue.annotations = this.annotations;
        },

        getPosition(el) {
            let xPos = 0
            let yPos = 0

            while (el) {
                if (el.tagName === 'BODY') {
                    // deal with browser quirks with body/window/document and page scroll
                    const xScroll = el.scrollLeft || document.documentElement.scrollLeft
                    const yScroll = el.scrollTop || document.documentElement.scrollTop

                    xPos += el.offsetLeft - xScroll + el.clientLeft
                    yPos += el.offsetTop - yScroll + el.clientTop
                } else {
                    // for all other non-BODY elements
                    xPos += el.offsetLeft - el.scrollLeft + el.clientLeft
                    yPos += el.offsetTop - el.scrollTop + el.clientTop
                }

                el = el.offsetParent
            }

            return {
                x: xPos,
                y: yPos
            }
        },

        remove(index) {
            if (confirm('Are you sure you want to delete item?')) {
                this.annotations.splice(index, 1)
                this.fieldValue.annotations = this.annotations;
            }
        },

        roundUp(num, precision) {
            precision = Math.pow(10, precision)
            return Math.ceil(num * precision) / precision
        },

        isNull(value) {
            return value === null
        },

        refreshObject(obj) {
            return JSON.parse(JSON.stringify(obj))
        }
    }

}
</script>
<style>
.field-type-pinpoint-image .assets-fieldtype-picker {
    display: flex;
    align-items: center;
    padding: 8px 16px;
    --bg-opacity: 1;
    background-color: #f5f8fc;
    background-color: rgba(244.79999999999998,248.11499999999998,252.45,var(--bg-opacity));
    border-width: 1px;
    border-radius: 3px;
}
.field-type-pinpoint-image .asset-upload-control {
    margin-left: 16px;
}
.field-type-pinpoint-image .upload-text-button {
    --text-opacity: 1;
    color: #19a1e6;
    color: rgba(25.499999999999993,161.49999999999994,229.5,var(--text-opacity));
    text-decoration: underline;
    white-space: nowrap;
}
</style>
<style scoped>
.floorplan-preview {
    max-width: 100%;
    position: relative;
    cursor: crosshair;
}

.floorplan-preview img {
    width: 100%;
    height: auto;
}

.floorplan-annotate {
    position: absolute;
    border-radius: 50% 50% 0;
    transform: rotate(45deg);
    background-color: #303750;
    color: #fff;

    height: 30px;
    width: 30px;
    text-align: center;
    line-height: 30px;
    font-weight: 700;
    font-size: 12px;
    cursor: pointer;

    &::after {
         content: '';
         position: absolute;
         width: 50%;
         height: 50%;
         top: 25%;
         left: 25%;
         border-radius: 50%;
     }
}

.floorplan-annotate span {
    display: block;
    transform: rotate(-45deg);
}
</style>
