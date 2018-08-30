import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
  DESTINATION_LIST_ERROR,
  DESTINATION_LIST_LOADING,
  DESTINATION_LIST_RESET,
  DESTINATION_LIST_VIEW,
  DESTINATION_LIST_SUCCESS
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: DESTINATION_LIST_ERROR, error};
}

function loading(loading) {
  return {type: DESTINATION_LIST_LOADING, loading};
}

function success(items) {
  return {type: DESTINATION_LIST_SUCCESS, items};
}

function view(items) {
  return { type: DESTINATION_LIST_VIEW, items};
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
      let page = '/destinations?pagination=' + pagination;
      return fetch(page)
        .then(response => response.json())
        .then(data => {
          commit(loading(false));
          commit(success(data['hydra:member']));
          commit(view(data['hydra:view']));
        })
        .catch(e => {
          commit(loading(false));
          commit(error(time() + ' ' + e.message));
        });
    }
};

const mutations = {
    [DESTINATION_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [DESTINATION_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [DESTINATION_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [DESTINATION_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [DESTINATION_LIST_RESET] (state) {
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
