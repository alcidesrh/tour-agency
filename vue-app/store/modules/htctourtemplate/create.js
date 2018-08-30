import SubmissionError from '../../../error/SubmissionError';
import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
  HTCTOURTEMPLATE_CREATE_ERROR,
  HTCTOURTEMPLATE_CREATE_LOADING,
  HTCTOURTEMPLATE_CREATE_SUCCESS,
  HTCTOURTEMPLATE_CREATE_VIOLATIONS,
  HTCTOURTEMPLATE_CREATE_RESET
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  created: null
};

function error(error) {
  return {type: HTCTOURTEMPLATE_CREATE_ERROR, error};
}

function loading(loading) {
  return {type: HTCTOURTEMPLATE_CREATE_LOADING, loading};
}

function success(created) {
  return {type: HTCTOURTEMPLATE_CREATE_SUCCESS, created};
}

function violations(violations) {
  return {type: HTCTOURTEMPLATE_CREATE_VIOLATIONS, violations};
}

function reset() {
  return {type: HTCTOURTEMPLATE_CREATE_RESET};
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

    return fetch('/create-htc-tour-template', {method: 'POST', body: JSON.stringify(values)})
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
    [HTCTOURTEMPLATE_CREATE_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [HTCTOURTEMPLATE_CREATE_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [HTCTOURTEMPLATE_CREATE_SUCCESS] (state, payload) {
      state.created = payload.created;
    },
    [HTCTOURTEMPLATE_CREATE_VIOLATIONS] (state, payload) {
      state.violations = payload.violations;
    },
    [HTCTOURTEMPLATE_CREATE_RESET] (state) {
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
