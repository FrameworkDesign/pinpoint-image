import PinPointImageFieldType from "./components/FieldTypes/PinPointImageFieldType";

Statamic.booting(() => {
    Statamic.$components.register("pin_point_image-fieldtype", PinPointImageFieldType);
});
