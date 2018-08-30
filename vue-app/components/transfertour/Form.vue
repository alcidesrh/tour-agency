<template>
  <form @submit.prevent="handleSubmit(item)">
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.numberPerson) }">
      <label for="_numberPerson" class="control-label">numberPerson</label>
      <input v-model="item.numberPerson" type="number"  placeholder=""  id="_numberPerson" class="form-control" />
      <span v-if="errors && errors.numberPerson" class="help-block" id="_numberPerson_helpBlock">{{ errors.numberPerson }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.date) }">
      <label for="_date" class="control-label">date</label>
      <input v-model="item.date" type="text"  placeholder=""  id="_date" class="form-control" />
      <span v-if="errors && errors.date" class="help-block" id="_date_helpBlock">{{ errors.date }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.contactName) }">
      <label for="_contactName" class="control-label">contactName</label>
      <input v-model="item.contactName" type="text"  placeholder=""  id="_contactName" class="form-control" />
      <span v-if="errors && errors.contactName" class="help-block" id="_contactName_helpBlock">{{ errors.contactName }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.code) }">
      <label for="_code" class="control-label">code</label>
      <input v-model="item.code" type="text"  placeholder=""  id="_code" class="form-control" />
      <span v-if="errors && errors.code" class="help-block" id="_code_helpBlock">{{ errors.code }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.flightData) }">
      <label for="_flightData" class="control-label">flightData</label>
      <input v-model="item.flightData" type="text"  placeholder=""  id="_flightData" class="form-control" />
      <span v-if="errors && errors.flightData" class="help-block" id="_flightData_helpBlock">{{ errors.flightData }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.accommodation) }">
      <label for="_accommodation" class="control-label">accommodation</label>
      <input v-model="item.accommodation" type="text"  placeholder=""  id="_accommodation" class="form-control" />
      <span v-if="errors && errors.accommodation" class="help-block" id="_accommodation_helpBlock">{{ errors.accommodation }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.type) }">
      <label for="_type" class="control-label">type</label>
      <input v-model="item.type" type="text"  placeholder=""  id="_type" class="form-control" />
      <span v-if="errors && errors.type" class="help-block" id="_type_helpBlock">{{ errors.type }}</span>
    </div>
    <div :class="{ 'form-group': true, 'has-error': (errors && errors.tourCode) }">
      <label for="_tourCode" class="control-label">tourCode</label>
      <input v-model="item.tourCode" type="text"  placeholder=""  id="_tourCode" class="form-control" />
      <span v-if="errors && errors.tourCode" class="help-block" id="_tourCode_helpBlock">{{ errors.tourCode }}</span>
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
