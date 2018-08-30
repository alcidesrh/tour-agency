import SubmissionError from '../../../error/SubmissionError';
import fetch from '../../../utils/fetch';
import {
  BOOKING_CREATE_ERROR,
  BOOKING_CREATE_LOADING,
  BOOKING_CREATE_SUCCESS,
  BOOKING_CREATE_VIOLATIONS,
  BOOKING_CREATE_RESET
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  created: null
};

function error(error) {
  return {type: BOOKING_CREATE_ERROR, error};
}

function loading(loading) {
  return {type: BOOKING_CREATE_LOADING, loading};
}

function success(created) {
  return {type: BOOKING_CREATE_SUCCESS, created};
}

function violations(violations) {
  return {type: BOOKING_CREATE_VIOLATIONS, violations};
}

function reset() {
  return {type: BOOKING_CREATE_RESET};
}

const getters = {
  created: state => state.created,
  error: state => state.error,
  loading: state => state.loading,
  violations: state => state.violations,
};

const actions = {
  create({ commit }, values) {
    commit(loading(true));

    return fetch('/bookings', {method: 'POST', body: JSON.stringify(values)})
      .then(response => {
        commit(loading(false));

        return response.json();
      })
      .then(data => {
        commit(success(data));
      })
      .catch(e => {
        commit(loading(false));

        if (e instanceof SubmissionError) {
          commit(violations(e.errors));
          commit(error(e.errors._error));
          return;
        }

        commit(error(e.message));
      });
  },
  reset({ commit }) {
    commit(reset());
  }
};

const mutations = {
    [BOOKING_CREATE_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [BOOKING_CREATE_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [BOOKING_CREATE_SUCCESS] (state, payload) {
      state.created = payload.created;
    },
    [BOOKING_CREATE_VIOLATIONS] (state, payload) {
      state.violations = payload.violations;
    },
    [BOOKING_CREATE_RESET] (state) {
      state.loading = false;
      state.error = '';
      state.created = null;
      state.violations = null;
    }
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
};
