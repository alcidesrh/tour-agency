<template>
  <form @submit.prevent="handleSubmit(item)">
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.fullName) }">
      <label for="_fullName" class="control-label">fullName</label>
      <input v-model="item.fullName" type="text"  placeholder=""  id="_fullName" class="form-control" />
      <span v-if="errors && errors.fullName" class="help-block" id="_fullName_helpBlock">{{ errors.fullName }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.username) }">
      <label for="_username" class="control-label">username</label>
      <input v-model="item.username" type="text"  placeholder=""  id="_username" class="form-control" />
      <span v-if="errors && errors.username" class="help-block" id="_username_helpBlock">{{ errors.username }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.email) }">
      <label for="_email" class="control-label">email</label>
      <input v-model="item.email" type="text"  placeholder=""  id="_email" class="form-control" />
      <span v-if="errors && errors.email" class="help-block" id="_email_helpBlock">{{ errors.email }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.password) }">
      <label for="_password" class="control-label">password</label>
      <input v-model="item.password" type="text"  placeholder=""  id="_password" class="form-control" />
      <span v-if="errors && errors.password" class="help-block" id="_password_helpBlock">{{ errors.password }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.roles) }">
      <label for="_roles" class="control-label">roles</label>
      <input v-model="item.roles" type="text"  placeholder=""  id="_roles" class="form-control" />
      <span v-if="errors && errors.roles" class="help-block" id="_roles_helpBlock">{{ errors.roles }}</span>
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
