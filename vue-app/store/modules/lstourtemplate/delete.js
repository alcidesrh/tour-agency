import fetch from '../../../utils/fetch';
import time from '../../../utils/time';
import {
  LSTOURTEMPLATE_DELETE_ERROR,
  LSTOURTEMPLATE_DELETE_LOADING,
  LSTOURTEMPLATE_DELETE_SUCCESS,
  LSTOURTEMPLATE_DELETE_RESET
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  deleted: null
};

function error(error) {
  return {type: LSTOURTEMPLATE_DELETE_ERROR, error};
}

function loading(loading) {
  return {type: LSTOURTEMPLATE_DELETE_LOADING, loading};
}

function success(deleted) {
  return {type: LSTOURTEMPLATE_DELETE_SUCCESS, deleted};
}

function reset() {
  return {type: LSTOURTEMPLATE_DELETE_RESET};
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
        commit(error(time() + ' ' + e.message));
      });
  },
  reset({ commit }) {
    commit(reset());
  }
};

const mutations = {
    [LSTOURTEMPLATE_DELETE_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [LSTOURTEMPLATE_DELETE_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [LSTOURTEMPLATE_DELETE_SUCCESS] (state, payload) {
      state.deleted = payload.deleted;
    },
    [LSTOURTEMPLATE_DELETE_RESET] (state) {
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
