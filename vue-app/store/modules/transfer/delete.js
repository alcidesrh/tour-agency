import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
  TRANSFER_DELETE_ERROR,
  TRANSFER_DELETE_LOADING,
  TRANSFER_DELETE_SUCCESS,
  TRANSFER_DELETE_RESET
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  deleted: null
};

function error(error) {
  return {type: TRANSFER_DELETE_ERROR, error};
}

function loading(loading) {
  return {type: TRANSFER_DELETE_LOADING, loading};
}

function success(deleted) {
  return {type: TRANSFER_DELETE_SUCCESS, deleted};
}

function reset() {
  return {type: TRANSFER_DELETE_RESET};
}

const getters = {
  error: state => state.error,
  deleted: state => state.deleted,
  loading: state => state.loading,
};

const actions = {
    deleteItem({ commit }, id) {
        commit(loading(true));

        return fetch('/remove-transfer-airport/'+id, {method: 'DELETE'})
            .then(() => {
                commit(loading(false));
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
    [TRANSFER_DELETE_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [TRANSFER_DELETE_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [TRANSFER_DELETE_SUCCESS] (state, payload) {
      state.deleted = payload.deleted;
    },
    [TRANSFER_DELETE_RESET] (state) {
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
