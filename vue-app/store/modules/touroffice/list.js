import fetch from '../../../utils/fetch';
import {
  TOUROFFICE_LIST_ERROR,
  TOUROFFICE_LIST_LOADING,
  TOUROFFICE_LIST_RESET,
  TOUROFFICE_LIST_VIEW,
  TOUROFFICE_LIST_SUCCESS
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: TOUROFFICE_LIST_ERROR, error};
}

function loading(loading) {
  return {type: TOUROFFICE_LIST_LOADING, loading};
}

function success(items) {
  return {type: TOUROFFICE_LIST_SUCCESS, items};
}

function view(items) {
  return { type: TOUROFFICE_LIST_VIEW, items};
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
      let page = '/tour_offices?pagination=' + pagination;
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
    [TOUROFFICE_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [TOUROFFICE_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [TOUROFFICE_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [TOUROFFICE_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [TOUROFFICE_LIST_RESET] (state) {
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
