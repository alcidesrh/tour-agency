import fetch from '../../../utils/fetch';
import {
  TRANSFERTOUR_LIST_ERROR,
  TRANSFERTOUR_LIST_LOADING,
  TRANSFERTOUR_LIST_RESET,
  TRANSFERTOUR_LIST_VIEW,
  TRANSFERTOUR_LIST_SUCCESS
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: TRANSFERTOUR_LIST_ERROR, error};
}

function loading(loading) {
  return {type: TRANSFERTOUR_LIST_LOADING, loading};
}

function success(items) {
  return {type: TRANSFERTOUR_LIST_SUCCESS, items};
}

function view(items) {
  return { type: TRANSFERTOUR_LIST_VIEW, items};
}

const getters = {
  error: state => state.error,
  items: state => state.items,
  loading: state => state.loading,
  view: state => state.view
};

const actions = {
    getItems({ commit }, page = '/transfer_tours') {
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
          commit(error(e.message));
        });
    }
};

const mutations = {
    [TRANSFERTOUR_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [TRANSFERTOUR_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [TRANSFERTOUR_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [TRANSFERTOUR_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [TRANSFERTOUR_LIST_RESET] (state) {
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
