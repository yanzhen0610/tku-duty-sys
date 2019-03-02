<template>
    <label>
        <input v-if="editable"
            v-model="copiedValue"
            type="checkbox"
            class="filled-in">
        <input v-else
            v-model="copiedValue"
            type="checkbox"
            onclick="return false">
        <span></span>
    </label>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        props: {
            value: {
                type: Boolean,
                required: true,
            },
            keyName: {
                required: true,
            },
        },
        data() {
            return {
                'copiedValue': this.value,
            };
        },
        computed: {
            ...mapGetters([
                'editable',
            ]),
        },
        methods: {
            updateValue() {
                this.$emit('update-value', this.keyName, this.copiedValue);
            },
        },
        watch: {
            copiedValue() {
                this.updateValue();
            },
        },
    }
</script>
