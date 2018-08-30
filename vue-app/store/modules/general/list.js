import fetch from '../../../utils/fetch';
import {
  GENERAL_LIST_ERROR,
  GENERAL_LIST_LOADING,
  GENERAL_LIST_RESET,
  GENERAL_LIST_VIEW,
  GENERAL_LIST_SUCCESS
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: GENERAL_LIST_ERROR, error};
}

function loading(loading) {
  return {type: GENERAL_LIST_LOADING, loading};
}

function success(items) {
  return {type: GENERAL_LIST_SUCCESS, items};
}

function view(items) {
  return { type: GENERAL_LIST_VIEW, items};
}

const getters = {
  error: state => state.error,
  items: state => state.items,
  loading: state => state.loading,
  view: state => state.view
};

const actions = {
    getItems({ commit }, pagination = false) {
        commit(loading(true));
        let page = '/notifications?pagination=' + pagination;

      fetch(page)
        .then(response => response.json())
        .then(data => {
          commit(loading(false));
          commit(success(data['hydra:member']));
          commit(view(data['hydra:view']));
        })
        .catch(e => {
          commit(loading(false));
          commit(error(e.message));
        });
    }
};

const mutations = {
    [GENERAL_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [GENERAL_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [GENERAL_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [GENERAL_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [GENERAL_LIST_RESET] (state) {
      state.items = [];
    }
};

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
}
