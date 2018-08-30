import fetch from '../../../utils/fetch';
import {
  LSBOOKING_LIST_ERROR,
  LSBOOKING_LIST_LOADING,
  LSBOOKING_LIST_RESET,
  LSBOOKING_LIST_VIEW,
  LSBOOKING_LIST_SUCCESS
} from './mutation-types';

const state = {
  loading: false,
  error: '',
  items: [],
  view: []
};

function error(error) {
  return {type: LSBOOKING_LIST_ERROR, error};
}

function loading(loading) {
  return {type: LSBOOKING_LIST_LOADING, loading};
}

function success(items) {
  return {type: LSBOOKING_LIST_SUCCESS, items};
}

function view(items) {
  return { type: LSBOOKING_LIST_VIEW, items};
}

const getters = {
  error: state => state.error,
  items: state => state.items,
  loading: state => state.loading,
  view: state => state.view
};

const actions = {
    getItems({ commit }, page = '/l_s_bookings') {
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
    [LSBOOKING_LIST_ERROR] (state, payload) {
      state.error = payload.error;
    },
    [LSBOOKING_LIST_LOADING] (state, payload) {
      state.loading = payload.loading;
    },
    [LSBOOKING_LIST_VIEW] (state, payload) {
      state.view = payload.items;
    },
    [LSBOOKING_LIST_SUCCESS] (state, payload) {
      state.items = payload.items;
    },
    [LSBOOKING_LIST_RESET] (state) {
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
