<template>
    <div>
        <assets-fieldtype
            :value="value"
            :config="config"
            :meta="meta"
            :handle="handle"
            :readOnly="readOnly"
            @input="updateKey($event)"
        ></assets-fieldtype>

        <div class="flex flex-row parent-wrap" v-show="hasImage">
            <div class="w-1/5 pin-annotated-items">
                <div class="flex flex-row " v-if="annotations.length" v-for="(item, index) in annotations">
                    <pin-annotated-item @updated="updateAnnotation" :key="index" :item="item" :item-index="index"></pin-annotated-item>
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
        <div @click.prevent="currentValue" class="btn">Current Value</div>
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
        this.hasImage = (this.value !== null && this.value.image);
    },

    data() {
        return {
            hasImage: false,
            containerWidth: null,
            annotations: [],
            loading: false,
            assets: null
        };
    },

    computed: {
        imageUrl() {
            if (this.hasImage && this.meta.image && this.meta.image.data && this.meta.image.data.length > 0) {
                return this.meta.image.data[0].thumbnail
            }
            return null;
        }
    },
    methods: {
        currentValue() {
            console.log('currentValue', this.value, this.meta)
        },
        updateAnnotation(updatedData) {
            let pinpointImageValue = this.value
            this.$set(this.annotations, updatedData.index, updatedData.data);
            // Vue.set(pinpointImageValue, 'annotations', this.annotations);
            // this.updateDebounced(pinpointImageValue);
        },
        updateKey(value) {
            console.log('updateKey', value)
            if(value === null) {
                return;
            }

            if(value.length === 0) {
                return;
            }

            let pinpointImageValue = this.value
            Vue.set(pinpointImageValue, 'image', value);
            this.updateDebounced(pinpointImageValue);

            this.hasImage = (pinpointImageValue.image !== null)
            if (this.hasImage === true) {
                this.loading = true

                this.$axios.get(this.cpUrl('assets-fieldtype'), {
                    params: { assets: pinpointImageValue.image }
                }).then(response => {
                    this.meta = {
                        annotations : this.annotations,
                        image: {
                            container: "assets",
                            data: response.data
                        }
                    }
                    this.loading = false
                });
            } else {
                // this.meta = null
            }
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
