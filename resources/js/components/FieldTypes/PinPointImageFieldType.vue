<template>
    <div class="field-type-pinpoint-image">
        <div
            class="pinpoint-image-global-actions mb-1"
            v-if="hasImage || hasAnnotations"
        >
            <button
                class="btn mr-1"
                v-if="hasAnnotations"
                @click.prevent="clearAnnotations"
            >
                {{ __("Clear Annotations") }}
            </button>

            <button class="btn mr-1" v-if="hasImage" @click.prevent="clearImage">
                {{ __("Clear Image") }}
            </button>
        </div>

        <div class="assets-fieldtype-picker" v-if="!hasImage">
            <assets-fieldtype
                :value="assetImage"
                ref="assets"
                handle="assets"
                :config="config"
                :meta="meta"
                :readOnly="readOnly"
                @input="updateKey"
                @upload-complete="uploadComplete"
            ></assets-fieldtype>
        </div>
        <div class="flex flex-row parent-wrap pin-has-image-wrap" v-show="hasImage">
            <div class="pin-annotated-items">
                <sortable-list
                    v-model="annotations"
                    :vertical="true"
                    item-class="sortable-row"
                    handle-class="sortable-handle"
                    @dragstart="$emit('focus')"
                    @dragend="updateOrder"
                >
                    <div ref="list">
                        <div
                            v-for="(annotation, index) in annotations"
                            :key="index"
                            class="flex flex-row sortable-row items-stretch justify-stretch"
                        >
                            <span class="pinpoint-drag-handle sortable-handle"></span>
                            <pin-annotated-item
                                :meta="meta"
                                @delete="remove(index)"
                                :item.sync="annotation"
                                :item-index="index"
                                @updateorder="updateAnnotationOrder"
                            ></pin-annotated-item>
                        </div>
                    </div>
                </sortable-list>
            </div>
            <div class="pin-point-image-image">
                <div class="pinpoint-preview relative">
                    <div class="border-b border-r border-l">
                        <img :src="imageUrl" ref="pinpointImage" @click="getClickedPosition" />
                    </div>
                    <div
                        v-if="annotations.length"
                        v-for="(item, index) in annotations"
                        :style="{ top: item.y + '%', left: item.x + '%' }"
                        class="pinpoint-annotate"
                        @mousedown="dragStart(index, $event)"
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
import {
    SortableList,
    SortableItem,
    SortableHelpers,
} from "./../../sortable/Sortable";
export default {
    components: {
        PinAnnotatedItem,
        SortableList,
        SortableItem,
    },

    mixins: [Fieldtype, SortableHelpers],

    mounted() {
        this.config.max_files = 1
        this.config.min_files = 0
        this.config.mode = 'list'


        if (this.value !== null && this.value.image && this.value.annotations) {
            this.fieldValue = this.value;
            if (this.value.image !== null) {

                if(this.meta.statamic_major_version === 3) {
                    return this.getImageAssetV3(this.value.image);
                } else {
                    this.getImageAsset(this.value.image);
                }
            }

            if (this.value.annotations.length > 0) {
                this.annotations = this.value.annotations;
            }
        }
    },

    data() {
        return {
            drag: false,
            fieldValue: { image: null, annotations: [] },
            hasImage: false,
            containerWidth: null,
            image: [],
            annotations: [],
            loading: false,
            assets: null,
        };
    },

    watch: {
        fieldValue: {
            deep: true,
            handler(data) {
                this.updateDebounced(data);
            },
        },
    },

    computed: {
        imageUrl() {
            if (
                this.hasImage &&
                this.image &&
                this.image.data &&
                this.image.data.length > 0
            ) {
                return this.image.data[0].thumbnail;
            }
            return false;
        },
        assetImage() {
            return this.fieldValue.image === null ? [] : [this.fieldValue.image];
        },
        hasAnnotations() {
            return this.annotations.length > 0;
        },
    },
    methods: {
        reorderItems() {
            this.drag = false;
        },
        updateAnnotation(updatedData) {
            this.$set(this.annotations, updatedData.index, updatedData.data);
        },
        clearImage() {
            if (! confirm("Are you sure?")) {
                return;
            }
            this.cleanOutImage();
        },
        clearAnnotations() {
            if (! confirm("Are you sure?")) {
                return;
            }
            this.$set(this, "annotations", []);
        },
        cleanOutImage() {
            this.image = null;
            this.fieldValue.image = null;
            let newMeta = this.cleanObject(this.meta);
            newMeta.data = [];
            this.hasImage = false;
            this.updateMeta(newMeta);
            this.update([]);
        },
        updateKey(assets) {
            if (assets === null) {
                this.cleanOutImage();
                return;
            }
            if (assets.length === 0) {
                this.cleanOutImage();
                return;
            }

            if(this.meta.statamic_major_version === 3) {
                return this.getImageAssetV3(assets[0]);
            }

            this.getImageAsset(assets[0]);
        },

        getImageAssetV3(value) {
            this.loading = true;
            this.$axios
                .get(this.cpUrl("assets-fieldtype"), {
                    params: { assets: value },
                })
                .then((response) => {
                    this.image = {
                        container: this.meta.container,
                        data: response.data,
                    };
                    this.fieldValue.image = value;
                    this.hasImage = true;
                })
                .catch(error => {
                    this.hasImage = false;
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        getImageAsset(value) {
            this.loading = true;
            this.$axios
                .post(this.cpUrl("assets-fieldtype"), {
                    assets: [ value ],
                })
                .then((response) => {
                    this.image = {
                        container: this.meta.container,
                        data: response.data,
                    };
                    this.fieldValue.image = value;
                    this.hasImage = true;
                })
                .catch(error => {
                    this.hasImage = false;
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        cpUrl(url) {
            url = Statamic.$config.get("cpUrl") + "/" + url;
            return url.replace(/([^:])(\/\/+)/g, "$1/");
        },

        getClickedPosition(e) {
            let xy = this.getXyPosition(e);
            this.annotations.push({
                x: xy.x,
                y: xy.y,
                data: {
                    heading: "",
                    fields: [],
                },
            });
            this.fieldValue.annotations = this.annotations;
        },

        getXyPosition(e) {
            const position = this.getPosition(e.currentTarget);
            const xPosition = e.clientX - position.x - 40 / 2;
            const yPosition = e.clientY - position.y - 40 / 2;

            const width = this.$refs.pinpointImage.clientWidth;
            const height = this.$refs.pinpointImage.clientHeight;

            // convert position to percentage values
            let x = this.roundUp((xPosition / width) * 100, 0);
            let y = this.roundUp((yPosition / height) * 100, 0);

            return {
                x,
                y,
            };
        },

        getPosition(el) {
            let xPos = 0;
            let yPos = 0;

            while (el) {
                if (el.tagName === "BODY") {
                    // deal with browser quirks with body/window/document and page scroll
                    const xScroll = el.scrollLeft || document.documentElement.scrollLeft;
                    const yScroll = el.scrollTop || document.documentElement.scrollTop;

                    xPos += el.offsetLeft - xScroll + el.clientLeft;
                    yPos += el.offsetTop - yScroll + el.clientTop;
                } else {
                    // for all other non-BODY elements
                    xPos += el.offsetLeft - el.scrollLeft + el.clientLeft;
                    yPos += el.offsetTop - el.scrollTop + el.clientTop;
                }

                el = el.offsetParent;
            }

            return {
                x: xPos,
                y: yPos,
            };
        },

        remove(index) {
            this.annotations.splice(index, 1);
            this.fieldValue.annotations = this.annotations;
        },

        dragStart(index, event) {
            const pinPoint = this.annotations[index];
            const { clientX: startX, clientY: startY } = event;
            const { x: startLeft, y: startTop } = pinPoint;

            const rect = this.$refs.pinpointImage.getBoundingClientRect();
            const bounds = { minX: 0, minY: 0, maxX: 100, maxY: 100 };

            const updatePosition = (event) => {
                const deltaX = (event.clientX - startX) / rect.width * 100;
                const deltaY = (event.clientY - startY) / rect.height * 100;

                pinPoint.x = Math.min(Math.max(startLeft + deltaX, bounds.minX), bounds.maxX);
                pinPoint.y = Math.min(Math.max(startTop + deltaY, bounds.minY), bounds.maxY);
            };

            const stopDragging = () => {
                document.removeEventListener('mousemove', updatePosition);
                document.removeEventListener('mouseup', stopDragging);
            };

            document.addEventListener('mousemove', updatePosition);
            document.addEventListener('mouseup', stopDragging);
        },


        dragEnd($event, item, index) {
            //let xy = this.getXyPosition($event);

            //this.annotations[index].x = item.x + xy.x;
            //this.annotations[index].y = item.y + xy.y;
        },

        updateOrder: _.debounce(function () {
            this.fieldValue.annotations = this.annotations;
        }, 500),

        updateAnnotationOrder(annotationData) {
            this.annotations[annotationData.index] = annotationData.item;
            this.annotations = this.cleanObject(this.annotations)
        },

        roundUp(num, precision) {
            precision = Math.pow(10, precision);
            return Math.ceil(num * precision) / precision;
        },

        isNull(value) {
            return value === null;
        },

        cleanObject(obj) {
            return JSON.parse(JSON.stringify(obj));
        },
    },
};
</script>
<style>
.field-type-pinpoint-image .assets-fieldtype-picker {
    //display: flex;
    align-items: center;
    padding: 8px 16px;
    --bg-opacity: 1;
    background-color: #f5f8fc;
    background-color: rgba(
        244.79999999999998,
        248.11499999999998,
        252.45,
        var(--bg-opacity)
    );
    border-width: 1px;
    border-radius: 3px;
    min-width: 360px;
    font-size: 12px;
    position: relative;
}

.field-type-pinpoint-image .pin-has-image-wrap {
    border-top-width: 1px;
}

.field-type-pinpoint-image .asset-table-listing {
    display: none;
}
.field-type-pinpoint-image .asset-upload-control {
    margin-left: 16px;
}
.field-type-pinpoint-image .upload-text-button {
    --text-opacity: 1;
    color: #19a1e6;
    color: rgba(
        25.499999999999993,
        161.49999999999994,
        229.5,
        var(--text-opacity)
    );
    text-decoration: underline;
    white-space: nowrap;
    display: flex;
    flex-wrap: wrap;
}
</style>
<style scoped>
.pinpoint-preview {
    max-width: 100%;
    position: relative;
    cursor: crosshair;
}

.pinpoint-preview img {
    width: 100%;
    height: auto;
}

.pinpoint-annotate {
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
        content: "";
        position: absolute;
        width: 50%;
        height: 50%;
        top: 25%;
        left: 25%;
        border-radius: 50%;
    }
}

.pin-annotated-items {
    width: 15%;
}
.pin-point-image-image {
    width: 85%;
}

.pinpoint-annotate span {
    display: block;
    transform: rotate(-45deg);
}

.pinpoint-drag-handle {
    width: 20px;
    border-left-width: 1px;
    border-bottom-width: 1px;
    height: auto;
    padding: 8px;
    cursor: -webkit-grab;
    cursor: grab;
    background: #f5f8fc url("/vendor/pinpoint-image/img/drag-dots.svg") 50%
    no-repeat;
}
</style>
