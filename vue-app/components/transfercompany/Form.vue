<template>
  <form @submit.prevent="handleSubmit(item)">
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.id) }">
      <label for="_id" class="control-label">id</label>
      <input v-model="item.id" type="number"  placeholder=""  id="_id" class="form-control" />
      <span v-if="errors && errors.id" class="help-block" id="_id_helpBlock">{{ errors.id }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.code) }">
      <label for="_code" class="control-label">code</label>
      <input v-model="item.code" type="text"  placeholder=""  id="_code" class="form-control" />
      <span v-if="errors && errors.code" class="help-block" id="_code_helpBlock">{{ errors.code }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.color) }">
      <label for="_color" class="control-label">color</label>
      <input v-model="item.color" type="text"  placeholder=""  id="_color" class="form-control" />
      <span v-if="errors && errors.color" class="help-block" id="_color_helpBlock">{{ errors.color }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.name) }">
      <label for="_name" class="control-label">name</label>
      <input v-model="item.name" type="text"  placeholder=""  id="_name" class="form-control" />
      <span v-if="errors && errors.name" class="help-block" id="_name_helpBlock">{{ errors.name }}</span>
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
  </form>
</template>

<script>
  export default {
    props: {
      handleSubmit: {
        type: Function,
        required: true,
      },
      values: {
        type: Object,
        required: true
      },
      errors: {
        type: Object
      },
      initialValues: {
        type: Object
      }
    },
    computed: {
      item: function () {
        return this.initialValues ? this.initialValues : this.values;
      }
    }
  }
</script>
