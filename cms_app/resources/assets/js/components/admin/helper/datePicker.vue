<template>
  <v-dialog
    v-model="menu"
    :close-on-content-click="false"
    transition="scale-transition"
    offset-y
    width="290px"
  >
    <template v-slot:activator="{ attrs }">
      <v-text-field
        v-bind="attrs"
        v-model="formattedDate"
        :label="label"
        :append-icon="'mdi-calendar'"
        @click:append="menu = true"
        :readonly="!writable"
        outlined
        dense
        hide-details
        class="custom-date-field"
      >
        <template v-slot:append>
          <v-icon class="clickable" @click.stop="menu = true">
            mdi-calendar
          </v-icon>
        </template>
      </v-text-field>
    </template>

    <v-date-picker
      v-model="formattedDate"
      @input="onDateSelect"
      no-title
      scrollable
    />
  </v-dialog>
</template>



<script>
export default {
  props: {
    value: {
      type: String,
      default: '',
    },
    label: {
      type: String,
      default: 'Select Date',
    },
    writable: {
      type: Boolean,
      default: true, // allow writing by default
    },
  },
  data() {
    return {
      menu: false,
      date: null,
      formattedDate: '',
    };
  },
  watch: {
    value: {
      immediate: true,
      handler(val) {
        if (val) {
          this.date = this.toIsoDate(val);
          this.formattedDate = val;
        }
      },
    },
  },
  methods: {
    onDateSelect(val) {
      this.date = val;
      this.menu = false;
      this.formattedDate = this.formatDate(val);
      this.$emit('input', this.formattedDate);
    },
    onManualInput() {
      const parts = this.formattedDate.split('-');
      if (parts.length === 3) {
        const [dd, mm, yyyy] = parts;
        const iso = `${yyyy}-${mm}-${dd}`;
        const d = new Date(iso);
        if (!isNaN(d.getTime())) {
          this.date = iso;
          this.$emit('input', this.formattedDate);
        }
      }
    },
    formatDate(isoDate) {
      const d = new Date(isoDate);
      if (isNaN(d.getTime())) return '';
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      return `${day}-${month}-${year}`;
    },
    toIsoDate(dateStr) {
      const parts = dateStr.split('-');
      if (parts.length === 3) {
        const [dd, mm, yyyy] = parts;
        return `${yyyy}-${mm}-${dd}`;
      }
      return null;
    },
    applyMask() {
      let v = this.formattedDate.replace(/\D/g, '').slice(0, 8)
      if (v.length >= 5) {
        v = v.replace(/(\d{2})(\d{2})(\d{1,4})/, '$1-$2-$3')
      } else if (v.length >= 3) {
        v = v.replace(/(\d{2})(\d{1,2})/, '$1-$2')
      }
      this.formattedDate = v
    }
  },
};
</script>

