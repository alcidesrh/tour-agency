import fetch from '../../../utils/fetch';
import time from '../../../utils/time';
import {
  CONFIG_LIST_ERROR,
  CONFIG_LIST_LOADING,
  CONFIG_LIST_RESET,
  CONFIG_LIST_VIEW,
  CONFIG_LIST_SUCCESS
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: CONFIG_LIST_ERROR, error};
}

function loading(loading) {
  return {type: CONFIG_LIST_LOADING, loading};
}

function success(items) {
  return {type: CONFIG_LIST_SUCCESS, items};
}

function view(items) {
  return { type: CONFIG_LIST_VIEW, items};
}

const getters = {
  error: state => state.error,
  items: state => state.items,
  loading: state => state.loading,
  view: state => state.view
};

const actions = {
    getItems({commit}, pagination = false) {
        commit(loading(true));
        let page = '/agents?pagination=' + pagination;
        fetch(page)
            .then(response => response.json())
            .then(data => {
                commit(loading(false));
                commit(success(data['hydra:member']));
                commit(view(data['hydra:view']));
            })
            .catch(e => {
                commit(loading(false));
                commit(error(time() + ' ' + e.messagee.message));
            });
    }
};

const mutations = {
    [CONFIG_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [CONFIG_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [CONFIG_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [CONFIG_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [CONFIG_LIST_RESET] (state) {
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
