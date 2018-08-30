import SubmissionError from '../../../error/SubmissionError';
import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
  CONFIG_CREATE_ERROR,
  CONFIG_CREATE_LOADING,
  CONFIG_CREATE_SUCCESS,
  CONFIG_CREATE_VIOLATIONS,
  CONFIG_CREATE_RESET
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  created: null
};

function error(error) {
  return {type: CONFIG_CREATE_ERROR, error};
}

function loading(loading) {
  return {type: CONFIG_CREATE_LOADING, loading};
}

function success(created) {
  return {type: CONFIG_CREATE_SUCCESS, created};
}

function violations(violations) {
  return {type: CONFIG_CREATE_VIOLATIONS, violations};
}

function reset() {
  return {type: CONFIG_CREATE_RESET};
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

    return fetch('/agents', {
      method: 'POST',
        credentials: "same-origin",
        body: JSON.stringify(values)})
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
          commit(error(time() + ' ' + e.errors._error));
          return;
        }

        commit(error(time() + ' ' + e.message));
      });
  },
  reset({ commit }) {
    commit(reset());
  }
};

const mutations = {
    [CONFIG_CREATE_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [CONFIG_CREATE_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [CONFIG_CREATE_SUCCESS] (state, payload) {
      state.created = payload.created;
    },
    [CONFIG_CREATE_VIOLATIONS] (state, payload) {
      state.violations = payload.violations;
    },
    [CONFIG_CREATE_RESET] (state) {
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
