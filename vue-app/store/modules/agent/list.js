import fetch from '../../../utils/fetch';
import time from '../../../utils/time';
import {
  AGENT_LIST_ERROR,
  AGENT_LIST_LOADING,
  AGENT_LIST_RESET,
  AGENT_LIST_VIEW,
  AGENT_LIST_SUCCESS
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: AGENT_LIST_ERROR, error};
}

function loading(loading) {
  return {type: AGENT_LIST_LOADING, loading};
}

function success(items) {
  return {type: AGENT_LIST_SUCCESS, items};
}

function view(items) {
  return { type: AGENT_LIST_VIEW, items};
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
    [AGENT_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [AGENT_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [AGENT_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [AGENT_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [AGENT_LIST_RESET] (state) {
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
