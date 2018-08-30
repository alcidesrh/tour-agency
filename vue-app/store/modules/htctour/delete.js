import fetch from '../../../utils/fetch';
    import time from "../../../utils/time";
import {
  HTCTOUR_DELETE_ERROR,
  HTCTOUR_DELETE_LOADING,
  HTCTOUR_DELETE_SUCCESS,
  HTCTOUR_DELETE_RESET
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  deleted: null
};

function error(error) {
  return {type: HTCTOUR_DELETE_ERROR, error};
}

function loading(loading) {
  return {type: HTCTOUR_DELETE_LOADING, loading};
}

function success(deleted) {
  return {type: HTCTOUR_DELETE_SUCCESS, deleted};
}

function reset() {
  return {type: HTCTOUR_DELETE_RESET};
}

const getters = {
  error: state => state.error,
  deleted: state => state.deleted,
  loading: state => state.loading,
};

const actions = {
    deleteItem({ commit }, id) {
        commit(loading(true));

        return fetch('/remove-htc-tour/'+id, {method: 'DELETE'})
            .then(() => {
                commit(loading(false));
            });
    },
  reset({ commit }) {
    commit(reset());
  }
};

const mutations = {
    [HTCTOUR_DELETE_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [HTCTOUR_DELETE_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [HTCTOUR_DELETE_SUCCESS] (state, payload) {
      state.deleted = payload.deleted;
    },
    [HTCTOUR_DELETE_RESET] (state) {
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
