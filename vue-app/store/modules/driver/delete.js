import fetch from '../../../utils/fetch';
import {
  DRIVER_DELETE_ERROR,
  DRIVER_DELETE_LOADING,
  DRIVER_DELETE_SUCCESS,
  DRIVER_DELETE_RESET
} from './mutation-types';
import time from "../../../utils/time";

const state = {
  loading: false,
  error: '',
  deleted: null
};

function error(error) {
  return {type: DRIVER_DELETE_ERROR, error};
}

function loading(loading) {
  return {type: DRIVER_DELETE_LOADING, loading};
}

function success(deleted) {
  return {type: DRIVER_DELETE_SUCCESS, deleted};
}

function reset() {
  return {type: DRIVER_DELETE_RESET};
}

const getters = {
  error: state => state.error,
  deleted: state => state.deleted,
  loading: state => state.loading,
};

const actions = {
  delete({ commit }, item) {
    commit(loading(true));

    return fetch(item['@id'], {method: 'DELETE'})
      .then(() => {
        commit(loading(false));
        commit(success(item));
      })
      .catch(e => {
        commit(loading(false));
        commit(error(time() + ' ' +e.message));
      });
  },
  reset({ commit }) {
    commit(reset());
  }
};

const mutations = {
    [DRIVER_DELETE_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [DRIVER_DELETE_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [DRIVER_DELETE_SUCCESS] (state, payload) {
      state.deleted = payload.deleted;
    },
    [DRIVER_DELETE_RESET] (state) {
      state.error = '';
      state.loading = false;
      state.deleted = null;
    }
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
};
