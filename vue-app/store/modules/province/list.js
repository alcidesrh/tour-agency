import fetch from '../../../utils/fetch';
import time from '../../../utils/time';
import {
  PROVINCE_LIST_ERROR,
  PROVINCE_LIST_LOADING,
  PROVINCE_LIST_RESET,
  PROVINCE_LIST_VIEW,
  PROVINCE_LIST_SUCCESS
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: PROVINCE_LIST_ERROR, error};
}

function loading(loading) {
  return {type: PROVINCE_LIST_LOADING, loading};
}

function success(items) {
  return {type: PROVINCE_LIST_SUCCESS, items};
}

function view(items) {
  return { type: PROVINCE_LIST_VIEW, items};
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
        let page = '/provinces?pagination=' + pagination;
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
    [PROVINCE_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [PROVINCE_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [PROVINCE_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [PROVINCE_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [PROVINCE_LIST_RESET] (state) {
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
