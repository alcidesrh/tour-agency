import fetch from '../../../utils/fetch';
import {
  ICON_LIST_ERROR,
  ICON_LIST_LOADING,
  ICON_LIST_RESET,
  ICON_LIST_VIEW,
  ICON_LIST_SUCCESS
} from './mutation-types';
import time from "../../../utils/time";

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: ICON_LIST_ERROR, error};
}

function loading(loading) {
  return {type: ICON_LIST_LOADING, loading};
}

function success(items) {
  return {type: ICON_LIST_SUCCESS, items};
}

function view(items) {
  return { type: ICON_LIST_VIEW, items};
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
        let page = '/icons?pagination=' + pagination;
        fetch(page)
            .then(response => response.json())
            .then(data => {
                commit(loading(false));
                commit(success(data['hydra:member']));
            })
            .catch(e => {
                commit(loading(false));
                commit(error(time() + ' ' + e.message));
            });
    }
};

const mutations = {
    [ICON_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [ICON_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [ICON_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [ICON_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [ICON_LIST_RESET] (state) {
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
