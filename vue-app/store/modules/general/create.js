import SubmissionError from '../../../error/SubmissionError';
import fetch from '../../../utils/fetch';
import {
  GENERAL_CREATE_ERROR,
  GENERAL_CREATE_LOADING,
  GENERAL_CREATE_SUCCESS,
  GENERAL_CREATE_VIOLATIONS,
  GENERAL_CREATE_RESET
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  created: null
};

function error(error) {
  return {type: GENERAL_CREATE_ERROR, error};
}

function loading(loading) {
  return {type: GENERAL_CREATE_LOADING, loading};
}

function success(created) {
  return {type: GENERAL_CREATE_SUCCESS, created};
}

function violations(violations) {
  return {type: GENERAL_CREATE_VIOLATIONS, violations};
}

function reset() {
  return {type: GENERAL_CREATE_RESET};
}

const getters = {
  created: state => state.created,
  error: state => state.error,
  loading: state => state.loading,
  violations: state => state.violations,
};

const actions = {
  create({ commit }, param) {
    commit(loading(true));

    return fetch( param.url, {method: 'POST', body: JSON.stringify(param.item)})
      .then(response => {
        commit(loading(false));

        return response.json();
      })
      .then(data => {
        commit(success(data));
      })
      .catch(e => {
        commit(loading(false));
          var d = new Date();
        if (e instanceof SubmissionError) {
          commit(violations(e.errors));
          commit(error(e.errors._error + ' ' + d.toString()));
          return;
        }

        commit(error(e.message + ' ' + d.toString()));
      });
  },
  reset({ commit }) {
    commit(reset());
  }
};

const mutations = {
    [GENERAL_CREATE_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [GENERAL_CREATE_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [GENERAL_CREATE_SUCCESS] (state, payload) {
      state.created = payload.created;
    },
    [GENERAL_CREATE_VIOLATIONS] (state, payload) {
      state.violations = payload.violations;
    },
    [GENERAL_CREATE_RESET] (state) {
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
