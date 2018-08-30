import fetch from '../../../utils/fetch';
import {
  HTCTOURTEMPLATE_SHOW_ERROR,
  HTCTOURTEMPLATE_SHOW_LOADING,
  HTCTOURTEMPLATE_SHOW_RETRIEVED_SUCCESS,
  HTCTOURTEMPLATE_SHOW_RESET
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  retrieved: null
};

function error(error) {
  return {type: HTCTOURTEMPLATE_SHOW_ERROR, error};
}

function loading(loading) {
  return {type: HTCTOURTEMPLATE_SHOW_LOADING, loading};
}

function retrieved(retrieved) {
  return {type: HTCTOURTEMPLATE_SHOW_RETRIEVED_SUCCESS, retrieved};
}

function reset() {
  return {type: HTCTOURTEMPLATE_SHOW_RESET};
}

const getters = {
  error: state => state.error,
  loading: state => state.loading,
  item: state => state.retrieved
};

const actions = {
  retrieve({ commit }, id) {
    commit(loading(true));

    return fetch(id)
      .then(response => response.json())
      .then(data => {
        commit(loading(false));
        commit(retrieved(data));
      })
      .catch(e => {
        commit(loading(false));
        commit(error(e.message));
      });
  },
  reset({ commit }) {
    commit(reset());
  }
};

const mutations = {
    [HTCTOURTEMPLATE_SHOW_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [HTCTOURTEMPLATE_SHOW_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [HTCTOURTEMPLATE_SHOW_RETRIEVED_SUCCESS] (state, payload) {
      state.retrieved = payload.retrieved;
    },
    [HTCTOURTEMPLATE_SHOW_RESET] (state) {
      state.retrieved = null;
    }
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
};
