import fetch from '../../../utils/fetch';
import time from "../../../utils/time";
import {
  DRIVER_LIST_ERROR,
  DRIVER_LIST_LOADING,
  DRIVER_LIST_RESET,
  DRIVER_LIST_VIEW,
  DRIVER_LIST_SUCCESS
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: DRIVER_LIST_ERROR, error};
}

function loading(loading) {
  return {type: DRIVER_LIST_LOADING, loading};
}

function success(items) {
  return {type: DRIVER_LIST_SUCCESS, items};
}

function view(items) {
  return { type: DRIVER_LIST_VIEW, items};
}

const getters = {
  error: state => state.error,
  items: state => state.items,
  loading: state => state.loading,
  view: state => state.view
};

const actions = {
    getItems({ commit }, page = '/drivers') {
      commit(loading(true));

      fetch(page)
        .then(response => response.json())
        .then(data => {
          commit(loading(false));
          commit(success(data['hydra:member']));
          commit(view(data['hydra:view']));
        })
        .catch(e => {
          commit(loading(false));
          commit(error(time() + ' ' +e.message));
        });
    }
};

const mutations = {
    [DRIVER_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [DRIVER_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [DRIVER_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [DRIVER_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [DRIVER_LIST_RESET] (state) {
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
